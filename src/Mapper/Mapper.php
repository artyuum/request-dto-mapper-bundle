<?php

namespace Artyum\RequestDtoMapperBundle\Mapper;

use Artyum\RequestDtoMapperBundle\Attribute\Dto;
use Artyum\RequestDtoMapperBundle\Event\PostDtoMappingEvent;
use Artyum\RequestDtoMapperBundle\Event\PostDtoValidationEvent;
use Artyum\RequestDtoMapperBundle\Event\PreDtoMappingEvent;
use Artyum\RequestDtoMapperBundle\Event\PreDtoValidationEvent;
use Artyum\RequestDtoMapperBundle\Exception\DtoMappingException;
use Artyum\RequestDtoMapperBundle\Exception\DtoValidationException;
use Artyum\RequestDtoMapperBundle\Exception\SourceExtractionException;
use Artyum\RequestDtoMapperBundle\Source\SourceInterface;
use LogicException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Throwable;

class Mapper
{
    public function __construct(
        private array $denormalizerConfiguration, private array $validationConfiguration,
        private ServiceLocator $sourceLocator, private RequestStack $requestStack,
        private EventDispatcherInterface $eventDispatcher, private DenormalizerInterface $denormalizer,
        private ?ValidatorInterface $validator = null,private ?string $defaultSourceConfiguration = null
    ) {
    }

    /**
     * Gets the options to pass to the denormalizer.
     */
    private function getDenormalizerOptions(array $attributeDenormalizerOptions = []): array
    {
        if ($attributeDenormalizerOptions) {
            $options = $attributeDenormalizerOptions;
        } else {
            $options = $this->denormalizerConfiguration['default_options'];
        }

        return array_merge_recursive($options, $this->denormalizerConfiguration['additional_options']);
    }

    /**
     * Gets the groups to the pass to the validator.
     */
    private function getValidationGroups(array $attributeValidationGroups = []): array
    {
        if ($attributeValidationGroups) {
            $groups = $attributeValidationGroups;
        } else {
            $groups = $this->validationConfiguration['default_groups'];
        }

        return array_merge_recursive($groups, $this->validationConfiguration['additional_groups']);
    }

    /**
     * Whether to validate the DTO.
     */
    private function canValidate(Dto $attribute): bool
    {
        if (is_bool($attribute->getValidate())) {
            return $attribute->getValidate();
        }

        return $this->validationConfiguration['enabled'];
    }

    /**
     * Validates the target (already mapped DTO).
     *
     * @throws DtoValidationException
     */
    public function validate(Dto $attribute, object $target): void
    {
        if (!$this->canValidate($attribute)) {
            return;
        }

        if (!$this->validator) {
            throw new LogicException(
                'You cannot validate the DTO if the "validator" component is not available. Try running "composer require symfony/validator".'
            );
        }

        $request = $this->requestStack->getMainRequest();

        $this->eventDispatcher->dispatch(new PreDtoValidationEvent($request, $attribute, $target));

        $validationGroups = $this->getValidationGroups($attribute->getValidationGroups());

        $errors = $this->validator->validate($target, null, $validationGroups);

        if ($errors->count()) {
            $request->attributes->set('_constraint_violations', $errors);

            if ($this->validationConfiguration['throw_on_violation']) {
                throw new DtoValidationException($errors);
            }
        }

        $this->eventDispatcher->dispatch(new PostDtoValidationEvent($request, $attribute, $target, $errors));
    }

    /**
     * Maps the request data to the DTO.
     *
     * @throws DtoMappingException
     * @throws SourceExtractionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function map(Dto $attribute, object $target): void
    {
        $request = $this->requestStack->getMainRequest();

        $this->eventDispatcher->dispatch(new PreDtoMappingEvent($request, $attribute, $target));

        $source = $attribute->getSource() ?? $this->defaultSourceConfiguration;

        if (!$source) {
            throw new LogicException('You must set a source either on the attribute or in the configuration file.');
        }

        if (!$this->sourceLocator->has($source)) {
            throw new LogicException('Unable to the find the passed source in the container. Make sure it\'s tagged as "artyum_request_dto_mapper.source".');
        }

        /** @var SourceInterface $sourceInstance */
        $sourceInstance = $this->sourceLocator->get($source);

        if (!($sourceInstance instanceof (SourceInterface::class))) {
            throw new LogicException(sprintf(
                'The passed source "%s" must implement "%s".',
                $source,
                SourceInterface::class,
            ));
        }

        try {
            $data = $sourceInstance->extract($request);
        } catch (Throwable $throwable) {
            throw new SourceExtractionException(previous: $throwable);
        }

        $denormalizerOptions = $this->getDenormalizerOptions($attribute->getDenormalizerOptions());
        $denormalizerOptions[AbstractNormalizer::OBJECT_TO_POPULATE] = $target;

        try {
            $this->denormalizer->denormalize($data, $attribute->getTarget(), null, $denormalizerOptions);
        } catch (Throwable $throwable) {
            throw new DtoMappingException(previous: $throwable);
        }

        $this->eventDispatcher->dispatch(new PostDtoMappingEvent($request, $attribute, $target));
    }
}
