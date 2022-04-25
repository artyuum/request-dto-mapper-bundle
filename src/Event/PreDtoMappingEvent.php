<?php

namespace Artyum\RequestDtoMapperBundle\Event;

use Artyum\RequestDtoMapperBundle\Attribute\Dto;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * This event is dispatched before the mapping is made, this allows you to alter the Serializer/Denormalizer options or the Request object.
 */
class PreDtoMappingEvent extends Event
{
    public function __construct()
    {
    }
}
