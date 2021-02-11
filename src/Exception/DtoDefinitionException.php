<?php

namespace Artyum\RequestDtoMapperBundle\Exception;

use Exception;

/**
 * Thrown when no DTO definition was found for the current HTTP method.
 */
class DtoDefinitionException extends Exception
{
}
