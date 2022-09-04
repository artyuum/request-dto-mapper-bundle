<?php

namespace Tests\Mapper;

use Artyum\RequestDtoMapperBundle\Attribute\Dto;
use Artyum\RequestDtoMapperBundle\Event\PostDtoMappingEvent;
use Artyum\RequestDtoMapperBundle\Event\PostDtoValidationEvent;
use Artyum\RequestDtoMapperBundle\Event\PreDtoMappingEvent;
use Artyum\RequestDtoMapperBundle\Event\PreDtoValidationEvent;
use Artyum\RequestDtoMapperBundle\Exception\DtoMappingException;
use Artyum\RequestDtoMapperBundle\Exception\DtoValidationException;
use Artyum\RequestDtoMapperBundle\Exception\ExtractionFailedException;
use Artyum\RequestDtoMapperBundle\Mapper\Mapper;
use Artyum\RequestDtoMapperBundle\Extractor\JsonExtractor;
use Artyum\RequestDtoMapperBundle\Extractor\ExtractorInterface;
use LogicException;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Tests\Fixtures\Dto\FooDto;
use Tests\Fixtures\Extractor\ErroringExtractor;

class MapperTest extends TestCase
{
    private function getMapper(
        ?ServiceLocator $serviceLocator = null, ?Request $request = null, ?EventDispatcher $eventDispatcher = null,
        ?string $defaultExtractor = null, ?ValidatorInterface $validator = null
    ): Mapper {
        $denormalizerConfiguration = [
            'default_options'    => [],
            'additional_options' => [],
        ];

        $validationConfiguration = [
            'enabled'            => false,
            'default_groups'     => [],
            'additional_groups'  => [],
            'throw_on_violation' => true,
        ];

        $serializer = new Serializer([
            new ObjectNormalizer(propertyTypeExtractor: new PropertyInfoExtractor(typeExtractors: [
                new PhpDocExtractor(),
            ])),
        ]);

        $serviceLocator = $serviceLocator ?? new ServiceLocator([]);

        $requestStack = new RequestStack();
        $requestStack->push($request ?? new Request());

        $eventDispatcher = $eventDispatcher ?? new EventDispatcher();

        return new Mapper(
            $denormalizerConfiguration,
            $validationConfiguration,
            $serviceLocator,
            $requestStack,
            $eventDispatcher,
            $serializer,
            $validator,
            $defaultExtractor
        );
    }

    public function testItThrowsAnExceptionOnMissingExtractor(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage(sprintf('Unable to the find the passed extractor "%s" in the container. Make sure it\'s tagged as "artyum_request_dto_mapper.extractor".', stdClass::class));

        $this
            ->getMapper()
            ->map(new Dto(stdClass::class), new stdClass())
        ;
    }

    public function testItThrowsAnExceptionOnNonRegisteredExtractor(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage(sprintf('Unable to the find the passed extractor "%s" in the container. Make sure it\'s tagged as "artyum_request_dto_mapper.extractor".', stdClass::class));

        $this
            ->getMapper()
            ->map(new Dto(stdClass::class, stdClass::class), new stdClass())
        ;
    }

    public function testItThrowsAnExceptionOnUnknownExtractor(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('You must set an extractor either on the attribute or in the configuration file.');

        $this
            ->getMapper()
            ->map(new Dto(), new stdClass())
        ;
    }

    public function testItThrowsAnExceptionOnExtractionFailure(): void
    {
        $this->expectException(ExtractionFailedException::class);
        $this->expectExceptionMessage('Failed to extract the request data.');

        $serviceLocatorMock = $this->createMock(ServiceLocator::class);
        $serviceLocatorMock
            ->expects($this->once())
            ->method('has')
            ->with(ErroringExtractor::class)
            ->willReturn(true)
        ;
        $serviceLocatorMock
            ->expects($this->once())
            ->method('get')
            ->with(ErroringExtractor::class)
            ->willReturn(new ErroringExtractor())
        ;

        $this
            ->getMapper($serviceLocatorMock)
            ->map(new Dto(ErroringExtractor::class, stdClass::class), new stdClass())
        ;
    }

    public function testItThrowsAnExceptionOnMappingFailure(): void
    {
        $this->expectException(DtoMappingException::class);
        $this->expectExceptionMessage('Failed to map the extracted request data to the DTO.');

        $serviceLocatorMock = $this->createMock(ServiceLocator::class);
        $serviceLocatorMock
            ->expects($this->once())
            ->method('has')
            ->with(JsonExtractor::class)
            ->willReturn(true)
        ;
        $serviceLocatorMock
            ->expects($this->once())
            ->method('get')
            ->with(JsonExtractor::class)
            ->willReturn(new JsonExtractor())
        ;

        /** @var string $json */
        $json = json_encode([
            'foo' => 123,
        ]);
        $request = new Request(content: $json);

        $this
            ->getMapper($serviceLocatorMock, $request)
            ->map(new Dto(JsonExtractor::class, FooDto::class), new FooDto())
        ;
    }

    public function testItMapsWithADefaultConfiguredExtractor(): void
    {
        $extractorMock = $this
            ->getMockBuilder(ExtractorInterface::class)
            ->getMock()
        ;

        $extractorMock->method('extract')->willReturn(['foo' => 'bar']);

        $serviceLocatorMock = $this->createMock(ServiceLocator::class);

        $serviceLocatorMock
            ->expects($this->once())
            ->method('has')
            ->with(stdClass::class)
            ->willReturn(true)
        ;
        $serviceLocatorMock
            ->expects($this->once())
            ->method('get')
            ->with(stdClass::class)
            ->willReturn($extractorMock)
        ;

        $serviceLocatorMock->method('has')->willReturn(true);
        $serviceLocatorMock->method('get')->willReturn($extractorMock);

        $dto = new stdClass();
        $dto->foo = null;

        $this
            ->getMapper($serviceLocatorMock, defaultExtractor: ExtractorInterface::class)
            ->map(new Dto(stdClass::class), $dto)
        ;

        self::assertSame($dto->foo, 'bar');
    }

    public function testItDispatchesTheMappingRelatedEvents(): void
    {
        $extractorMock = $this
            ->getMockBuilder(ExtractorInterface::class)
            ->getMock()
        ;

        $extractorMock->method('extract')->willReturn([]);

        $serviceLocatorMock = $this->createMock(ServiceLocator::class);

        $serviceLocatorMock
            ->expects(self::once())
            ->method('has')
            ->with(stdClass::class)
            ->willReturn(true)
        ;

        $serviceLocatorMock
            ->expects(self::once())
            ->method('get')
            ->with(stdClass::class)
            ->willReturn($extractorMock)
        ;

        $eventDispatcherMock = $this->createMock(EventDispatcher::class);

        $eventDispatcherMock
            ->expects(self::exactly(2))
            ->method('dispatch')
            ->withConsecutive(
                [$this->isInstanceOf(PreDtoMappingEvent::class)],
                [$this->isInstanceOf(PostDtoMappingEvent::class)]
            )
        ;

        $this
            ->getMapper($serviceLocatorMock, eventDispatcher: $eventDispatcherMock)
            ->map(new Dto(stdClass::class), new stdClass())
        ;
    }

    public function testItThrowsAnExceptionIfTheValidatorIsNotInstalled(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('You cannot validate the DTO if the "validator" component is not available. Try running "composer require symfony/validator".');

        $this
            ->getMapper()
            ->validate(new Dto(validate: true), new stdClass())
        ;
    }

    public function testItDoesNotValidateIfDisabled(): void
    {
        $validatorMock = $this->createMock(ValidatorInterface::class);

        $validatorMock
            ->expects($this->never())
            ->method('validate')
        ;

        $this
            ->getMapper(validator: $validatorMock)
            ->validate(new Dto(validate: false), new stdClass())
        ;
    }

    public function testItDispatchesTheValidationRelatedEvents(): void
    {
        $eventDispatcherMock = $this->createMock(EventDispatcher::class);
        $eventDispatcherMock->expects(self::exactly(2))
            ->method('dispatch')
            ->withConsecutive(
                [$this->isInstanceOf(PreDtoValidationEvent::class)],
                [$this->isInstanceOf(PostDtoValidationEvent::class)]
            )
        ;

        $validatorMock = $this->createMock(ValidatorInterface::class);

        $this
            ->getMapper(eventDispatcher: $eventDispatcherMock, validator: $validatorMock)
            ->validate(new Dto(validate: true), new stdClass())
        ;
    }

    public function testItThrowsAnExceptionOnConstraintViolations(): void
    {
        $this->expectException(DtoValidationException::class);
        $this->expectExceptionMessage('There is one or more constraint violations for the passed DTO.');

        $validatorMock = $this->createMock(ValidatorInterface::class);
        $validatorMock
            ->expects($this->once())
            ->method('validate')
            ->willReturnCallback(fn () => ConstraintViolationList::createFromMessage('test'))
        ;

        $this
            ->getMapper(validator: $validatorMock)
            ->validate(new Dto(stdClass::class, validate: true), new stdClass())
        ;
    }

    public function testItDoesNotThrowAnExceptionOnConstraintViolationsIfConfiguredOnTheAttribute(): void
    {
        $validatorMock = $this->createMock(ValidatorInterface::class);
        $validatorMock
            ->expects($this->once())
            ->method('validate')
            ->willReturnCallback(fn () => ConstraintViolationList::createFromMessage('test'))
        ;

        $this
            ->getMapper(validator: $validatorMock)
            ->validate(new Dto(stdClass::class, validate: true, throwOnViolation: false), new stdClass())
        ;
    }

    public function testItStoresTheConstraintViolationsAsRequestAttribute(): void
    {
        $constraintViolationList = ConstraintViolationList::createFromMessage('test');

        $validatorMock = $this->createMock(ValidatorInterface::class);
        $validatorMock
            ->expects($this->once())
            ->method('validate')
            ->willReturnCallback(fn () => $constraintViolationList)
        ;

        $request = new Request();

        $this
            ->getMapper(request: $request, validator: $validatorMock)
            ->validate(new Dto(stdClass::class, validate: true, throwOnViolation: false), new stdClass())
        ;

        self::assertSame($constraintViolationList, $request->attributes->get('_constraint_violations'));
    }
}
