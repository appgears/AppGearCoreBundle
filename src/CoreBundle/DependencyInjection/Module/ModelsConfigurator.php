<?php

namespace AppGear\CoreBundle\DependencyInjection\Module;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * AppGear configuration for the models
 */
class ModelsConfigurator implements ConfiguratorInterface
{
    /**
     * {@inheritdoc}
     */
    public function buildNode()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode    = $treeBuilder->root('models');

        $rootNode
            ->useAttributeAsKey('name')
            ->prototype('array')
                ->performNoDeepMerging()
                ->children()
                    ->scalarNode('parent')->end()
                    ->scalarNode('toString')->end()
                    ->booleanNode('abstract')->end()
                    ->arrayNode('extensions')
                        ->prototype('variable')->end()
                    ->end()
                    ->arrayNode('properties')
                        ->useAttributeAsKey('name')
                        ->prototype('array')
                        ->children()
                            ->arrayNode('classType')
                                ->children()
                                    ->scalarNode('className')->end()
                                ->end()
                            ->end()
                            ->arrayNode('collection')
                                ->children()
                                    ->scalarNode('className')->end()
                                ->end()
                            ->end()
                            ->arrayNode('field')
                                ->children()
                                    ->scalarNode('type')
                                        ->isRequired()
                                    ->end()
                                    ->arrayNode('extensions')
                                        ->prototype('variable')->end()
                                    ->end()
                                ->end()
                            ->end()
                            ->arrayNode('relationship')
                                ->children()
                                    ->scalarNode('type')
                                        ->isRequired()
                                    ->end()
                                    ->scalarNode('target')
                                        ->isRequired()
                                    ->end()
                                    ->arrayNode('extensions')
                                        ->prototype('variable')->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $rootNode;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container, $modulesConfigs)
    {
        $container->setParameter('app_gear.core.model.manager.models', $modulesConfigs);
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias()
    {
        return 'models';
    }
}
