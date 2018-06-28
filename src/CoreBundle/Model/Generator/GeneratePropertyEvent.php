<?php

namespace AppGear\CoreBundle\Model\Generator;

use AppGear\CoreBundle\Entity\Model;
use AppGear\CoreBundle\Entity\Property;
use PhpParser\Builder\Class_;
use Symfony\Component\EventDispatcher\Event;

class GeneratePropertyEvent extends Event
{
    /**
     * @var Property
     */
    private $property;

    /**
     * @var SourceGenerator
     */
    private $sourceGenerator;

    /**
     * Constructor.
     *
     * @param Property        $property
     * @param SourceGenerator $sourceGenerator
     */
    public function __construct(Property $property, SourceGenerator $sourceGenerator)
    {
        $this->property        = $property;
        $this->sourceGenerator = $sourceGenerator;
    }

    /**
     * @return Property
     */
    public function getProperty()
    {
        return $this->property;
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