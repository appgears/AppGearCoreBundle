<?php

namespace AppGear\CoreBundle\Collection;

use Cosmologist\Gears\ArrayType;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
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
            $data        = array_map($transformFn, $data);
        }

        $filterFn = function ($item) use ($filterCallback, $filterArgs) {
            // we use opposite logic - if callback return true - then we exclude this item from result
            return !call_user_func($filterCallback, $item, ...$filterArgs);
        };

        return new self(array_filter($data, $filterFn));
    }

    /**
     * Useful filter - filter collection items by expression
     *
     * @param string $expression The expression (Symfony expression language)
     *                           If the expression returns true, the current value from array is returned into
     *                           the result array. Array keys are preserved.
     *                           Use "item" alias in the expression for access to iterated array item.
     * @return Collection
     */
    public function filterExpression(string $expression)
    {
        return new self(ArrayType::filter($this->toArray(), $expression));
    }

    /**
     * Transforms each item and returns result collection
     *
     * @param callable $callback
     * @param array    $args
     *
     * @return Collection
     */
    public function transform(callable $callback, array $args = []): Collection
    {
        $transformFn = function ($item) use ($callback, $args) {
            return call_user_func($callback, $item, ...$args);
        };

        return new self(array_map($transformFn, $this->data));
    }

    /**
     * @param string $expression
     *
     * @return Collection
     */
    public function transformExpression(string $expression)
    {
        $el          = new ExpressionLanguage();
        $parsedNodes = $el->parse($expression, ['item'])->getNodes();

        $callback = function ($item) use ($parsedNodes) {
            return $parsedNodes->evaluate([], ['item' => $item]);
        };

        return $this->transform($callback);
    }

    /**
     *
     * @param string $fqcn
     *
     * @return Collection
     */
    public function transformMap(string $fqcn)
    {
        return $this->transform([ArrayType::class, 'map'], [$fqcn]);
    }

    /**
     * Apply aggregation callback and returns aggregation result
     *
     * @param callable $callback
     *
     * @return mixed
     */
    public function aggregate(callable $callback)
    {
        return call_user_func_array($callback, [$this->toArray()]);
    }

    /**
     * Collects the items by path from collection
     *
     * @param string|string[] $path
     *
     * @return Collection
     */
    public function collect($path)
    {
        return new self(ArrayType::collect($this->toArray(), $path));
    }

    /**
     * @return array|Traversable
     */
    public function toArray()
    {
        return $this->data;
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->data);
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return $this->count() === 0;
    }
}