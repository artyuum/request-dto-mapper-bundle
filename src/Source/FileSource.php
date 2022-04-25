<?php

namespace Artyum\RequestDtoMapperBundle\Source;

use Symfony\Component\HttpFoundation\Request;

class FileSource implements SourceInterface
{
    public function extract(Request $request): array
    {
        return $request->files->all();
    }
}
