<?php

namespace AppGear\CoreBundle\DependencyInjection;

use AppGear\CoreBundle\DependencyInjection\Module\ConfiguratorInterface;
use Cosmologist\Gears\Str;
use ReflectionClass;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Yaml\Parser;

/**
 * CoreBundle extension
 */
class AppGearExtension extends Extension implements PrependExtensionInterface
{
    /**
     * AppGear module addons configurators
     *
     * @var ConfiguratorInterface[]
     */
    public static $moduleConfigurators = [];

    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $locator = new FileLocator(__DIR__ . '/../Resources/config/services');
        $loader  = new YamlFileLoader($container, $locator);

        $loader->load('core.yml');

        $configuration = new Configuration();
        $config        = $this->processConfiguration($configuration, $configs);

        // Dispatch configurations to the suitable module configurators
        foreach (self::$moduleConfigurators as $configurator)
        {
            $configuratorConfig = [];
            foreach ($config['modules'] as $moduleName=>$moduleConfig) {
                if (array_key_exists($configurator->getAlias(), $moduleConfig)) {
                    $configuratorConfig[$moduleName] = $moduleConfig[$configurator->getAlias()];
                }
            }

            $configurator->process($container, $configuratorConfig);
        }
    }

    /**
     * Append bundles datagrid and datasource configurations to the Showcase bundle configuration
     *
     * @param ContainerBuilder $container Container Builder
     *
     * @return void
     */
    public function prepend(ContainerBuilder $container)
    {
        foreach ($container->getParameter('kernel.bundles') as $bundleClass) {

            if (!($bundleAlias = $this->getBundleAlias($bundleClass))) {
                continue;
            }

            $configPath = dirname((new ReflectionClass($bundleClass))->getFileName())
                . '/Resources/config/appgear.yml';

            if (!file_exists($configPath) || is_dir($configPath)) {
                continue;
            }

            $parser = new Parser();
            $config = $parser->parse(file_get_contents($configPath));

            $config = [
                'modules' => [
                    $bundleAlias => $config
                ]
            ];

            $container->prependExtensionConfig($this->getAlias(), $config);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias()
    {
        return 'appgear';
    }

    /**
     * Returns the bundle alias
     *
     * @param string $className The bundle class
     *
     * @return string Prefix
     */
    private function getBundleAlias($className)
    {
        $class = new ReflectionClass($className);
        return Container::underscore(str_replace('\\', '.', $class->getNamespaceName()));
    }
}
