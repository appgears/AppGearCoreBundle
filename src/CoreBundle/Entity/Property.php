<?php

namespace AppGear\CoreBundle\Entity;

class Property
{
    
    /**
     * Name
     */
    protected $name;
    
    /**
     * Extensions
     */
    protected $extensions = array();
    
    /**
     * Get name
     */
    public function getName()
    {
        return $this->name;
    }
    
    /**
     * Set name
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }
    
    /**
     * Get extensions
     */
    public function getExtensions()
    {
        return $this->extensions;
    }
    
    /**
     * Set extensions
     */
    public function setExtensions($extensions)
    {
        $this->extensions = $extensions;
        return $this;
    }
}