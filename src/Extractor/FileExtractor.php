<?php

namespace Artyum\RequestDtoMapperBundle\Extractor;

use Symfony\Component\HttpFoundation\Request;

class FileExtractor implements ExtractorInterface
{
    /**
     * {@inheritdoc}
     */
    public function extract(Request $request): array
    {
        return $request->files->all();
    }
}
