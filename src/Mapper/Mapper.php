<?php

namespace Artyum\RequestDtoMapperBundle\Mapper;

use Artyum\RequestDtoMapperBundle\Attribute\Dto;
use Artyum\RequestDtoMapperBundle\Event\PostDtoMappingEvent;
use Artyum\RequestDtoMapperBundle\Event\PostDtoValidationEvent;
use Artyum\RequestDtoMapperBundle\Event\PreDtoMappingEvent;
use Artyum\RequestDtoMapperBundle\Event\PreDtoValidationEvent;
use Artyum\RequestDtoMapperBundle\Exception\DtoMappingException;
use Artyum\RequestDtoMapperBundle\Exception\DtoValidationException;
use Artyum\RequestDtoMapperBundle\Exception\ExtractionFailedException;
use Artyum\RequestDtoMapperBundle\Extractor\ExtractorInterface;
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
        private ServiceLocator $extractorLocator, private RequestStack $requestStack,
        private EventDispatcherInterface $eventDispatcher, private DenormalizerInterface $denormalizer,
        private ?ValidatorInterface $validator = null, private ?string $defaultExtractorConfiguration = null
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
     * Validates the subject (already mapped DTO).
     *
     * @throws DtoValidationException
     */
    public function validate(Dto $attribute, object $subject): void
    {
        if (!$this->canValidate($attribute)) {
            return;
        }

        if (!$this->validator) {
            throw new LogicException('You cannot validate the DTO if the "validator" component is not available. Try running "composer require symfony/validator".');
        }

        $request = $this->requestStack->getMainRequest();

        $this->eventDispatcher->dispatch(new PreDtoValidationEvent($request, $attribute, $subject));

        $validationGroups = $this->getValidationGroups($attribute->getValidationGroups());

        $errors = $this->validator->validate($subject, null, $validationGroups);

        if ($errors->count()) {
            $request->attributes->set('_constraint_violations', $errors);

            $canThrowOnViolation = $attribute->getThrowOnViolation();

            if (!is_bool($canThrowOnViolation)) {
                $canThrowOnViolation = $this->validationConfiguration['throw_on_violation'];
            }

            if ($canThrowOnViolation) {
                throw new DtoValidationException($errors);
            }
        }

        $this->eventDispatcher->dispatch(new PostDtoValidationEvent($request, $attribute, $subject, $errors));
    }

    /**
     * Maps the request data to the DTO.
     *
     * @throws DtoMappingException
     * @throws ExtractionFailedException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function map(Dto $attribute, object $subject): void
    {
        $request = $this->requestStack->getMainRequest();

        $this->eventDispatcher->dispatch(new PreDtoMappingEvent($request, $attribute, $subject));

        $extractor = $attribute->getExtractor() ?? $this->defaultExtractorConfiguration;

        if (!$extractor) {
            throw new LogicException('You must set an extractor either on the attribute or in the configuration file.');
        }

        if (!$this->extractorLocator->has($extractor)) {
            throw new LogicException(sprintf('Unable to the find the passed extractor "%s" in the container. Make sure it\'s tagged as "artyum_request_dto_mapper.extractor".', $extractor));
        }

        /** @var ExtractorInterface $extractorInstance */
        $extractorInstance = $this->extractorLocator->get($extractor);

        try {
            $data = $extractorInstance->extract($request);
        } catch (Throwable $throwable) {
            throw new ExtractionFailedException(previous: $throwable);
        }

        $denormalizerOptions = $this->getDenormalizerOptions($attribute->getDenormalizerOptions());
        $denormalizerOptions[AbstractNormalizer::OBJECT_TO_POPULATE] = $subject;

        try {
            $this->denormalizer->denormalize($data, $subject::class, null, $denormalizerOptions);
        } catch (Throwable $throwable) {
            throw new DtoMappingException(previous: $throwable);
        }

        $this->eventDispatcher->dispatch(new PostDtoMappingEvent($request, $attribute, $subject));
    }
}
