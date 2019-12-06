<?php

namespace Lib\Form\Field;

class FileUploadField extends Field
{
    public function getValue()
    {
        return isset($_FILES[$this->name]) ? $_FILES[$this->name] : null;
    }

    public function setValue($value)
    {
    }
}
