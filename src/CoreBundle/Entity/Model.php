<?php

namespace AppGear\CoreBundle\Entity;

class Model
{
    
    /**
     * Name
     */
    protected $name;
    
    /**
     * Parent
     */
    protected $parent;
    
    /**
     * Properties
     */
    protected $properties = array();
    
    /**
     * ToString
     */
    protected $toString;
    
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
     * Get parent
     */
    public function getParent()
    {
        return $this->parent;
    }
    
    /**
     * Set parent
     */
    public function setParent($parent)
    {
        $this->parent = $parent;
        return $this;
    }
    
    /**
     * Get properties
     */
    public function getProperties()
    {
        return $this->properties;
    }
    
    /**
     * Set properties
     */
    public function setProperties($properties)
    {
        $this->properties = $properties;
        return $this;
    }
    
    /**
     * Get toString
     */
    public function getToString()
    {
        return $this->toString;
    }
    
    /**
     * Set toString
     */
    public function setToString($toString)
    {
        $this->toString = $toString;
        return $this;
    }
}