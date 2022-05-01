<?php

namespace Artyum\RequestDtoMapperBundle\Mapper;

use Artyum\RequestDtoMapperBundle\Attribute\Dto;
use Artyum\RequestDtoMapperBundle\Event\PostDtoMappingEvent;
use Artyum\RequestDtoMapperBundle\Event\PostDtoValidationEvent;
use Artyum\RequestDtoMapperBundle\Event\PreDtoMappingEvent;
use Artyum\RequestDtoMapperBundle\Event\PreDtoValidationEvent;
use Artyum\RequestDtoMapperBundle\Exception\DtoValidationException;
use Artyum\RequestDtoMapperBundle\Source\SourceInterface;
use LogicException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class Mapper
{
    public function __construct(
        private array $denormalizerConfiguration, private array $validationConfiguration,
        private EventDispatcherInterface $eventDispatcher, private DenormalizerInterface $denormalizer,
        private ?ValidatorInterface $validator = null, private ?string $defaultSourceConfiguration = null
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
     * Maps the request data to the DTO.
     *
     * @throws ExceptionInterface
     * @throws DtoValidationException
     */
    public function map(Request $request, Dto $attribute, object $subject): void
    {
        $this->eventDispatcher->dispatch(new PreDtoMappingEvent());

        $source = $attribute->getSource() ?? $this->defaultSourceConfiguration;

        if (!$source) {
            throw new LogicException('You must set a source on the attribute or in the configuration file.');
        }

        /** @var SourceInterface $source */
        $source = new ($source)();

        $denormalizerOptions = $this->getDenormalizerOptions($attribute->getDenormalizerOptions());
        $denormalizerOptions[AbstractNormalizer::OBJECT_TO_POPULATE] = $subject;

        $this->denormalizer->denormalize($source->extract($request), $attribute->getSubject(), null, $denormalizerOptions);

        $this->eventDispatcher->dispatch(new PostDtoMappingEvent());

        if (!$attribute->getValidate() || !$this->validationConfiguration['enabled']) {
            return;
        }

        if (!$this->validator) {
            throw new LogicException('You cannot validate the DTO if the "validator" component is not available. Try running "composer require symfony/validator".');
        }

        $this->eventDispatcher->dispatch(new PreDtoValidationEvent());

        $validationGroups = $this->getValidationGroups($attribute->getValidationGroups());

        $errors = $this->validator->validate($subject, null, $validationGroups);

        if (count($errors) && $this->validationConfiguration['throw_on_violation']) {
            throw new DtoValidationException($errors);
        }

        $this->eventDispatcher->dispatch(new PostDtoValidationEvent());
    }
}
