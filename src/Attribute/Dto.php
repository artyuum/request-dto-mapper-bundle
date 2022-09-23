<?php

namespace Artyum\RequestDtoMapperBundle\Attribute;

use Attribute;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_METHOD | Attribute::TARGET_PARAMETER)]
class Dto
{
    public function __construct(
        private ?string $extractor = null, private ?string $subject = null, private array|string $methods = [], private array $denormalizerOptions = [],
        private ?bool   $validate = null, private array $validationGroups = [], private ?bool $throwOnViolation = null
    ) {
        $this->methods = is_array($methods) ? $methods : [$methods];
    }

    public function getExtractor(): ?string
    {
        return $this->extractor;
    }

    public function setExtractor(?string $extractor): void
    {
        $this->extractor = $extractor;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function setSubject(string $subject): void
    {
        $this->subject = $subject;
    }

    public function getMethods(): array
    {
        return $this->methods;
    }

    public function setMethods(array|string $methods): void
    {
        $this->methods = is_array($methods) ? $methods : [$methods];
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
