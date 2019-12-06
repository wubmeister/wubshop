<?php

namespace Lib\InputFilter;

interface InputFilterInterface
{
    public function parseValue($value);
    public function isValid();
    public function getError();
}
