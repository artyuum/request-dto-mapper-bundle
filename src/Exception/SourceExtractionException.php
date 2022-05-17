<?php

namespace Artyum\RequestDtoMapperBundle\Exception;

use Exception;
use Throwable;

/**
 * Thrown when the extraction of the request data failed.
 */
class SourceExtractionException extends Exception
{
    public function __construct(string $message = 'Failed to extract the request data.', int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
