<?php

namespace AppGear\CoreBundle\Entity\Property;

use AppGear\CoreBundle\Entity\Property;
class ClassType extends Property
{
    
    /**
     * ClassName
     */
    protected $className;
    
    /**
     * Get className
     */
    public function getClassName()
    {
        return $this->className;
    }
    
    /**
     * Set className
     */
    public function setClassName($className)
    {
        $this->className = $className;
        return $this;
    }
}