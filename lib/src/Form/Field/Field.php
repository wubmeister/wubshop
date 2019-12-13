<?php

namespace Lib\Form\Field;

/**
 * Class to represent a form field
 *
 * @author Wubbo Bos
 */
class Field
{
    /** @var string $name The name without any brackets */
    protected $name;

    /** @var mixed $value */
    protected $value;

    /** @var bool $isArray Flag to determine where this field holds an array of values */
    protected $isArray = false;

    /** @var bool $required */
    protected $required = false;

    /** @var Field $parent */
    protected $parent;

    /** @var bool $valid */
    protected $valid = true;

    /** @var bool $changed */
    protected $changed = false;

    /** @var array $errors */
    protected $errors = [];

    /** @var array $inputFilters */
    protected $inputFilters = [];

    /**
     * Constructor
     *
     * @param string $name The field name without any brackets
     * @param array $options Optional.
     */
    public function __construct($name, array $options = [])
    {
        $this->name = $name;
        if (isset($options["is_array"])) $this->isArray = (bool)$options["is_array"];
        if (isset($options["required"])) $this->required = (bool)$options["required"];
    }

    /**
     * Returns the field name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets the value for the field
     *
     * @param mixed $value
     * @param bool $filter Pass TRUE or omit to filter the value
     */
    public function setValue($value, $filter = false)
    {
        $this->value = $value;
        $this->changed = true;
        if ($filter) $this->filter();
    }

    /**
     * Returns the current value of the field
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Method to check if this field is required
     *
     * @return bool
     */
    public function isRequired()
    {
        return $this->required;
    }

    /**
     * Filters the current value of the field
     */
    protected function filter()
    {
        if ($this->required && empty($this->value)) {
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

    /**
     * Method to check if this field value is valid
     *
     * @return bool
     */
    public function isValid()
    {
        if ($this->valid && $this->changed && $this->required && empty($this->value)) {
            $this->errors[] = "This field is required";
            $this->valid = false;
        }
        return $this->valid;
    }

    /**
     * Returns any validation error messages
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Returns the full name for the field to be used in the 'name' attribute in HTML
     *
     * @return string
     */
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

    /**
     * Returns a unique ID for the field to be used in the 'id' attribute in HTML
     *
     * @return string
     */
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
