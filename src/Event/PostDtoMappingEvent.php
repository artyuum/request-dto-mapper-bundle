<?php

namespace Artyum\RequestDtoMapperBundle\Event;

use Artyum\RequestDtoMapperBundle\Attribute\Dto;
use Artyum\RequestDtoMapperBundle\Mapper\DtoInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * This event is dispatched once the mapping is done, and it's the last event that is called before your controller is called (if the validation is NOT enabled).
 */
class PostDtoMappingEvent extends Event
{
}
