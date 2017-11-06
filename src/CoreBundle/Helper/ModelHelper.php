<?php

namespace AppGear\CoreBundle\Helper;

use AppGear\CoreBundle\Entity\Model;
use AppGear\CoreBundle\Entity\Property;
use Generator;

class ModelHelper
{
    /**
     * Return self and parents models
     *
     * @param Model $model Model
     *
     * @return Model[]|Generator
     */
    public static function getSelfAndParents(Model $model): Generator
    {
        do {
            yield $model;
        } while (null !== $model = $model->getParent());
    }

    /**
     * Return parents models
     *
     * @param Model $model Model
     *
     * @return Model[]|Generator
     */
    public static function getParents(Model $model): Generator
    {
        while (null !== $model = $model->getParent()) {
            yield $model;
        }
    }

    /**
     * Get model property by name
     *
     * @param Model  $model Model
     * @param string $name  Property name
     *
     * @return Property|null
     */
    public static function getProperty(Model $model, string $name): ?Property
    {
        foreach (self::getProperties($model) as $property) {
            if ($property->getName() === $name) {
                return $property;
            }
        }

        return null;
    }

    /**
     * Get properties from model and parents
     *
     * @param Model $model Model
     *
     * @return Property[]|Generator
     */
    public static function getProperties(Model $model): Generator
    {
        foreach (self::getSelfAndParents($model) as $parent) {
            foreach ($parent->getProperties() as $property) {
                yield $property;
            }
        }
    }

    /**
     * Get model relationship by name
     *
     * @param Model  $model Model
     * @param string $name  Relationship name
     *
     * @return Property\Relationship|null
     */
    public static function getRelationship(Model $model, string $name): ?Property\Relationship
    {
        $property = self::getProperty($model, $name);

        if ($property instanceof Property\Relationship) {
            return $property;
        }

        return null;
    }
}
