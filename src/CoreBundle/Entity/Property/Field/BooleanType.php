<?php

namespace AppGear\CoreBundle\Entity\Property\Field;

use AppGear\CoreBundle\Entity\Property\Field;
class BooleanType extends Field
{
    
    /**
     * InternalType
     */
    protected $internalType = 'boolean';
    
    /**
     * Get internalType
     */
    public function getInternalType()
    {
        return $this->internalType;
    }
}