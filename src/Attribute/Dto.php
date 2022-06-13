<?php

namespace Artyum\RequestDtoMapperBundle\Attribute;

use Artyum\RequestDtoMapperBundle\Source\SourceInterface;
use Attribute;
use LogicException;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_METHOD)]
class Dto
{
    public function __construct(
        private string $subject, private ?string $source = null, private array $methods = [], private array $denormalizerOptions = [],
        private ?bool $validate = null, private array $validationGroups = [], private ?bool $throwOnViolation = null
    ) {
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function setSubject(string $subject): void
    {
        $this->subject = $subject;
    }

    public function getSource(): ?string
    {
        return $this->source;
    }

    public function setSource(?string $source): void
    {
        $this->source = $source;
    }

    public function getMethods(): array
    {
        return $this->methods;
    }

    public function setMethods(array $methods): void
    {
        $this->methods = $methods;
    }

    public function getDenormalizerOptions(): array
    {
        return $this->denormalizerOptions;
    }

    public function setDenormalizerOptions(array $denormalizerOptions): void
    {
        $this->denormalizerOptions = $denormalizerOptions;
    }

    public function getValidate(): ?bool
    {
        return $this->validate;
    }

    public function setValidate(?bool $validate): void
    {
        $this->validate = $validate;
    }

    public function getValidationGroups(): array
    {
        return $this->validationGroups;
    }

    public function setValidationGroups(array $validationGroups): void
    {
        $this->validationGroups = $validationGroups;
    }

    public function getThrowOnViolation(): ?bool
    {
        return $this->throwOnViolation;
    }

    public function setThrowOnViolation(?bool $throwOnViolation): void
    {
        $this->throwOnViolation = $throwOnViolation;
    }
}
