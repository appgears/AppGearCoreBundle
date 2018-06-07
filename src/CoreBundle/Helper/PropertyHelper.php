<?php

namespace AppGear\CoreBundle\Helper;

use AppGear\CoreBundle\Entity\Property;
use Cosmologist\Gears\StringType;

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

    /**
     * Check if property is field
     *
     * @param Property $property Property
     *
     * @return bool
     */
    public static function isField(Property $property)
    {
        return $property instanceof Property\Field;
    }

    /**
     * Check if property is relationship
     *
     * @param Property $property Property
     *
     * @return bool
     */
    public static function isRelationship(Property $property)
    {
        return $property instanceof Property\Relationship;
    }

    /**
     * Check if internal type of field is scalar
     *
     * @param Property\Field $field Field
     *
     * @return bool
     */
    public static function isScalar(Property\Field $field)
    {
        return in_array($field->getInternalType(), ['boolean', 'integer', 'string', 'float']);
    }

    /**
     * Check if property is calculated
     *
     * @param Property $property Property
     *
     * @return bool
     */
    public static function isCalculated(Property $property)
    {
        return $property->getCalculated() !== null;
    }

    /**
     * Check if property is calculated with service
     *
     * @param Property $property Property
     *
     * @return bool
     */
    public static function isCalculatedWithService(Property $property)
    {
        return StringType::contains($property->getCalculated(), '::');
    }

    /**
     * Check if property is calculated with expression
     *
     * @param Property $property Property
     *
     * @return bool
     */
    public static function isCalculatedWithExpression(Property $property)
    {
        return !self::isCalculatedWithService($property);
    }
}
