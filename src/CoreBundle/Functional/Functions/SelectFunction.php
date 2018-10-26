<?php

namespace AppGear\CoreBundle\Functional\Functions;

use Closure;
use Cosmologist\Gears\ArrayType;

class SelectFunction
{
    /**
     * @param string|string[] $path
     *
     * @return Closure
     */
    public static function select($path): Closure
    {
        return function (array $data) use ($path) {
            return ArrayType::collect($data, $path);
        };
    }
}