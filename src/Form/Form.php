<?php

namespace App\Form;

use App\Form\Field\Field;

class Form
{
    protected $fields = [];
    protected $fieldOrder = [];

    public function addField(Field $field)
    {
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

    public function setValues(array $values, $filter = false)
    {
        foreach ($values as $key => $value) {
            if (isset($this->fields[$key])) {
                $this->fields[$key]->setValue($value, $filter);
            }
        }
    }

    public function getValues()
    {
        $values = [];
        foreach ($this->fields as $name => $field) {
            $values[$name] = $field->getValue();
        }

        return $values;
    }

    public function getField($name)
    {
        return isset($this->fields[$name]) ? $this->fields[$name] : null;
    }

    public function isValid()
    {
        $valid = true;
        foreach ($this->fields as $field) {
            if (!$field->isValid()) {
                $valid = false;
                break;
            }
        }
        return $valid;
    }

    public function getErrors()
    {
        $errors = [];
        foreach ($this->fields as $name => $field) {
            $errors = $field->getErrors();
            if (!empty($errors)) {
                $errors[$name] = $errors;
            }
        }
        return $errors;
    }
}
