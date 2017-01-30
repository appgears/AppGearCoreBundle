<?php

namespace AppGear\CoreBundle\Entity\Property;

use AppGear\CoreBundle\Entity\Property;
class Field extends Property
{
    
    /**
     * DefaultValue
     */
    protected $defaultValue;
    
    /**
     * Set defaultValue
     */
    public function setDefaultValue($defaultValue)
    {
        $this->defaultValue = $defaultValue;
        return $this;
    }
    
    /**
     * Get defaultValue
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }
}