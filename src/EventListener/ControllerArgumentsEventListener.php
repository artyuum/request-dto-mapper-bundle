<?php

namespace Artyum\RequestDtoMapperBundle\EventListener;

use Artyum\RequestDtoMapperBundle\Attribute\Dto;
use Artyum\RequestDtoMapperBundle\Exception\DtoMappingFailedException;
use Artyum\RequestDtoMapperBundle\Exception\DtoValidationFailedException;
use Artyum\RequestDtoMapperBundle\Exception\ExtractionFailedException;
use Artyum\RequestDtoMapperBundle\Mapper\Mapper;
use LogicException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionException;
use ReflectionMethod;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ControllerArgumentsEventListener implements EventSubscriberInterface
{
    public function __construct(private Mapper $mapper)
    {
    }

    /**
     * Gets the subject instance from the passed controller arguments.
     */
    private function getSubjectInstanceFromControllerArguments(string $subject, array $arguments): ?object
    {
        foreach ($arguments as $argument) {
            if ($argument instanceof $subject) {
                return $argument;
            }
        }

        return null;
    }

    /**
     * Gets the ReflectionMethod from the controller.
     *
     * @throws ReflectionException
     */
    private function getReflectionMethod(callable $controller): ReflectionMethod
    {
        if (is_array($controller)) {
            $class = $controller[0];
            $method = $controller[1];
        } else {
            /** @var object $controller */
            $class = $controller;
            $method = '__invoke';
        }

        return new ReflectionMethod($class, $method);
    }

    /**
     * Extracts the Dto subjects from the passed ReflectionMethod.
     */
    private function extractFromMethod(ReflectionMethod $reflectionMethod): array
    {
        $subjects = [];
        $alreadyExtractedSubjects = [];
        $reflectionAttributes = $reflectionMethod->getAttributes(Dto::class);

        foreach ($reflectionAttributes as $reflectionAttribute) {
            /** @var Dto $dtoAttribute */
            $dtoAttribute = $reflectionAttribute->newInstance();

            if (!$dtoAttribute->getSubject()) {
                throw new LogicException(sprintf('When used as a method attribute, you must set the $subject argument on the %s attribute.', Dto::class));
            }

            if (in_array($dtoAttribute->getSubject(), $alreadyExtractedSubjects, true)) {
                throw new LogicException(sprintf('The subject %s is present more than once in the method arguments. In that case, you must configure the attribute directly on the argument itself and not on the method.', $dtoAttribute->getSubject()));
            }

            $alreadyExtractedSubjects[] = $dtoAttribute->getSubject();
            $subjects[$dtoAttribute->getSubject()][] = $reflectionAttribute->newInstance();
        }

        return $subjects;
    }

    /**
     * Extracts the Dto subjects from the passed ReflectionMethod parameters.
     */
    private function extractFromParameters(ReflectionMethod $reflectionMethod): array
    {
        $subjects = [];

        foreach ($reflectionMethod->getParameters() as $index => $reflectionParameter) {
            $reflectionAttributes = $reflectionParameter->getAttributes(Dto::class);

            if (!$reflectionAttributes) {
                continue;
            }

            $subjects[$index] = [
                /* @phpstan-ignore-next-line */
                'argument' => $reflectionParameter->getType()->getName(),
            ];

            foreach ($reflectionAttributes as $reflectionAttribute) {
                /** @var Dto $dtoAttribute */
                $dtoAttribute = $reflectionAttribute->newInstance();

                $subjects[$index]['attributes'][] = $dtoAttribute;
            }
        }

        return $subjects;
    }

    /**
     * @throws ReflectionException
     * @throws DtoMappingFailedException
     * @throws DtoValidationFailedException
     * @throws ExtractionFailedException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function onKernelControllerArguments(ControllerArgumentsEvent $event): void
    {
        $controller = $event->getController();
        $request = $event->getRequest();
        $reflectionMethod = $this->getReflectionMethod($controller);

        $dtoAttributesFromMethod = $this->extractFromMethod($reflectionMethod);
        $dtoAttributesFromParameters = $this->extractFromParameters($reflectionMethod);

        if (!$dtoAttributesFromMethod && !$dtoAttributesFromParameters) {
            return;
        }

        foreach ($dtoAttributesFromMethod as $subjectFqcn => $dtoAttributes) {
            foreach ($dtoAttributes as $dtoAttribute) {
                if ($dtoAttribute->getMethods() && !in_array($request->getMethod(), $dtoAttribute->getMethods(), true)) {
                    continue;
                }

                $subjectInstance = $this->getSubjectInstanceFromControllerArguments($subjectFqcn, $event->getArguments());

                if (!$subjectInstance) {
                    throw new LogicException(sprintf('The subject (%s) was not found in the controller arguments.', $subjectFqcn));
                }

                $this->mapper->map($dtoAttribute, $subjectInstance);
                $this->mapper->validate($dtoAttribute, $subjectInstance);
            }
        }

        foreach ($dtoAttributesFromParameters as $index => $dtoAttributesFromParameter) {
            $subjectInstance = $event->getArguments()[$index];

            foreach ($dtoAttributesFromParameter['attributes'] as $dtoAttribute) {
                $this->mapper->map($dtoAttribute, $subjectInstance);
                $this->mapper->validate($dtoAttribute, $subjectInstance);
            }
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
