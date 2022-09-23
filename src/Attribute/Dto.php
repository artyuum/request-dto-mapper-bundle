<?php

namespace Artyum\RequestDtoMapperBundle\Attribute;

use Attribute;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_METHOD | Attribute::TARGET_PARAMETER)]
class Dto
{
    private array|string $methods;

    public function __construct(
        private ?string $extractor = null, private ?string $subject = null, array|string $methods = [],
        private array $denormalizerOptions = [], private ?bool $validate = null, private array $validationGroups = [],
        private ?bool $throwOnViolation = null
    ) {
        $this->methods = is_array($methods) ? $methods : [$methods];
    }

    public function getExtractor(): ?string
    {
        return $this->extractor;
    }

    public function setExtractor(?string $extractor): self
    {
        $this->extractor = $extractor;

        return $this;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function setSubject(string $subject): self
    {
        $this->subject = $subject;

        return $this;
    }

    public function getMethods(): array
    {
        return $this->methods;
    }

    public function setMethods(array|string $methods): self
    {
        $this->methods = is_array($methods) ? $methods : [$methods];

        return $this;
    }

    public function getDenormalizerOptions(): array
    {
        return $this->denormalizerOptions;
    }

    public function setDenormalizerOptions(array $denormalizerOptions): self
    {
        $this->denormalizerOptions = $denormalizerOptions;

        return $this;
    }

    public function getValidate(): ?bool
    {
        return $this->validate;
    }

    public function setValidate(?bool $validate): self
    {
        $this->validate = $validate;

        return $this;
    }

    public function getValidationGroups(): array
    {
        return $this->validationGroups;
    }

    public function setValidationGroups(array $validationGroups): self
    {
        $this->validationGroups = $validationGroups;

        return $this;
    }

    public function getThrowOnViolation(): ?bool
    {
        return $this->throwOnViolation;
    }

    public function setThrowOnViolation(?bool $throwOnViolation): self
    {
        $this->throwOnViolation = $throwOnViolation;

        return $this;
    }
}
