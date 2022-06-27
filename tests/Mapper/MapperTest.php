<?php

namespace Tests\Mapper;

use Artyum\RequestDtoMapperBundle\Attribute\Dto;
use Artyum\RequestDtoMapperBundle\Mapper\Mapper;
use Artyum\RequestDtoMapperBundle\Source\SourceInterface;
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
use Symfony\Component\Serializer\Normalizer\UnwrappingDenormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Validator\Validator\TraceableValidator;

class MapperTest extends TestCase
{
    protected function setUp(): void
    {
    }

    private function getMapper(
        ?array $denormalizerConfiguration = null, ?array $validationConfiguration = null, ?ServiceLocator $serviceLocator = null,
        ?string $defaultSourceConfiguration = null
    ): Mapper {
        $requestStackMock = $this->createMock(RequestStack::class);
        $requestStackMock->method('getMainRequest')->willReturn(new Request(['foo' => 'bar']));

        $eventDispatcherMock = $this->getMockBuilder(EventDispatcher::class)->getMock();
        $validatorMock = $this->getMockBuilder(TraceableValidator::class)->disableOriginalConstructor()->getMock();
        $denormalizerMock = $this->getMockBuilder(DenormalizerInterface::class)->getMock();

        $denormalizerConfiguration = $denormalizerConfiguration ?? [
            'default_options'    => [],
            'additional_options' => []
        ];

        $validationConfiguration = $validationConfiguration ?? [
            'enabled'            => false,
            'default_groups'     => [],
            'additional_groups'  => [],
            'throw_on_violation' => true,
        ];
        $serviceLocator = $serviceLocator ?? new ServiceLocator([]);

        return new Mapper(
            $denormalizerConfiguration,
            $validationConfiguration,
            $serviceLocator,
            $requestStackMock,
            $eventDispatcherMock,
            new Serializer([
                new UnwrappingDenormalizer(),
                new ArrayDenormalizer(),
                new ObjectNormalizer(),
            ]),
            $validatorMock,
            $defaultSourceConfiguration
        );
    }

    public function testItThrowsAnExceptionOnMissingSource(): void
    {
        $this->expectException(LogicException::class);

        $mapper = $this->getMapper();

        $mapper->map(new Dto(stdClass::class), new stdClass());
    }

    public function testItThrowsAnExceptionOnUnknownSource(): void
    {
        $this->expectException(LogicException::class);

        $mapper = $this->getMapper();

        $mapper->map(new Dto(stdClass::class, stdClass::class), new stdClass());
    }

    public function testCanMapWithADefaultSource(): void
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

        $mapper = $this->getMapper(serviceLocator: $serviceLocatorMock, defaultSourceConfiguration: SourceInterface::class);

        $dto = new stdClass();
        $dto->foo = null;

        $mapper->map(new Dto(stdClass::class), $dto);

        self::assertEquals($dto->foo, 'bar');
    }
}
