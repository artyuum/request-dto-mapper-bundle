<?php

use Artyum\RequestDtoMapperBundle\EventListener\ControllerArgumentsEventListener;
use Artyum\RequestDtoMapperBundle\Mapper\Mapper;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $container): void {
    $container->services()
        // Services
        ->set(Mapper::class)
        ->args([
            service(EventDispatcherInterface::class),
            service(DenormalizerInterface::class),
            service(ValidatorInterface::class),
        ])

        // Listeners
        ->set(ControllerArgumentsEventListener::class)
        ->args([
            service(Mapper::class),
        ])
        ->tag('kernel.event_listener', ['event' => KernelEvents::CONTROLLER_ARGUMENTS])
    ;
};
