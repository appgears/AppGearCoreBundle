<?php

namespace AppGear\CoreBundle\Model\Generator;

use AppGear\CoreBundle\Entity\Model;
use PhpParser\Builder\Class_;
use Symfony\Component\EventDispatcher\Event;

class GenerateModelEvent extends Event
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
     * @var SourceGenerator
     */
    private $sourceGenerator;

    /**
     * Constructor.
     *
     * @param Model           $model Model
     * @param Class_          $class Class node
     * @param SourceGenerator $sourceGenerator
     */
    public function __construct(Model $model, Class_ $class, SourceGenerator $sourceGenerator)
    {
        $this->model           = $model;
        $this->class           = $class;
        $this->sourceGenerator = $sourceGenerator;
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

    /**
     * Source generator
     *
     * @return SourceGenerator
     */
    public function getSourceGenerator()
    {
        return $this->sourceGenerator;
    }
}