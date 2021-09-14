<?php

namespace Artyum\RequestDtoMapperBundle\Mapper;

use Artyum\RequestDtoMapperBundle\Annotation\Dto;
use Artyum\RequestDtoMapperBundle\Event\PostDtoMappingEvent;
use Artyum\RequestDtoMapperBundle\Event\PreDtoMappingEvent;
use Artyum\RequestDtoMapperBundle\Event\PreDtoValidationEvent;
use Artyum\RequestDtoMapperBundle\Exception\DtoDefinitionException;
use Artyum\RequestDtoMapperBundle\Exception\DtoMappingException;
use Artyum\RequestDtoMapperBundle\Exception\DtoValidationException;
use Doctrine\Common\Annotations\AnnotationReader;
use ReflectionClass;
use ReflectionException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Throwable;

class DtoMapper
{
    private EventDispatcherInterface $eventDispatcher;

    private SerializerInterface $serializer;

    private DenormalizerInterface $denormalizer;

    private ValidatorInterface $validator;

    private array $defaultMapperOptions = [
        ObjectNormalizer::DISABLE_TYPE_ENFORCEMENT => true,
    ];

    public function __construct(
        EventDispatcherInterface $eventDispatcher, SerializerInterface $serializer, DenormalizerInterface $denormalizer,
        ValidatorInterface $validator
    ) {
        $this->serializer = $serializer;
        $this->denormalizer = $denormalizer;
        $this->validator = $validator;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Maps the passed array to the DTO.
     *
     * @throws ExceptionInterface
     */
    private function mapFromArray(array $array, string $dto, array $options = []): DtoInterface
    {
        $options = array_merge($this->defaultMapperOptions, $options);

        /** @var DtoInterface $dto */
        $dto = $this->denormalizer->denormalize($array, $dto, null, $options);

        return $dto;
    }

    /**
     * Maps the passed raw body (assuming its JSON) to the DTO.
     *
     * @throws DtoMappingException
     */
    private function mapFromJson(string $rawBody, string $dto, array $options = []): DtoInterface
    {
        $options = array_merge($this->defaultMapperOptions, $options);

        try {
            /** @var DtoInterface $dto */
            $dto = $this->serializer->deserialize($rawBody, $dto, 'json', $options);
        } catch (Throwable $throwable) {
            throw new DtoMappingException($throwable->getMessage(), 0, $throwable);
        }

        return $dto;
    }

    /**
     * Gets the right Dto annotation class instance from the passed FQCN for the current HTTP method.
     *
     * @throws ReflectionException
     */
    private function getDtoAnnotation(string $dto, string $method): ?Dto
    {
        $reflectionClass = new ReflectionClass($dto);
        $annotationReader = new AnnotationReader();
        $annotations = $annotationReader->getClassAnnotations($reflectionClass);

        // loops through all annotations of the class and return the Dto annotation class instance if found
        foreach ($annotations as $annotation) {
            if (!$annotation instanceof Dto) {
                continue;
            }

            if (in_array($method, $annotation->methods, true)) {
                return $annotation;
            }
        }

        // gets the Dto annotation class instance from the parent class if it wasn't found above
        if ($reflectionClass->getParentClass() && $reflectionClass->getParentClass()->implementsInterface(DtoInterface::class)) {
            return $this->getDtoAnnotation($reflectionClass->getParentClass()->getName(), $method);
        }

        return null;
    }

    /**
     * @return array|bool[]
     */
    public function getDefaultMapperOptions()
    {
        return $this->defaultMapperOptions;
    }

    /**
     * Maps the request data to the DTO.
     *
     * @throws DtoDefinitionException
     * @throws DtoMappingException
     * @throws DtoValidationException
     * @throws ExceptionInterface
     * @throws ReflectionException
     */
    public function map(Request $request, string $dto): DtoInterface
    {
        $dtoAnnotation = $this->getDtoAnnotation($dto, $request->getMethod());

        if (!$dtoAnnotation) {
            throw new DtoDefinitionException('There is no context set for the current HTTP method: ' . $request->getMethod());
        }

        /** @var PreDtoMappingEvent $preDtoMappingEvent */
        $preDtoMappingEvent = $this->eventDispatcher->dispatch(new PreDtoMappingEvent($request, $dto, $dtoAnnotation));

        // calls the proper "mapper" method based on the passed source
        if ($dtoAnnotation->source === 'query_strings') {
            $dto = $this->mapFromArray($preDtoMappingEvent->getRequest()->query->all(), $dto, $preDtoMappingEvent->getOptions());
        } elseif ($dtoAnnotation->source === 'body_parameters') {
            $dto = $this->mapFromArray($preDtoMappingEvent->getRequest()->request->all(), $dto, $preDtoMappingEvent->getOptions());
        } elseif ($dtoAnnotation->source === 'files') {
            $dto = $this->mapFromArray($preDtoMappingEvent->getRequest()->files->all(), $dto, $preDtoMappingEvent->getOptions());
        } elseif ($dtoAnnotation->source === 'json') {
            $dto = $this->mapFromJson($preDtoMappingEvent->getRequest()->getContent(), $dto, $preDtoMappingEvent->getOptions());
        }

        if ($dtoAnnotation->validation) {
            /** @var PreDtoValidationEvent $preDtoValidationEvent */
            $preDtoValidationEvent = $this->eventDispatcher->dispatch(new PreDtoValidationEvent($request, $dto, $dtoAnnotation));

            if (count($errors = $this->validator->validate($preDtoValidationEvent->getDto(), null, $dtoAnnotation->validationGroups))) {
                throw new DtoValidationException($errors);
            }
        }

        /** @var PostDtoMappingEvent $postDtoMappingEvent */
        $postDtoMappingEvent = $this->eventDispatcher->dispatch(new PostDtoMappingEvent($dto));

        return $postDtoMappingEvent->getDto();
    }
}
