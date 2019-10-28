<?php

namespace App\Form\Field;

class Options extends Field
{
    protected $options = [];

    public function __construct($name, array $options = [])
    {
        parent::__construct($name, $options);
        if (isset($options["options"])) $this->setOptions($options["options"]);
    }

    public function setOptions(array $options)
    {
        $this->options = $options;
    }

    public function addOption($value, $label)
    {
        $this->options[$value] = $label;
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function isSelected($value)
    {
        if ($this->isArray) return in_array($value, $this->value);
        return $value == $this->value;
    }

    // Convenience functions
    public function getHtmlSelectOptions($glued = false)
    {
        $options = [];
        foreach ($this->options as $value => $label) {
            if (is_array($label)) {
                $options[] = '<optgroup label="' . $value . '">';
                foreach ($label as $v => $l) {
                    $options[] = '    <option value="' . $v . '"' . ($this->isSelected($v) ? ' selected' : '') . '>' . $l . '</option>';
                }
                $options[] = '</optgroup>';
            } else {
                $options[] = '<option value="' . $value . '"' . ($this->isSelected($value) ? ' selected' : '') . '>' . $label . '</option>';
            }
        }

        if ($glued) {
            return implode(PHP_EOL, $options);
        }

        return $options;
    }

    public function getHtmlInputs($type, $glued = false)
    {
        $options = [];
        $basename = $this->getFullName();
        $baseid = $this->getId();
        $index = 0;

        foreach ($this->options as $value => $label) {
            if (is_array($label)) {
                $options[] = '<label>' . $value . '</label>';
                foreach ($label as $v => $l) {
                    $options[] = '<input type="' . $type . '" name="' . $basename . '" id="' . $baseid . '_' . $index . '" value="' . $v . '"' . ($this->isSelected($v) ? ' checked' : '') . '> ' .
                        '<label for="' . $baseid . '_' . $index . '">' . $l . '</label>';
                    $index++;
                }
            } else {
                $options[] = '<input type="' . $type . '" name="' . $basename . '" id="' . $baseid . '_' . $index . '" value="' . $value . '"' . ($this->isSelected($value) ? ' checked' : '') . '> ' .
                    '<label for="' . $baseid . '_' . $index . '">' . $label . '</label>';
                $index++;
            }
        }

        if ($glued) {
            return implode(PHP_EOL, $options);
        }

        return $options;
    }
}
