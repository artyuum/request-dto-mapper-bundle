<?php

namespace Artyum\RequestDtoMapperBundle\EventListener;

use Artyum\RequestDtoMapperBundle\Attribute\Dto;
use Artyum\RequestDtoMapperBundle\Mapper\Mapper;
use LogicException;
use ReflectionClass;
use ReflectionException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

class ControllerArgumentsEventListener implements EventSubscriberInterface
{
    public function __construct(private Mapper $mapper)
    {
    }

    private function getTargetFromControllerArguments(string $target, array $arguments): ?object
    {
        foreach ($arguments as $argument) {
            if ($argument instanceof $target) {
                return $argument;
            }
        }

        return null;
    }

    /**
     * @throws ReflectionException
     * @throws ExceptionInterface
     */
    public function onKernelControllerArguments(ControllerArgumentsEvent $event)
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
            $target = $this->getTargetFromControllerArguments($attribute->getTarget(), $event->getArguments());

            if ($attribute->getMethods() && !in_array($request->getMethod(), $attribute->getMethods())) {
                continue;
            }

            if (!$target) {
                throw new LogicException(sprintf(
                    'The target DTO (%s) was not found in the controller arguments.',
                    $attribute->getTarget()
                ));
            }

            $this->mapper->map($attribute, $target);
            $this->mapper->validate($attribute, $target);
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER_ARGUMENTS => 'onKernelControllerArguments'
        ];
    }
}
