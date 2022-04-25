<?php

namespace Artyum\RequestDtoMapperBundle\Mapper;

use Artyum\RequestDtoMapperBundle\Attribute\Dto;
use Artyum\RequestDtoMapperBundle\Event\PostDtoMappingEvent;
use Artyum\RequestDtoMapperBundle\Event\PostDtoValidationEvent;
use Artyum\RequestDtoMapperBundle\Event\PreDtoMappingEvent;
use Artyum\RequestDtoMapperBundle\Event\PreDtoValidationEvent;
use Artyum\RequestDtoMapperBundle\Exception\DtoDefinitionException;
use Artyum\RequestDtoMapperBundle\Exception\DtoMappingException;
use Artyum\RequestDtoMapperBundle\Exception\DtoValidationException;
use Artyum\RequestDtoMapperBundle\Source\SourceInterface;
use Doctrine\Common\Annotations\AnnotationReader;
use ReflectionClass;
use ReflectionException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Throwable;

class Mapper
{
    public function __construct(
        private EventDispatcherInterface $eventDispatcher, private DenormalizerInterface $denormalizer,
        private ValidatorInterface $validator
    ) {
    }

    /**
     * Maps the request data to the DTO.
     * @throws ExceptionInterface
     */
    public function map(Request $request, Dto $attribute, object $subject): void
    {
        $this->eventDispatcher->dispatch(new PreDtoMappingEvent());

        /** @var SourceInterface $source */
        $source = new ($attribute->getSource());

        $this->denormalizer->denormalize($source->extract($request), $attribute->getSubject(), null, [
            AbstractNormalizer::OBJECT_TO_POPULATE => $subject
        ]);

        $this->eventDispatcher->dispatch(new PostDtoMappingEvent());

        if (!$attribute->getValidation()) {
            return;
        }

        $this->eventDispatcher->dispatch(new PreDtoValidationEvent());

        $errors = $this->validator->validate($subject, null, $attribute->getValidationGroups());

        if (count($errors)) {
            throw new DtoValidationException($errors);
        }

        $this->eventDispatcher->dispatch(new PostDtoValidationEvent());
    }
}
