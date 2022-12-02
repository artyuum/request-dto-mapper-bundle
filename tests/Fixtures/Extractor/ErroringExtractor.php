<?php

namespace Tests\Fixtures\Extractor;

use Artyum\RequestDtoMapperBundle\Extractor\ExtractorInterface;
use Exception;
use Symfony\Component\HttpFoundation\Request;

class ErroringExtractor implements ExtractorInterface
{
    /**
     * {@inheritDoc}
     *
     * @throws Exception
     */
    public function extract(Request $request): array
    {
        throw new Exception('Extraction failed.');
    }
}
