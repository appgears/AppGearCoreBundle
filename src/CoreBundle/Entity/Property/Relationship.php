<?php

namespace AppGear\CoreBundle\Entity\Property;

use AppGear\CoreBundle\Entity\Property;
abstract class Relationship extends Property
{
    
    /**
     * Target
     */
    protected $target;
    
    /**
     * Set target
     */
    public function setTarget($target)
    {
        $this->target = $target;
        return $this;
    }
    
    /**
     * Get target
     */
    public function getTarget()
    {
        return $this->target;
    }
}