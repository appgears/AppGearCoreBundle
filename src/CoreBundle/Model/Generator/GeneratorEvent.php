<?php

namespace AppGear\CoreBundle\Model\Generator;

use AppGear\CoreBundle\Entity\Model;
use PhpParser\Builder\Class_;
use Symfony\Component\EventDispatcher\Event;

class GeneratorEvent extends Event
{
    /**
     * Model
     *
     * @var Model
     */
    private $model;

    /**
     * Class node
     *
     * @var Class_
     */
    private $class;

    /**
     * GeneratorEvent constructor.
     *
     * @param Model  $model Model
     * @param Class_ $class Class node
     */
    public function __construct(Model $model, Class_ $class)
    {
        $this->model = $model;
        $this->class = $class;
    }

    /**
     * Model
     *
     * @return Model
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * Class node
     * 
     * @return Class_
     */
    public function getClass()
    {
        return $this->class;
    }
}