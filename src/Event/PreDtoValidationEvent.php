<?php

namespace Artyum\RequestDtoMapperBundle\Event;

use Artyum\RequestDtoMapperBundle\Attribute\Dto;
use Artyum\RequestDtoMapperBundle\Mapper\DtoInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * This event is dispatched before the validation is made, this allows you to alter the DTO before it's being passed to the validator.
 */
class PreDtoValidationEvent extends Event
{
}
