<?php

namespace AppGear\CoreBundle\Entity\Property;

use AppGear\CoreBundle\Entity\Property;
class Collection extends Property
{
    
    /**
     * ClassName
     */
    protected $className;
    
    /**
     * Set className
     */
    public function setClassName($className)
    {
        $this->className = $className;
        return $this;
    }
    
    /**
     * Get className
     */
    public function getClassName()
    {
        return $this->className;
    }
}