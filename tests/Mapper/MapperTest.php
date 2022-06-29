<?php

namespace Tests\Mapper;

use Artyum\RequestDtoMapperBundle\Attribute\Dto;
use Artyum\RequestDtoMapperBundle\Exception\DtoMappingException;
use Artyum\RequestDtoMapperBundle\Exception\SourceExtractionException;
use Artyum\RequestDtoMapperBundle\Mapper\Mapper;
use Artyum\RequestDtoMapperBundle\Source\SourceInterface;
use Exception;
use LogicException;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Validator\Validator\TraceableValidator;

class MapperTest extends TestCase
{
    private RequestStack $requestStackMock;
    private EventDispatcher $eventDispatcherMock;
    private DenormalizerInterface $denormalizer;
    private array $denormalizerConfiguration = [
        'default_options'    => [],
        'additional_options' => [],
    ];
    private array $validationConfiguration = [
        'enabled'            => false,
        'default_groups'     => [],
        'additional_groups'  => [],
        'throw_on_violation' => true,
    ];
    private ServiceLocator $serviceLocatorMock;
    private ?string $defaultSourceConfiguration = null;

    protected function setUp(): void
    {
        $this->requestStackMock = $this->createMock(RequestStack::class);
        $this->requestStackMock->method('getMainRequest')->willReturn(new Request(['foo' => 'bar']));

        $this->eventDispatcherMock = $this->getMockBuilder(EventDispatcher::class)->getMock();
        $this->validatorMock = $this->getMockBuilder(TraceableValidator::class)->disableOriginalConstructor()->getMock();
        $this->denormalizer = new Serializer([
            new ArrayDenormalizer(),
            new ObjectNormalizer(),
        ]);
        $this->serviceLocatorMock = new ServiceLocator([]);
    }

    public function testItThrowsAnExceptionOnMissingSource(): void
    {
        $this->expectException(LogicException::class);

        $mapper = new Mapper(
            $this->denormalizerConfiguration,
            $this->validationConfiguration,
            $this->serviceLocatorMock,
            $this->requestStackMock,
            $this->eventDispatcherMock,
            $this->denormalizer,
            $this->validatorMock,
        );

        $mapper->map(new Dto(stdClass::class), new stdClass());
    }

    public function testItThrowsAnExceptionOnUnknownSource(): void
    {
        $this->expectException(LogicException::class);

        $mapper = new Mapper(
            $this->denormalizerConfiguration,
            $this->validationConfiguration,
            $this->serviceLocatorMock,
            $this->requestStackMock,
            $this->eventDispatcherMock,
            $this->denormalizer,
            $this->validatorMock,
        );

        $mapper->map(new Dto(stdClass::class, stdClass::class), new stdClass());
    }

    public function testItThrowsAnExceptionOnSourceExtractionFailure(): void
    {
        $this->expectException(SourceExtractionException::class);

        $sourceMock = $this
            ->getMockBuilder(SourceInterface::class)
            ->getMock()
        ;

        $sourceMock->method('extract')->willReturnCallback(function () {
            throw new Exception();
        });

        $serviceLocatorMock = $this
            ->getMockBuilder(ServiceLocator::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['has', 'get'])
            ->getMock()
        ;

        $serviceLocatorMock->method('has')->willReturn(true);
        $serviceLocatorMock->method('get')->willReturn($sourceMock);

        $mapper = new Mapper(
            $this->denormalizerConfiguration,
            $this->validationConfiguration,
            $serviceLocatorMock,
            $this->requestStackMock,
            $this->eventDispatcherMock,
            $this->denormalizer,
            $this->validatorMock,
        );

        $mapper->map(new Dto(stdClass::class, stdClass::class), new stdClass());
    }

    public function testItThrowsAnExceptionOnMappingFailure(): void
    {
        $this->expectException(DtoMappingException::class);

        $sourceMock = $this
            ->getMockBuilder(SourceInterface::class)
            ->getMock()
        ;

        $sourceMock->method('extract')->willReturn(['foo' => 'bar']);

        $serviceLocatorMock = $this
            ->getMockBuilder(ServiceLocator::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['has', 'get'])
            ->getMock()
        ;

        $serviceLocatorMock->method('has')->willReturn(true);
        $serviceLocatorMock->method('get')->willReturn($sourceMock);

        $denormalizerMock = $this
            ->getMockBuilder(DenormalizerInterface::class)
            ->getMock()
        ;

        $denormalizerMock->method('denormalize')->willReturnCallback(function () {
            throw new Exception();
        });

        $mapper = new Mapper(
            $this->denormalizerConfiguration,
            $this->validationConfiguration,
            $serviceLocatorMock,
            $this->requestStackMock,
            $this->eventDispatcherMock,
            $denormalizerMock,
            $this->validatorMock,
        );

        $mapper->map(new Dto(stdClass::class, stdClass::class), new stdClass());
    }

    public function testItMapsWithADefaultConfiguredSource(): void
    {
        $sourceMock = $this
            ->getMockBuilder(SourceInterface::class)
            ->getMock()
        ;

        $sourceMock->method('extract')->willReturn(['foo' => 'bar']);

        $serviceLocatorMock = $this
            ->getMockBuilder(ServiceLocator::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['has', 'get'])
            ->getMock()
        ;

        $serviceLocatorMock->method('has')->willReturn(true);
        $serviceLocatorMock->method('get')->willReturn($sourceMock);

        $mapper = new Mapper(
            $this->denormalizerConfiguration,
            $this->validationConfiguration,
            $serviceLocatorMock,
            $this->requestStackMock,
            $this->eventDispatcherMock,
            $this->denormalizer,
            $this->validatorMock,
            SourceInterface::class
        );

        $dto = new stdClass();
        $dto->foo = null;

        $mapper->map(new Dto(stdClass::class), $dto);

        self::assertSame($dto->foo, 'bar');
    }
}
