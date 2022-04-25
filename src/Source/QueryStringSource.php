<?php

namespace Artyum\RequestDtoMapperBundle\Source;

use Symfony\Component\HttpFoundation\Request;

class QueryStringSource implements SourceInterface
{
    public function extract(Request $request): array
    {
        return $request->query->all();
    }
}
