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
        $treeBuilder = new TreeBuilder('artyum_request_dto_mapper');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->scalarNode('default_source')
                    ->info('Used if the attribute does not specify any (must be a FQCN implementing "\Artyum\RequestDtoMapperBundle\Source\SourceInterface").')
                    ->defaultNull()
                ->end()
                ->arrayNode('denormalizer')
                    ->info('The configuration related to the denormalizer (https://symfony.com/doc/current/components/serializer.html).')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('default_options')
                            ->info('Used when mapping the request data to the DTO if the attribute does not set any.')
                            ->prototype('scalar')->end()
                        ->end()
                        ->arrayNode('additional_options')
                            ->info('Used when mapping the request data to the DTO (merged with the values passed by the attribute or "default_options").')
                            ->prototype('scalar')->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('validation')
                    ->info('The configuration related to the validator (https://symfony.com/doc/current/validation.html).')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')
                            ->info('Whether to validate the DTO after mapping it.')
                            ->defaultFalse()
                        ->end()
                        ->arrayNode('default_groups')
                            ->info('Used when validating the DTO if the attribute does not set any.')
                            ->addDefaultsIfNotSet()
                        ->end()
                        ->arrayNode('additional_groups')
                            ->info('Used when validating the DTO (merged with the values passed by the attribute or "default_groups").')
                            ->addDefaultsIfNotSet()
                        ->end()
                        ->booleanNode('throw_on_violation')
                            ->info('Whether to throw an exception if the DTO validation failed (constraint violations).')
                            ->defaultTrue()
                        ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
