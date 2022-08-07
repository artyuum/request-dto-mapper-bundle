<?php

namespace Tests\Fixtures\Source;

use Artyum\RequestDtoMapperBundle\Source\SourceInterface;
use Exception;
use Symfony\Component\HttpFoundation\Request;

class ErroringSource implements SourceInterface
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
