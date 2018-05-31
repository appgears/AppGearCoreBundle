<?php

namespace AppGear\CoreBundle\Entity;

class Property
{
    
    /**
     * Name
     */
    protected $name;
    
    /**
     * ReadOnly
     */
    protected $readOnly = false;
    
    /**
     * Calculated
     */
    protected $calculated;
    
    /**
     * Extensions
     */
    protected $extensions = array();
    
    /**
     * Set name
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }
    
    /**
     * Get name
     */
    public function getName()
    {
        return $this->name;
    }
    
    /**
     * Set readOnly
     */
    public function setReadOnly($readOnly)
    {
        $this->readOnly = $readOnly;
        return $this;
    }
    
    /**
     * Get readOnly
     */
    public function getReadOnly()
    {
        return $this->readOnly;
    }
    
    /**
     * Set calculated
     */
    public function setCalculated($calculated)
    {
        $this->calculated = $calculated;
        return $this;
    }
    
    /**
     * Get calculated
     */
    public function getCalculated()
    {
        return $this->calculated;
    }
    
    /**
     * Set extensions
     */
    public function setExtensions($extensions)
    {
        $this->extensions = $extensions;
        return $this;
    }
    
    /**
     * Get extensions
     */
    public function getExtensions()
    {
        return $this->extensions;
    }
}