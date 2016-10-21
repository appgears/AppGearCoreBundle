<?php

namespace AppGear\CoreBundle\DependencyInjection;

use AppGear\CoreBundle\DependencyInjection\Module\ConfiguratorRegistry;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use AppGear\CoreBundle\DependencyInjection\Module\ConfiguratorInterface;

/**
 * AppGearCoreBundle configuration structure.
 */
class Configuration implements ConfigurationInterface
{
    /**
     * AppGear module addons configurators
     *
     * @var ConfiguratorInterface[]
     */
    public static $moduleConfigurators = [];

    /**
     * {@inhertidoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode    = $treeBuilder->root('appgear');

        $modulesNode = $rootNode
            ->children()
                ->arrayNode('modules');

        foreach (self::$moduleConfigurators as $configurator) {
            $modulesNode = $modulesNode->append($configurator->buildNode());
        }

        $modulesNode
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
