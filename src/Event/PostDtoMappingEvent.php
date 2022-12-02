<?php

namespace Artyum\RequestDtoMapperBundle\Event;

use Artyum\RequestDtoMapperBundle\Attribute\Dto;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * This event is dispatched once the mapping is done. If the validation is disabled, this would be the last event that is dispatched before your controller is called.
 */
class PostDtoMappingEvent extends Event
{
    public function __construct(private Request $request, private Dto $attribute, private object $subject)
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

    public function getSubject(): object
    {
        return $this->subject;
    }
}
