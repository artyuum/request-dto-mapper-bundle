<?php

namespace Artyum\RequestDtoMapperBundle\Event;

use Artyum\RequestDtoMapperBundle\Attribute\Dto;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * This event is dispatched once the validation is done, and it's the last event that is called before your controller is called (if the validation is enabled).
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
