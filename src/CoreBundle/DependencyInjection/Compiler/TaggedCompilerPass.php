<?php

namespace AppGear\CoreBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * CompilerPass to collect tagged services and pass it to the tagged service manager
 */
class TaggedCompilerPass implements CompilerPassInterface
{
    /**
     * Tagged service manager definition ID
     */
    const TAGGED_MANAGER_ID = 'app_gear.core.tagged_manager';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(self::TAGGED_MANAGER_ID)) {
            return;
        }

        $definition = $container->getDefinition(self::TAGGED_MANAGER_ID);

        // Iterate between all tags
        foreach ($container->findTags() as $tag) {

            // Get all services with the same tag
            $taggedServices = $container->findTaggedServiceIds($tag);

            foreach ($taggedServices as $id => $attributes) {

                // Add service to the manager
                $definition->addMethodCall(
                    'addService',
                    array(
                        $id,
                        $tag,
                        $attributes[0]
                    )
                );
            }
        }
    }
}