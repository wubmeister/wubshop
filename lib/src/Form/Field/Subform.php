<?php

namespace Lib\Form\Field;

/**
 * Represents a subform, i.e. a collection of fields with a common parent name.
 *
 * @author Wubbo Bos
 */
class Subform extends Field
{
    /** @var array $fields All the fields in the subform */
    protected $fields = [];

    /** @var array $fieldOrder The field names in their correct order */
    protected $fieldOrder = [];

    /**
     * Adds a field to the subform
     *
     * @param Field $field
     * @return Subform $this
     */
    public function addField(Field $field)
    {
        $field->parent = $this;
        $name = $field->getName();
        $this->fieldOrder[] = $name;
        $this->fields[$name] = $field;

        return $this;
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
     * @return Subform $this
     */
    public function setValue($value, $filter = false)
    {
        foreach ($value as $key => $val) {
            if (isset($this->fields[$key])) {
                $this->fields[$key]->setValue($val, $filter);
            }
        }

        return $this;
    }

    /**
     * Returns the values of all the fields in the subform
     *
     * @return array
     */
    public function getValue()
    {
        $value = [];
        foreach ($this->fields as $name => $field) {
            $value[$name] = $field->getValue();
        }

        return $value;
    }

    /**
     * Returns a field with the specified name.
     *
     * @param string $name
     * @return Field|null If no field is found with the specified name, NULL is returned
     */
    public function getField(string $name)
    {
        return isset($this->fields[$name]) ? $this->fields[$name] : null;
    }
}
