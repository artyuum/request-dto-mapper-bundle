<?php

namespace Artyum\RequestDtoMapperBundle\Event;

use Artyum\RequestDtoMapperBundle\Attribute\Dto;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * This event is dispatched before the mapping is made, this allows you to alter the Request object for example.
 */
class PreDtoMappingEvent extends Event
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
