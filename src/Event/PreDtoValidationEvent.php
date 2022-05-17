<?php

namespace Artyum\RequestDtoMapperBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * This event is dispatched before the validation is made, this allows you to alter the DTO before it's being passed to the validator.
 */
class PreDtoValidationEvent extends Event
{
}
