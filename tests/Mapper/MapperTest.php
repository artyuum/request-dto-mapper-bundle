<?php

namespace Tests\Mapper;

use Artyum\RequestDtoMapperBundle\Attribute\Dto;
use Artyum\RequestDtoMapperBundle\Exception\SourceExtractionException;
use Artyum\RequestDtoMapperBundle\Mapper\Mapper;
use LogicException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\TraceableValidator;
use Tests\Mapper\Fixture\FooDto;
use Tests\Mapper\Fixture\SourceNotImplementingSourceInterface;
use Tests\Mapper\Fixture\SourceThrowingException;

class MapperTest extends TestCase
{
    private Mapper $mapper;

    protected function setUp(): void
    {
        $requestStackMock = $this->createMock(RequestStack::class);
        $requestStackMock->method('getMainRequest')->willReturn(new Request());

        $eventDispatcherMock = $this->getMockBuilder(EventDispatcher::class)->getMock();
        $validatorMock = $this->getMockBuilder(TraceableValidator::class)->disableOriginalConstructor()->getMock();
        $serializerMock = $this->getMockBuilder(SerializerInterface::class)->getMock();

        $this>$this->mapper = new Mapper([], [], $requestStackMock, $eventDispatcherMock, $serializerMock, $validatorMock);
    }

    public function testEnsureSourceIsProvided()
    {
        $this->expectException(LogicException::class);

        $this->mapper->map(new Dto(FooDto::class), new FooDto());
    }

    public function testCheckProvidedSourceInterface()
    {
        $this->expectException(LogicException::class);

        $this->mapper->map(new Dto(FooDto::class, SourceNotImplementingSourceInterface::class), new FooDto());
    }

    public function testExceptionWhenExtractingSourceData()
    {
        $this->expectException(SourceExtractionException::class);

        $this->mapper->map(new Dto(FooDto::class, SourceThrowingException::class), new FooDto());
    }
}
