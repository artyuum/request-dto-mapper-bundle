<?php

namespace Artyum\RequestDtoMapperBundle\Extractor;

use Symfony\Component\HttpFoundation\Request;

class JsonExtractor implements ExtractorInterface
{
    /**
     * {@inheritdoc}
     */
    public function extract(Request $request): array
    {
        return $request->toArray();
    }
}
