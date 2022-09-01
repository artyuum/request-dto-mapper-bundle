<?php

use Artyum\RequestDtoMapperBundle\EventListener\ControllerArgumentsEventListener;
use Artyum\RequestDtoMapperBundle\Mapper\Mapper;
use Artyum\RequestDtoMapperBundle\Extractor\BodyParameterExtractor;
use Artyum\RequestDtoMapperBundle\Extractor\FileExtractor;
use Artyum\RequestDtoMapperBundle\Extractor\FormExtractor;
use Artyum\RequestDtoMapperBundle\Extractor\JsonExtractor;
use Artyum\RequestDtoMapperBundle\Extractor\QueryStringExtractor;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\param;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_locator;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

return static function (ContainerConfigurator $container): void {
    $container->services()
        // Listeners
        ->set(ControllerArgumentsEventListener::class)
        ->args([
            service(Mapper::class),
        ])
        ->tag('kernel.event_listener', ['event' => KernelEvents::CONTROLLER_ARGUMENTS, 'priority' => -1])

        // Services
        ->set(Mapper::class)
        ->args([
            param('denormalizer'),
            param('validation'),
            tagged_locator('artyum_request_dto_mapper.extractor'),
            service(RequestStack::class),
            service(EventDispatcherInterface::class),
            service(DenormalizerInterface::class),
            service(ValidatorInterface::class)->nullOnInvalid(),
            param('default_extractor'),
        ])

        // Extractors
        ->set(BodyParameterExtractor::class)
            ->tag('artyum_request_dto_mapper.extractor')
        ->set(FileExtractor::class)
            ->tag('artyum_request_dto_mapper.extractor')
        ->set(FormExtractor::class)
            ->tag('artyum_request_dto_mapper.extractor')
        ->set(JsonExtractor::class)
            ->tag('artyum_request_dto_mapper.extractor')
        ->set(QueryStringExtractor::class)
            ->tag('artyum_request_dto_mapper.extractor')
    ;
};
