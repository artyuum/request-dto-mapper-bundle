<?php

namespace Artyum\RequestDtoMapperBundle\Event;

use Artyum\RequestDtoMapperBundle\Annotation\Dto;
use Artyum\RequestDtoMapperBundle\Mapper\DtoInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * This event is disptached at the very end of the mapping (and after the validation if enabled), this allows you to alter the DTO before it's passed to the controller.
 */
class PostDtoMappingEvent extends Event
{
    private DtoInterface $dto;

    public function __construct(DtoInterface $dto)
    {
        $this->dto = $dto;
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
}
