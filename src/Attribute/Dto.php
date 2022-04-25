<?php

namespace Artyum\RequestDtoMapperBundle\Attribute;

use Artyum\RequestDtoMapperBundle\Source\SourceInterface;
use Attribute;
use LogicException;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_METHOD)]
class Dto
{
    public function __construct(
        private string $subject, private string $source, private array $methods = [], private array $options = [],
        private bool $validation = false, private array $validationGroups = []
    ) {
        if (!class_implements($this->source, SourceInterface::class)) {
            throw new LogicException(sprintf(
                'The passed source "%s" must implement "%s".',
                $this->source,
                SourceInterface::class,
            ));
        }
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function getSource(): string
    {
        return $this->source;
    }

    public function getMethods(): ?array
    {
        return $this->methods;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function getValidation(): bool
    {
        return $this->validation;
    }

    public function getValidationGroups(): array
    {
        return $this->validationGroups;
    }
}
