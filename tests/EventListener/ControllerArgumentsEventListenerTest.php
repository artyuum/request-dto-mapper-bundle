<?php

namespace Tests\EventListener;

use Artyum\RequestDtoMapperBundle\Attribute\Dto;
use Artyum\RequestDtoMapperBundle\EventListener\ControllerArgumentsEventListener;
use Artyum\RequestDtoMapperBundle\Mapper\Mapper;
use LogicException;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Tests\Fixtures\Controller\Controller;

class ControllerArgumentsEventListenerTest extends TestCase
{
    public function testItSubscribesToTheRightEvent(): void
    {
        self::assertArrayHasKey(KernelEvents::CONTROLLER_ARGUMENTS, ControllerArgumentsEventListener::getSubscribedEvents());
    }

    public function testNothingHappensWhenThereIsNoAttribute(): void
    {
        $event = new ControllerArgumentsEvent(
            $this->createMock(HttpKernelInterface::class),
            [new Controller(), 'controllerNotUsingTheAttribute'],
            [],
            new Request(),
            null
        );

        $mapperMock = $this->createMock(Mapper::class);

        $mapperMock->expects(self::never())->method('map');
        $mapperMock->expects(self::never())->method('validate');

        $listener = new ControllerArgumentsEventListener($mapperMock);

        $listener->onKernelControllerArguments($event);
    }

    public function testItThrowsAnExceptionWhenTheAttributeIsUsedWithoutAKnownSubject(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage(sprintf('When used as a method attribute, you must set the $subject argument on the %s attribute.',Dto::class));

        $event = new ControllerArgumentsEvent(
            $this->createMock(HttpKernelInterface::class),
            [new Controller(), 'attributeDoesNotHaveAKnownSubject'],
            [],
            new Request(),
            null
        );

        $mapperMock = $this->createMock(Mapper::class);

        $listener = new ControllerArgumentsEventListener($mapperMock);

        $listener->onKernelControllerArguments($event);
    }

    public function testItThrowsAnExceptionWhenTheSubjectIsNotFound(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage(sprintf('The subject (%s) was not found in the controller arguments.', stdClass::class));

        $event = new ControllerArgumentsEvent(
            $this->createMock(HttpKernelInterface::class),
            [new Controller(), 'subjectIsNotFound'],
            [],
            new Request(),
            null,
        );

        $mapperMock = $this->createMock(Mapper::class);

        $listener = new ControllerArgumentsEventListener($mapperMock);

        $listener->onKernelControllerArguments($event);
    }
}
