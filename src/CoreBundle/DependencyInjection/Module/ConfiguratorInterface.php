<?php

namespace AppGear\CoreBundle\DependencyInjection\Module;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Interface for AppGear module addons configurators
 */
interface ConfiguratorInterface
{
    /**
     * Build and return module configuration node
     *
     * @return NodeDefinition|ArrayNodeDefinition Configuration node
     */
    public function buildNode();

    /**
     * @param ContainerBuilder $container      Container builder
     * @param array            $modulesConfigs Modules configs list (key is module alias, value is config)
     *
     * @return mixed
     */
    public function process(ContainerBuilder $container, $modulesConfigs);

    /**
     * Return configurator alias
     * @return string
     */
    public function getAlias();
}