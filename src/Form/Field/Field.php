<?php

namespace App\Form\Field;

class Field
{
    protected $name;
    protected $value;
    protected $isArray;
    protected $required;
    protected $parent;
    protected $valid = true;
    protected $errors = [];
    protected $inputFilters = [];

    public function __construct($name, array $options = [])
    {
        $this->name = $name;
        if (isset($options["is_array"])) $this->isArray = (bool)$options["is_array"];
        if (isset($options["required"])) $this->isArray = (bool)$options["required"];
    }

    public function getName()
    {
        return $this->name;
    }

    public function setValue($value, $filter = false)
    {
        $this->value = $value;
        if ($filter) $this->filter();
    }

    public function getValue()
    {
        return $this->value;
    }

    public function isRequired()
    {
        return $this->required;
    }

    protected function filter()
    {
        if ($this->required && empty($value)) {
            $this->errors[] = "This field is required";
            $this->valid = false;
            return;
        }

        $value = $this->value;
        foreach ($this->inputFilters as $if) {
            $value = $if->parseValue($value);
            if (!$if->isValid()) {
                $this->errors[] = $if->getError();
                $this->valid = false;
                break;
            }
        }
        $this->value = $value;
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
