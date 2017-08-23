<?php

namespace Core\Supports;

use Illuminate\Support\Collection as BaseCollection;

class Collection extends BaseCollection
{
    /**
     * Number of available data on resources.
     * this usefull for making pagination
     *
     * @var int
     */
    protected $total;

    /**
     * Create a new collection.
     *
     * @param mixed $items
     * @param int   $total
     */
    public function __construct($items = [], $total = 0)
    {
        $this->items = $items;
        $this->total = ($total) ? $total : count($items);
    }

    /**
     * Convert items into object.
     *
     * @return object|bool
     */
    public function toObject()
    {
        return ($this->isEmpty()) ? false : array_to_object($this->items);
    }

    /**
     * Get total data from collection.
     *
     * @return int
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * Remove item collection if value match.
     *
     * @param $value
     * @return void
     */
    public function remove($value)
    {
        $key = array_search($value, $this->items);
        if ($key !== false) {
            $this->offsetUnset($key);
        }
    }

    /**
     * Dynamically retrieve attributes on the model.
     *
     * @param string $key
     * @return mixed
     */
    public function __get($key)
    {
        if ($this->has($key)) {
            return $this->get($key);
        }

        return false;
    }
}