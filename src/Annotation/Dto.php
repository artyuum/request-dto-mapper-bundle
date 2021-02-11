<?php

namespace Artyum\RequestDtoMapperBundle\Annotation;

/**
 * @Annotation
 * @Target({"CLASS"})
 */
class Dto
{
    public array $methods;

    /**
     * @Enum({"json", "query_strings", "body_parameters"})
     */
    public string $source;

    public bool $validation = true;

    public ?array $validationGroups = null;
}
