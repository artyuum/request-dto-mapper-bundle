<?php

namespace Artyum\RequestDtoMapperBundle\Source;

use Symfony\Component\HttpFoundation\Request;

interface SourceInterface {
    /**
     * Extracts the data from the request.
     */
    public function extract(Request $request): array;
}
