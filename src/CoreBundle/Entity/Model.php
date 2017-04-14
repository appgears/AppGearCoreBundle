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
     * Extensions
     */
    protected $extensions = array();
    
    /**
     * Abstract
     */
    protected $abstract;
    
    /**
     * ToString
     */
    protected $toString;
    
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
     * Set parent
     */
    public function setParent($parent)
    {
        $this->parent = $parent;
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
     * Set properties
     */
    public function setProperties($properties)
    {
        $this->properties = $properties;
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
    
    /**
     * Set abstract
     */
    public function setAbstract($abstract)
    {
        $this->abstract = $abstract;
        return $this;
    }
    
    /**
     * Get abstract
     */
    public function getAbstract()
    {
        return $this->abstract;
    }
    
    /**
     * Set toString
     */
    public function setToString($toString)
    {
        $this->toString = $toString;
        return $this;
    }
    
    /**
     * Get toString
     */
    public function getToString()
    {
        return $this->toString;
    }
    public function __toString()
    {
        return (string) $this->name;
    }
}