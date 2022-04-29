<?php

namespace Artyum\RequestDtoMapperBundle\Source;

use Symfony\Component\HttpFoundation\Request;

class JsonSource implements SourceInterface
{
    /**
     * {@inheritdoc}
     */
    public function extract(Request $request): array
    {
        return $request->toArray();
    }
}
