<?php

namespace AppGear\CoreBundle\EntityService;

use AppGear\CoreBundle\Entity\Model;
use AppGear\CoreBundle\Entity\Property;

class PropertyService
{
    /**
     * Property
     *
     * @var Property
     */
    private $property;

    /**
     * Service for model property
     *
     * @param Model $property Property
     */
    public function __construct(Property $property)
    {
        $this->property = $property;
    }

    /**
     * Return specific property extension
     *
     * @param string $extensionClass Extension FQCN
     *
     * @return object|null
     */
    public function getExtension($extensionClass)
    {
        foreach ($this->property->getExtensions() as $extension) {
            if ($extension instanceof $extensionClass) {
                return $extension;
            }
        }

        return null;
    }
}