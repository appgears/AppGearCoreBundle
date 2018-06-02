<?php

namespace AppGear\CoreBundle\Collection;

use Cosmologist\Gears\ArrayType;
use Traversable;

class Collection
{
    /**
     * @var array|Traversable
     */
    private $data;

    /**
     * Collection constructor.
     *
     * @param array|Traversable $data
     */
    public function __construct($data = [])
    {
        $this->data = ArrayType::cast($data);
    }

    /**
     * @param array|Traversable $data
     *
     * @return Collection
     */
    public static function create($data = []): Collection
    {
        return new self($data);
    }

    /**
     * Returns new filtered collection
     *
     * @param callable      $filterCallback
     * @param array         $filterArgs
     * @param callable|null $transformCallback
     * @param array         $transformArgs
     *
     * @return Collection
     */
    public function filter(callable $filterCallback, array $filterArgs = [], callable $transformCallback = null, array $transformArgs = []): Collection
    {
        $data = $this->data;

        if ($transformCallback !== null) {
            $transformFn = function ($item) use ($transformCallback, $transformArgs) {
                return call_user_func($transformCallback, $item, ...$transformArgs);
            };
            $data = array_map($transformFn, $data);
        }

        $filterFn = function ($item) use ($filterCallback, $filterArgs) {
            // we use opposite logic - if callback return true - then we exclude this item from result
            return !call_user_func($filterCallback, $item, ...$filterArgs);
        };

        return new Collection(array_filter($data, $filterFn));
    }

    /**
     * @param callable|null $transformCallback
     * @param array         $transformArgs
     *
     * @return Collection
     */
    public function transform(callable $transformCallback = null, array $transformArgs = []): Collection
    {
        $transformFn = function ($item) use ($transformCallback, $transformArgs) {
            return call_user_func($transformCallback, $item, ...$transformArgs);
        };

        return new Collection(array_map($transformFn, $this->data));
    }

    /**
     * @return array|Traversable
     */
    public function toArray()
    {
        return $this->data;
    }
}