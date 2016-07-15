<?php

namespace AppGear\CoreBundle;

use AppGear\CoreBundle\DependencyInjection\Compiler\TaggedCompilerPass;
use AppGear\CoreBundle\DependencyInjection\Module\ModelsConfigurator;
use AppGear\CoreBundle\DependencyInjection\AppGearExtension;
use AppGear\CoreBundle\DependencyInjection\Configuration;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class AppGearCoreBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        AppGearExtension::$moduleConfigurators[] = Configuration::$moduleConfigurators[] = new ModelsConfigurator;

        parent::build($container);

        $container->addCompilerPass(new TaggedCompilerPass());
    }

    /**
     * Override method for using "appgear" section name in the config
     *
     * {@inheritdoc}
     */
    public function getContainerExtension()
    {
        if (null === $this->extension) {
            $this->extension = new AppGearExtension();
        }

        return $this->extension;
    }
}
