<?php

namespace Tests\Mapper;

use Artyum\RequestDtoMapperBundle\Attribute\Dto;
use Artyum\RequestDtoMapperBundle\Event\PostDtoMappingEvent;
use Artyum\RequestDtoMapperBundle\Event\PostDtoValidationEvent;
use Artyum\RequestDtoMapperBundle\Event\PreDtoMappingEvent;
use Artyum\RequestDtoMapperBundle\Event\PreDtoValidationEvent;
use Artyum\RequestDtoMapperBundle\Exception\DtoMappingException;
use Artyum\RequestDtoMapperBundle\Exception\DtoValidationException;
use Artyum\RequestDtoMapperBundle\Exception\SourceExtractionException;
use Artyum\RequestDtoMapperBundle\Mapper\Mapper;
use Artyum\RequestDtoMapperBundle\Source\JsonSource;
use Artyum\RequestDtoMapperBundle\Source\SourceInterface;
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
use Tests\Fixtures\Source\ErroringSource;

class MapperTest extends TestCase
{
    private function getMapper(
        ?ServiceLocator $serviceLocator = null, ?Request $request = null, ?EventDispatcher $eventDispatcher = null,
        ?string $defaultSource = null, ?ValidatorInterface $validator = null
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
        $requestStack->push($request ?? Request::create('/'));

        $eventDispatcher = $eventDispatcher ?? new EventDispatcher();

        return new Mapper(
            $denormalizerConfiguration,
            $validationConfiguration,
            $serviceLocator,
            $requestStack,
            $eventDispatcher,
            $serializer,
            $validator,
            $defaultSource
        );
    }

    public function testItThrowsAnExceptionOnMissingSource(): void
    {
        $this->expectException(LogicException::class);

        $this
            ->getMapper()
            ->map(new Dto(stdClass::class), new stdClass())
        ;
    }

    public function testItThrowsAnExceptionOnUnknownSource(): void
    {
        $this->expectException(LogicException::class);

        $this
            ->getMapper()
            ->map(new Dto(stdClass::class, stdClass::class), new stdClass())
        ;
    }

    public function testItThrowsAnExceptionOnSourceExtractionFailure(): void
    {
        $this->expectException(SourceExtractionException::class);

        $serviceLocatorMock = $this->createMock(ServiceLocator::class);
        $serviceLocatorMock
            ->expects($this->once())
            ->method('has')
            ->with(ErroringSource::class)
            ->willReturn(true)
        ;
        $serviceLocatorMock
            ->expects($this->once())
            ->method('get')
            ->with(ErroringSource::class)
            ->willReturn(new ErroringSource())
        ;

        $this
            ->getMapper($serviceLocatorMock)
            ->map(new Dto(ErroringSource::class, stdClass::class), new stdClass())
        ;
    }

    public function testItThrowsAnExceptionOnMappingFailure(): void
    {
        $this->expectException(DtoMappingException::class);

        $serviceLocatorMock = $this->createMock(ServiceLocator::class);
        $serviceLocatorMock
            ->expects($this->once())
            ->method('has')
            ->with(JsonSource::class)
            ->willReturn(true)
        ;
        $serviceLocatorMock
            ->expects($this->once())
            ->method('get')
            ->with(JsonSource::class)
            ->willReturn(new JsonSource())
        ;

        /** @var string $json */
        $json = json_encode([
            'foo' => 123,
        ]);
        $request = Request::create('/', content: $json);

        $this
            ->getMapper($serviceLocatorMock, $request)
            ->map(new Dto(JsonSource::class, FooDto::class), new FooDto())
        ;
    }

    public function testItMapsWithADefaultConfiguredSource(): void
    {
        $sourceMock = $this
            ->getMockBuilder(SourceInterface::class)
            ->getMock()
        ;

        $sourceMock->method('extract')->willReturn(['foo' => 'bar']);

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
            ->willReturn($sourceMock)
        ;

        $serviceLocatorMock->method('has')->willReturn(true);
        $serviceLocatorMock->method('get')->willReturn($sourceMock);

        $dto = new stdClass();
        $dto->foo = null;

        $this
            ->getMapper($serviceLocatorMock, defaultSource: SourceInterface::class)
            ->map(new Dto(stdClass::class), $dto)
        ;

        $this->assertSame($dto->foo, 'bar');
    }

    public function testItDispatchesTheMappingRelatedEvents(): void
    {
        $sourceMock = $this
            ->getMockBuilder(SourceInterface::class)
            ->getMock()
        ;

        $sourceMock->method('extract')->willReturn([]);

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
            ->willReturn($sourceMock)
        ;

        $eventDispatcherMock = $this->createMock(EventDispatcher::class);

        $eventDispatcherMock
            ->expects($this->exactly(2))
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
        $eventDispatcherMock->expects($this->exactly(2))
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

        $request = Request::create('/');

        $this
            ->getMapper(request: $request, validator: $validatorMock)
            ->validate(new Dto(stdClass::class, validate: true, throwOnViolation: false), new stdClass())
        ;

        $this->assertSame($constraintViolationList, $request->attributes->get('_constraint_violations'));
    }
}
