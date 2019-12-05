<?php

namespace App;

use ArrayAccess;
use Iterator;

/**
 * Provides a way to alter arrays without passing them by reference.
 *
 * @author Wubbo Bos
 */
class MutableArray implements ArrayAccess, Iterator
{
    /** @var array $array The array */
    protected $array;

    /**
     * Initializes the object.
     *
     * @param array $array The array
     */
    public function __construct(array $array)
    {
        $this->array = $array;
    }

    /**
     * Checks if a specific offset exists
     *
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->array);
    }

    /**
     * Gets the value at a specific offset
     *
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->array[$offset];
    }

    /**
     * Sets the value at a specific offset
     *
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        $this->array[$offset] = $value;
    }

    /**
     * Removes a specific offset from the array
     *
     * @param mixed $offset
     */
    public function offsetUnset($offset)
    {
        unset($this->array[$offset]);
    }

    /**
     * Rewinds the iteration
     */
    public function rewind()
    {
        $this->keys = array_keys($this->array);
        $this->count = count($this->keys);
        $this->index = 0;
    }

    /**
     * Advances to the next element
     */
    public function next()
    {
        $this->index++;
    }

    /**
     * Checks if the current iteration is valid
     *
     * @return bool
     */
    public function valid()
    {
        return $this->index < $this->count;
    }

    /**
     * Gets the current key (offset)
     *
     * @return mixed
     */
    public function key()
    {
        return $this->keys[$this->index];
    }

    /**
     * Gets the current value
     *
     * @return mixed
     */
    public function current()
    {
        return $this->array[$this->keys[$this->index]];
    }

    /**
     * Returns the native PHP array version
     *
     * @return array
     */
    public function getArrayCopy()
    {
        return $this->array;
    }
}
