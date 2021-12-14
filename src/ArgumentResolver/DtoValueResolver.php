<?php

namespace Artyum\RequestDtoMapperBundle\ArgumentResolver;

use Artyum\RequestDtoMapperBundle\Mapper\DtoInterface;
use Artyum\RequestDtoMapperBundle\Mapper\DtoMapper;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

/**
 * Responsible for calling the DtoMapper when an of argument implementing the DtoInterface is needed in the controller arguments.
 */
class DtoValueResolver implements ArgumentValueResolverInterface
{
    private DtoMapper $dtoMapper;

    public function __construct(DtoMapper $dtoMapper)
    {
        $this->dtoMapper = $dtoMapper;
    }

    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        return
            class_exists($argument->getType()) &&
            in_array(DtoInterface::class, class_implements($argument->getType()), true)
        ;
    }

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        yield $this->dtoMapper->map($request, $argument->getType());
    }
}
