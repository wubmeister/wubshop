<?php

namespace App\InputFilter;

interface InputFilterInterface
{
    public function parseValue($value);
    public function isValid();
    public function getError();
}
