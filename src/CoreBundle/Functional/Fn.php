<?php

namespace AppGear\CoreBundle\Functional;

use Closure;

class Fn
{
    /**
     * @var Fn[]
     */
    private $chain;

    /**
     * Constructor
     *
     * @param Fn[]
     */
    public function __construct(...$chain)
    {
        $this->chain = $chain;
    }

    /**
     * @param Closure[] $chain
     *
     * @return Fn
     */
    public static function chain(...$chain): Fn
    {
        return new self($chain);
    }

    /**
     * @param string|string[] $path
     */
    public static function select($path, ...$chain)
    {
        Functions\SelectFunction::select($path);
    }

    /**
     * @param mixed $data
     *
     * @return array
     */
    public function apply($data)
    {
        foreach ($this->chain as $item) {
            $data = $item->apply($data);
        }

        return $data;
    }
}