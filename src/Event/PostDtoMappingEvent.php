<?php

namespace Artyum\RequestDtoMapperBundle\Event;

use Artyum\RequestDtoMapperBundle\Attribute\Dto;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * This event is dispatched once the mapping is done, and it's the last event that is called before your controller is called (if the validation is NOT enabled).
 */
class PostDtoMappingEvent extends Event
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
