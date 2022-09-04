<?php

namespace Tests\Fixtures\Controller;

use Artyum\RequestDtoMapperBundle\Attribute\Dto;

class Controller
{
    public function controllerNotUsingTheAttribute(object $someArgument): void
    {

    }

    #[Dto]
    public function subjectNotPresentInArgument(object $subject): void
    {

    }
}
