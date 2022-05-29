<?php

use Artyum\RequestDtoMapperBundle\EventListener\ControllerArgumentsEventListener;
use Artyum\RequestDtoMapperBundle\Mapper\Mapper;
use Artyum\RequestDtoMapperBundle\Source\BodyParameterSource;
use Artyum\RequestDtoMapperBundle\Source\FileSource;
use Artyum\RequestDtoMapperBundle\Source\FormSource;
use Artyum\RequestDtoMapperBundle\Source\JsonSource;
use Artyum\RequestDtoMapperBundle\Source\QueryStringSource;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use function Symfony\Component\DependencyInjection\Loader\Configurator\param;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;

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
                tagged_iterator('artyum_request_dto_mapper.source'),
                service(RequestStack::class),
                service(EventDispatcherInterface::class),
                service(DenormalizerInterface::class),
                service(ValidatorInterface::class)->nullOnInvalid(),
                param('default_source'),
            ])

        // Sources
        ->set(BodyParameterSource::class)
            ->tag('artyum_request_dto_mapper.source')
        ->set(FileSource::class)
            ->tag('artyum_request_dto_mapper.source')
        ->set(FormSource::class)
            ->tag('artyum_request_dto_mapper.source')
        ->set(JsonSource::class)
            ->tag('artyum_request_dto_mapper.source')
        ->set(QueryStringSource::class)
            ->tag('artyum_request_dto_mapper.source')
    ;
};
