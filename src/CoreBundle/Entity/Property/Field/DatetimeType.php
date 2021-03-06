<?php

namespace AppGear\CoreBundle\Entity\Property\Field;

use AppGear\CoreBundle\Entity\Property\Field;
class DatetimeType extends Field
{
    
    /**
     * InternalType
     */
    protected $internalType = '\\DateTime';
    
    /**
     * Get internalType
     */
    public function getInternalType()
    {
        return $this->internalType;
    }
}