<?php

namespace App\Form\Field;

class Field
{
    protected $name;
    protected $value;
    protected $isArray;
    protected $parent;

    public function __construct($name, array $options = [])
    {
        $this->name = $name;
        if (isset($options["is_array"])) $this->isArray = (bool)$options["is_array"];
    }

    public function getName()
    {
        return $this->name;
    }

    public function setValue($value, $validate = false)
    {
        $this->value = $value;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getFullName()
    {
        if ($this->parent) {
            $name = $this->parent->getFullName() . '[' . $this->name . ']';
        } else {
            $name = $this->name;
        }
        if ($this->isArray) {
            $name .= '[]';
        }

        return $name;
    }

    public function getId()
    {
        if ($this->parent) {
            $id = $this->parent->getid() . '_' . $this->name;
        } else {
            $id = $this->name;
        }
        return $id;
    }
}
