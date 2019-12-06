<?php

namespace Lib\InputFilter;

abstract class AbstractValidator implements InputFilterInterface
{
    protected $valid = true;
    protected $error = null;

    abstract public function parseValue($value);

    public function isValid()
    {
        return $this->valid;
    }

    public function getError()
    {
        return $this->error;
    }
}
