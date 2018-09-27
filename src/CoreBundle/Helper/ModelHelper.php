<?php

namespace AppGear\CoreBundle\Helper;

use AppGear\CoreBundle\Entity\Extension\Model as ExtensionModel;
use AppGear\CoreBundle\Entity\Model;
use AppGear\CoreBundle\Entity\Property;
use Cosmologist\Gears\StringType;
use Generator;
use ReflectionClass;
use stdClass;

class ModelHelper
{
    /**
     * Build FQCN for the model name
     *
     * @param string $model   The model name
     * @param array  $bundles Bundles from kernel.bundles
     *
     * @return string FQCN
     */
    public static function getFqcn($model, array $bundles)
    {
        $parts       = explode('.', $model);
        $bundle      = array_shift($parts);
        $bundleClass = $bundles[$bundle];
        $reflection  = new ReflectionClass($bundleClass);
        $parts       = array_map(['\\Cosmologist\\Gears\\StringType\\CamelSnakeCase', 'snakeToCamel'], $parts);

        $fqcn = $reflection->getNamespaceName() . '\\Entity';
        if (count($parts)) {
            $fqcn .= '\\' . implode('\\', $parts);
        }

        return $fqcn;
    }

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
     * Get model property by name or path
     *
     * @param Model $model Model
     * @param       $name  $path  Property name or path to property (like foo.bar)
     *
     * @return Property|null
     */
    public static function getProperty(Model $model, string $name): ?Property
    {
        $isPath = StringType::contains($name, '.');
        if ($isPath) {
            list($currentName, $leftPath) = explode('.', $name, 2);

            $property = self::getProperty($model, $currentName);

            return self::getProperty($property->getTarget(), $leftPath);
        }

        foreach (self::getProperties($model) as $property) {
            if ($property->getName() === $name) {
                return $property;
            }
        }

        return null;
    }

    /**
     * Get first property with specified extension
     *
     * @param Model  $model Model
     * @param string $fqcn  Extension FQCN
     *
     * @return stdClass Result with properties "property" and "extension"
     */
    public static function getPropertyWithExtension(Model $model, string $fqcn): ?stdClass
    {
        foreach (self::getProperties($model) as $property) {
            if (null !== $extension = PropertyHelper::getExtension($property, $fqcn)) {
                $result            = new stdClass();
                $result->property  = $property;
                $result->extension = $extension;

                return $result;
            }
        }

        return null;
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

        throw new \RuntimeException("Model '$model' does not contain relationship '$name'");
    }

    /**
     * Return specified model extension
     *
     * @param Model  $model Model
     * @param string $fqcn  Extension FQCN
     *
     * @return ExtensionModel|null
     */
    public static function getExtension(Model $model, string $fqcn): ?ExtensionModel
    {
        foreach ($model->getExtensions() as $extension) {
            if ($extension instanceof $fqcn) {
                return $extension;
            }
        }

        return null;
    }

    /**
     * Reads object property value
     *
     * @param object   $object   Object
     * @param Property $property Property
     *
     * @return mixed
     */
    public static function readPropertyValue($object, Property $property)
    {
        $method = 'get' . ucfirst($property->getName());

        return $object->$method();
    }

    /**
     * Sets object property value
     *
     * @param object   $object   Object
     * @param Property $property Property
     * @param mixed    $value    Value
     *
     * @return mixed
     */
    public static function setPropertyValue($object, Property $property, $value)
    {
        $method = 'set' . ucfirst($property->getName());

        return $object->$method($value);
    }
}
