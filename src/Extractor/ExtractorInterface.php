<?php

namespace Artyum\RequestDtoMapperBundle\Extractor;

use Symfony\Component\HttpFoundation\Request;

interface ExtractorInterface
{
    /**
     * Extracts the data from the request.
     */
    public function extract(Request $request): array;
}
