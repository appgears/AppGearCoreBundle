<?php

namespace AppGear\CoreBundle\Entity\Property;

use AppGear\CoreBundle\Entity\Property;
class Relationship extends Property
{
    
    /**
     * Target
     */
    protected $target;
    
    /**
     * Composition
     */
    protected $composition = false;
    
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
    
    /**
     * Set composition
     */
    public function setComposition($composition)
    {
        $this->composition = $composition;
        return $this;
    }
    
    /**
     * Get composition
     */
    public function getComposition()
    {
        return $this->composition;
    }
}