<?php

namespace ManeOlawale\RestResponse;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Traversable;

abstract class AbstractListResponse extends AbstractResponse implements IteratorAggregate, Countable
{
    /**
     * Return array of the list
     *
     * @return array
     */
    abstract protected function getListArray(): array;

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->getListArray());
    }

    public function count(): int
    {
        return count($this->getListArray());
    }

    /**
     * Execute a callback over each item.
     *
     * @param  callable  $callback
     * @return $this
     */
    public function each(callable $callback)
    {
        foreach ($this->getListArray() as $key => $item) {
            if ($callback($item, $key) === false) {
                break;
            }
        }

        return $this;
    }

    /**
     * Reduce the collection to a single value.
     *
     * @param  callable  $callback
     * @param  mixed  $initial
     * @return mixed
     */
    public function reduce(callable $callback, $initial = null)
    {
        $result = $initial;

        foreach ($this as $key => $value) {
            $result = $callback($result, $value, $key);
        }

        return $result;
    }

    /**
     * Run a map over each of the items.
     *
     * @param  callable  $callback
     * @return array
     */
    public function map(callable $callback)
    {
        $keys = array_keys($this->getListArray());

        $items = array_map($callback, $this->getListArray(), $keys);

        return array_combine($keys, $items);
    }

    /**
     * Get the list array
     *
     * @return array
     */
    public function list(): array
    {
        return $this->getListArray();
    }
}
