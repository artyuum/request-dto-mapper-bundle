<?php

namespace Artyum\RequestDtoMapperBundle\Exception;

use Exception;
use Throwable;

/**
 * Thrown when the request data couldn't be mapped to the DTO.
 */
class DtoMappingFailedException extends Exception
{
    public function __construct(string $message = 'Failed to map the extracted request data to the DTO.', int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
