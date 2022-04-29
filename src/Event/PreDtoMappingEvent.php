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
}
