<?php

namespace Artyum\RequestDtoMapperBundle\Event;

use Artyum\RequestDtoMapperBundle\Annotation\Dto;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * This event is disptached before the mapping is made, this allows you to alter the Serializer/Denormalizer options or the Request object.
 */
class PreDtoMappingEvent extends Event
{
    private Request $request;

    private string $dto;

    private Dto $dtoAnnotation;

    private array $options;

    public function __construct(Request $request, string $dto, Dto $dtoAnnotation, array $options = [])
    {
        $this->dto = $dto;
        $this->dtoAnnotation = $dtoAnnotation;
        $this->request = $request;
        $this->options = $options;
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function setRequest(Request $request): self
    {
        $this->request = $request;

        return $this;
    }

    public function getDto(): string
    {
        return $this->dto;
    }

    public function setDto(string $dto): self
    {
        $this->dto = $dto;

        return $this;
    }

    public function getDtoAnnotation(): Dto
    {
        return $this->dtoAnnotation;
    }

    public function setDtoAnnotation(Dto $dtoAnnotation): self
    {
        $this->dtoAnnotation = $dtoAnnotation;

        return $this;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function setOptions(array $options): self
    {
        $this->options = $options;

        return $this;
    }
}
