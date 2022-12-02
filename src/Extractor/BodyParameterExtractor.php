<?php

namespace Artyum\RequestDtoMapperBundle\Extractor;

use Symfony\Component\HttpFoundation\Request;

class BodyParameterExtractor implements ExtractorInterface
{
    /**
     * {@inheritdoc}
     */
    public function extract(Request $request): array
    {
        return $request->request->all();
    }
}
