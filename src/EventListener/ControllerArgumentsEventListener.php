<?php

namespace Artyum\RequestDtoMapperBundle\EventListener;

use Artyum\RequestDtoMapperBundle\Attribute\Dto;
use Artyum\RequestDtoMapperBundle\Exception\DtoMappingException;
use Artyum\RequestDtoMapperBundle\Exception\DtoValidationException;
use Artyum\RequestDtoMapperBundle\Exception\SourceExtractionException;
use Artyum\RequestDtoMapperBundle\Mapper\Mapper;
use LogicException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionClass;
use ReflectionException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ControllerArgumentsEventListener implements EventSubscriberInterface
{
    public function __construct(private Mapper $mapper)
    {
    }

    private function getSubjectFromControllerArguments(string $subject, array $arguments): ?object
    {
        foreach ($arguments as $argument) {
            if ($argument instanceof $subject) {
                return $argument;
            }
        }

        return null;
    }

    /**
     * @throws ReflectionException
     * @throws DtoMappingException
     * @throws DtoValidationException
     * @throws SourceExtractionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function onKernelControllerArguments(ControllerArgumentsEvent $event): void
    {
        $controller = $event->getController();
        $request = $event->getRequest();

        if (is_array($controller)) {
            $class = new ReflectionClass($controller[0]);
            $attributes = $class->getMethod($controller[1])->getAttributes(Dto::class);
        } else {
            $class = new ReflectionClass($controller);
            $attributes = $class->getMethod('__invoke')->getAttributes(Dto::class);
        }

        if (!$attributes) {
            return;
        }

        foreach ($attributes as $attribute) {
            /** @var Dto $attribute */
            $attribute = $attribute->newInstance();
            $subject = $this->getSubjectFromControllerArguments($attribute->getSubject(), $event->getArguments());

            if ($attribute->getMethods() && !in_array($request->getMethod(), $attribute->getMethods(), true)) {
                continue;
            }

            if (!$subject) {
                throw new LogicException(sprintf('The subject (%s) was not found in the controller arguments.', $attribute->getSubject()));
            }

            $this->mapper->map($attribute, $subject);
            $this->mapper->validate($attribute, $subject);
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER_ARGUMENTS => 'onKernelControllerArguments',
        ];
    }
}
