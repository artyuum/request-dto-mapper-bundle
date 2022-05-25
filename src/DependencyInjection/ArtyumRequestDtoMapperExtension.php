<?php

namespace Artyum\RequestDtoMapperBundle\DependencyInjection;

use Artyum\RequestDtoMapperBundle\Mapper\Mapper;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class ArtyumRequestDtoMapperExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new PhpFileLoader($container, new FileLocator(dirname(__DIR__) . '/Resources/config'));
        $loader->load('services.php');

        $config = $this->processConfiguration(new Configuration(), $configs);


        $container->getDefinition(Mapper::class)
            ->setArgument(0, $config['denormalizer'])
            ->setArgument(1, $config['validation'])
            ->setArgument(6, $config['default_source'])
        ;
    }
}
