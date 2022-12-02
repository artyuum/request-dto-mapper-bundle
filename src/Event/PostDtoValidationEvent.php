<?php

namespace Artyum\RequestDtoMapperBundle\Event;

use Artyum\RequestDtoMapperBundle\Attribute\Dto;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * This event is dispatched once the validation is done. If the validation is enabled, this would be the last event that is dispatched before your controller is called.
 */
class PostDtoValidationEvent extends Event
{
    public function __construct(
        private Request $request, private Dto $attribute, private object $subject,
        private ConstraintViolationListInterface $errors
    ) {
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function getAttribute(): Dto
    {
        return $this->attribute;
    }

    public function getSubject(): object
    {
        return $this->subject;
    }

    public function getErrors(): ConstraintViolationListInterface
    {
        return $this->errors;
    }
}
