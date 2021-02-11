<?php

namespace Artyum\RequestDtoMapperBundle\Event;

use Artyum\RequestDtoMapperBundle\Annotation\Dto;
use Artyum\RequestDtoMapperBundle\Mapper\DtoInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * This event is dispatched before the validation is made, this allows you to alter the DTO object.
 */
class PreDtoValidationEvent extends Event
{
    private Request $request;

    private DtoInterface $dto;

    private Dto $dtoAnnotation;

    public function __construct(Request $request, DtoInterface $dto, Dto $dtoAnnotation)
    {
        $this->dto = $dto;
        $this->dtoAnnotation = $dtoAnnotation;
        $this->request = $request;
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    /**
     * @return PreDtoValidationEvent
     */
    public function setRequest(Request $request): self
    {
        $this->request = $request;

        return $this;
    }

    public function getDto(): DtoInterface
    {
        return $this->dto;
    }

    public function setDto(DtoInterface $dto): self
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
}
