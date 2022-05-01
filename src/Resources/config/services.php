<?php

use Artyum\RequestDtoMapperBundle\EventListener\ControllerArgumentsEventListener;
use Artyum\RequestDtoMapperBundle\Mapper\Mapper;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use function Symfony\Component\DependencyInjection\Loader\Configurator\param;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $container): void {
    $container->services()
        // Services
        ->set(Mapper::class)
        ->args([
            param('denormalizer'),
            param('validation'),
            service(EventDispatcherInterface::class),
            service(DenormalizerInterface::class),
            service(ValidatorInterface::class)->nullOnInvalid(),
            param('default_source'),
        ])

        // Listeners
        ->set(ControllerArgumentsEventListener::class)
        ->args([
            service(Mapper::class),
        ])
        ->tag('kernel.event_listener', ['event' => KernelEvents::CONTROLLER_ARGUMENTS, 'priority' => -1])
    ;
};
