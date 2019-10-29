<?php

namespace App\Form\Field;

class Subform extends Field
{
    protected $fields = [];
    protected $fieldOrder = [];

    public function addField(Field $field)
    {
        $field->parent = $this;
        $name = $field->getName();
        $this->fieldOrder[] = $name;
        $this->fields[$name] = $field;
    }

    public function getFields()
    {
        $fields = [];
        foreach ($this->fieldOrder as $name) {
            $fields[] = $this->fields[$name];
        }
        return $fields;
    }

    public function setValue($value, $filter = false)
    {
        foreach ($value as $key => $val) {
            if (isset($this->fields[$key])) {
                $this->fields[$key]->setValue($val, $filter);
            }
        }
    }

    public function getValue()
    {
        $value = [];
        foreach ($this->fields as $name => $field) {
            $value[$name] = $field->getValue();
        }

        return $value;
    }

    public function getField($name)
    {
        return isset($this->fields[$name]) ? $this->fields[$name] : null;
    }
}
