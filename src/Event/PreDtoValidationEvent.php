<?php

namespace Artyum\RequestDtoMapperBundle\Event;

use Artyum\RequestDtoMapperBundle\Attribute\Dto;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * This event is dispatched before the validation is made, this allows you to alter the DTO before it's being passed to the validator.
 */
class PreDtoValidationEvent extends Event
{
    public function __construct(private Request $request, private Dto $attribute, private object $target)
    {
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function getAttribute(): Dto
    {
        return $this->attribute;
    }

    public function getTarget(): object
    {
        return $this->target;
    }
}
