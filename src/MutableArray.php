<?php

namespace App;

use ArrayAccess;
use Iterator;

class MutableArray implements ArrayAccess, Iterator
{
    protected $array;

    public function __construct(array $array)
    {
        $this->array = $array;
    }

    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->array);
    }

    public function offsetGet($offset)
    {
        return $this->array[$offset];
    }

    public function offsetSet($offset, $value)
    {
        $this->array[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->array[$offset]);
    }

    public function rewind()
    {
        $this->keys = array_keys($this->array);
        $this->count = count($this->keys);
        $this->index = 0;
    }

    public function next()
    {
        $this->index++;
    }

    public function valid()
    {
        return $this->index < $this->count;
    }

    public function key()
    {
        return $this->keys[$this->index];
    }

    public function current()
    {
        return $this->array[$this->keys[$this->index]];
    }

    public function getArrayCopy()
    {
        return $this->array;
    }
}
