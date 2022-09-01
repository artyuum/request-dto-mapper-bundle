<?php

namespace Artyum\RequestDtoMapperBundle\Extractor;

use Symfony\Component\HttpFoundation\Request;

class FormExtractor implements ExtractorInterface
{
    /**
     * {@inheritdoc}
     */
    public function extract(Request $request): array
    {
        return array_merge_recursive($request->request->all(), $request->files->all());
    }
}
