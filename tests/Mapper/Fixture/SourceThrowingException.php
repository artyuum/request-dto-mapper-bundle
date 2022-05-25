<?php

namespace Tests\Mapper\Fixture;

use Artyum\RequestDtoMapperBundle\Source\SourceInterface;
use Exception;
use Symfony\Component\HttpFoundation\Request;

class SourceThrowingException implements SourceInterface
{
    public function extract(Request $request): array
    {
        throw new Exception();
    }
}
