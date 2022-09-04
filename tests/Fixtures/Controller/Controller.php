<?php

namespace Tests\Fixtures\Controller;

use stdClass;
use Artyum\RequestDtoMapperBundle\Attribute\Dto;

class Controller
{
    public function controllerNotUsingTheAttribute(object $someArgument): void {}

    #[Dto]
    public function attributeDoesNotHaveAKnownSubject(object $subject): void {}

    #[Dto(subject: stdClass::class)]
    public function subjectIsNotFound(): void {}
}
