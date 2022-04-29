<?php

namespace Artyum\RequestDtoMapperBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\NodeParentInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder(): NodeParentInterface
    {
        $treeBuilder = new TreeBuilder('artyuum_request_dto_mapper');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->arrayNode('validation')
                ->info('The configuration related to the validator.')
                    ->children()
                        ->booleanNode('enabled')
                            ->info('Whether to validate the DTO after mapping it.')
                            ->defaultFalse()
                        ->end()
                        ->arrayNode('default_groups')
                            ->info('The default validation groups to use when validating the DTO.')
                        ->end()
                        ->booleanNode('throw_on_violation')
                            ->info('Whether to throw an exception if the DTO validation failed (constraint violations).')
                            ->defaultFalse()
                        ->end()
                    ->end()
                ->end()
                ->scalarNode('default_source')
                    ->info('The default source (FQCN) to use if the attribute does not specify any.')
                ->end()
                ->arrayNode('denormalizer')
                    ->info('The configuration related to the denormalizer.')
                    ->children()
                        ->arrayNode('default_options')
                            ->info('The default denormalizer options to pass when mapping the request data to the DTO.')
                        ->end()
                ->end()
        ;

        return $treeBuilder;
    }
}
