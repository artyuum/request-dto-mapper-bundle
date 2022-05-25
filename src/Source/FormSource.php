<?php

namespace Artyum\RequestDtoMapperBundle\Source;

use Symfony\Component\HttpFoundation\Request;

class FormSource implements SourceInterface
{
    /**
     * {@inheritdoc}
     */
    public function extract(Request $request): array
    {
        return array_merge_recursive($request->request->all(), $request->files->all());
    }
}
