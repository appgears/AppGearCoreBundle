<?php

namespace AppGear\CoreBundle\EntityService;

use AppGear\CoreBundle\Entity\Model;
use AppGear\CoreBundle\Entity\Property;

class ModelService
{
    /**
     * Model
     *
     * @var Model
     */
    private $model;

    /**
     * ModelService constructor.
     *
     * @param Model $model Model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * Return properties from the model and her parent models
     *
     * @return Property[]
     */
    public function getAllProperties()
    {
        $properties = $this->model->getProperties();

        $current = $this->model;
        while ($current = $current->getParent()) {
            $properties = array_merge($properties, $current->getProperties());
        }

        return $properties;
    }

    /**
     * Return self and parents models
     *
     * @return \Generator
     */
    public function getSelfAndParents()
    {
        $current = $this->model;
        do {
            yield $current;
        } while ($current = $current->getParent());
    }

    /**
     * Return parents models
     *
     * @return Model[]
     */
    public function getParents()
    {
        $parents = [];
        $current = $this->model;
        while ($parent = $current = $current->getParent()) {
            $parents[] = $parent;
        }

        return $parents;
    }

    /**
     * Get property by name
     *
     * @param string $name Proeprty name
     *
     * @return Property|null
     */
    public function getProperty($name)
    {
        foreach ($this->getSelfAndParents() as $model) {
            foreach ($model->getProperties() as $property) {
                if ($property->getName() === $name) {
                    return $property;
                }
            }
        }
    }
}