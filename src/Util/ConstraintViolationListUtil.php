<?php

namespace Artyum\RequestDtoMapperBundle\Util;

use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * A simple utility class that can be used to convert the passed violations into a key => value array.
 */
class ConstraintViolationListUtil
{
    /**
     * Gets the validator errors.
     */
    public static function toArray(ConstraintViolationListInterface $violations): ?array
    {
        $errors = null;

        // loops through all errors and stores only the needed information to be displayed
        foreach ($violations as $violation) {
            $errors[$violation->getPropertyPath()][] = $violation->getMessage();
        }

        return $errors;
    }
}
