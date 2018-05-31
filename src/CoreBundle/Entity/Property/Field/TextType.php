<?php

namespace AppGear\CoreBundle\Entity\Property\Field;

use AppGear\CoreBundle\Entity\Property\Field;
class TextType extends Field
{
    
    /**
     * InternalType
     */
    protected $internalType = 'string';
    
    /**
     * Get internalType
     */
    public function getInternalType()
    {
        return $this->internalType;
    }
}