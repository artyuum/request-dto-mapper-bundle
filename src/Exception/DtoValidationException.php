<?php

namespace Artyum\RequestDtoMapperBundle\Exception;

use Exception;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Throwable;

/**
 * Thrown when the DTO validation failed.
 */
class DtoValidationException extends Exception
{
    private ConstraintViolationListInterface $violations;

    public function __construct(ConstraintViolationListInterface $violations, string $message = 'There is one or more constraint violations for the passed DTO.', int $code = 0, Throwable $previous = null)
    {
        $this->violations = $violations;
        parent::__construct($message, $code, $previous);
    }

    public function getViolations(): ConstraintViolationListInterface
    {
        return $this->violations;
    }
}
