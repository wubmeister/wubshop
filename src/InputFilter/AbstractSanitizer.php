<?php

namespace App\InputFilter;

abstract class AbstractSanitizer implements InputFilterInterface
{
    abstract public function parseValue($value);

    public function isValid()
    {
        return true;
    }

    public function getError()
    {
        return null;
    }
}
