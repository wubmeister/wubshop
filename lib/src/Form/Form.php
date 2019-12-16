<?php

namespace Lib\Form;

use Lib\Form\Field\Field;

/**
 * Represents a form
 *
 * @author Wubbo Bos
 */
class Form
{
    /** @var array $fields All the fields in the form */
    protected $fields = [];

    /** @var array $fieldOrder The field names in their correct order */
    protected $fieldOrder = [];

    /**
     * Adds a field to the form
     *
     * @param Field $field
     * @return Subform $this
     */
    public function addField(Field $field)
    {
        $name = $field->getName();
        $this->fieldOrder[] = $name;
        $this->fields[$name] = $field;
    }

    /**
     * Returns all the fields in the correct order
     *
     * @return array
     */
    public function getFields()
    {
        $fields = [];
        foreach ($this->fieldOrder as $name) {
            $fields[] = $this->fields[$name];
        }
        return $fields;
    }

    /**
     * Sets the values for one or more fields
     *
     * @param array $value The values: [ "fieldname" => $value ]
     * @param bool $filter Pass TRUE to filter the value
     * @return Form $this
     */
    public function setValues(array $values, $filter = false)
    {
        foreach ($values as $key => $value) {
            if (isset($this->fields[$key])) {
                $this->fields[$key]->setValue($value, $filter);
            }
        }

        return $this;
    }

    /**
     * Returns the values of all the fields in the form
     *
     * @return array
     */
    public function getValues()
    {
        $values = [];
        foreach ($this->fields as $name => $field) {
            $values[$name] = $field->getValue();
        }

        return $values;
    }

    /**
     * Returns a field with the specified name.
     *
     * @param string $name
     * @return Field|null If no field is found with the specified name, NULL is returned
     */
    public function getField($name)
    {
        return isset($this->fields[$name]) ? $this->fields[$name] : null;
    }

    /**
     * Method to check if all the fields in the form are valid.
     * If one or more fields are not valid, this will return false.
     *
     * @return bool
     */
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

    /**
     * Returns all the validation error messages
     *
     * @return array
     */
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
