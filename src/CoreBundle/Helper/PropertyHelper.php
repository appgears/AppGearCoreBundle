<?php

namespace AppGear\CoreBundle\Helper;

use AppGear\CoreBundle\Entity\Extension\Model as ExtensionModel;
use AppGear\CoreBundle\Entity\Model;
use AppGear\CoreBundle\Entity\Property;
use Generator;
use ReflectionClass;

class PropertyHelper
{
    /**
     * Return specified property extension
     *
     * @param Property $property Property
     * @param string   $fqcn     Extension FQCN
     *
     * @todo all column extensions should be implements common interface
     *
     * @return object|null
     */
    public static function getExtension(Property $property, string $fqcn)
    {
        foreach ($property->getExtensions() as $extension) {
            if ($extension instanceof $fqcn) {
                return $extension;
            }
        }

        return null;
    }
}
