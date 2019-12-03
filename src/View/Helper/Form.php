<?php

namespace App\View\Helper;

class Form
{
    public function label($field, $label)
    {
        echo '<label for="' . $field->getId() . '">' . $label . '</label>' . PHP_EOL;
    }

    public function textField($field, $label = null)
    {
        echo '<div class="field">' . PHP_EOL;
        if ($label) {
            $this->label($field, $label);
        }
        echo '<input type="text" name="' . $field->getFullName() . '" id="' . $field->getId() . '" value="' . $field->getValue() . '" />' . PHP_EOL;
        if (!$field->isValid()) {
            echo '<ul class="errors">' . PHP_EOL;
            foreach ($field->getErrors() as $error) {
                echo '<li>' . $error . '</li>' . PHP_EOL;
            }
            echo '</ul>' . PHP_EOL;
        }
        echo '</div>' . PHP_EOL;
    }

    public function select($field, $label = null)
    {
        echo '<div class="field">' . PHP_EOL;
        if ($label) {
            $this->label($field, $label);
        }
        echo '<select name="' . $field->getFullName() . '" id="' . $field->getId() . '" value="' . $field->getValue() . '">' . PHP_EOL;
        foreach ($field->getOptions() as $key => $value) {
            echo '<option value="' . $key . '"' . ($field->isSelected($value) ? ' selected' : '') . '>' . $value . '</option>';
        }
        echo '</select>';
        if (!$field->isValid()) {
            echo '<ul class="errors">' . PHP_EOL;
            foreach ($field->getErrors() as $error) {
                echo '<li>' . $error . '</li>' . PHP_EOL;
            }
            echo '</ul>' . PHP_EOL;
        }
        echo '</div>' . PHP_EOL;
    }

    public function checkbox($field, $label = null)
    {
        echo '<div class="field">' . PHP_EOL;
        if ($label) {
            $this->label($field, $label);
        }
        echo '<input type="hidden" name="' . $field->getFullName() . '" value="0" />' . PHP_EOL;
        echo '<input type="checkbox" name="' . $field->getFullName() . '" id="' . $field->getId() . '" value="1"' . ($field->getValue() == '1' ? ' checked' : '') . ' />' . PHP_EOL;
        if (!$field->isValid()) {
            echo '<ul class="errors">' . PHP_EOL;
            foreach ($field->getErrors() as $error) {
                echo '<li>' . $error . '</li>' . PHP_EOL;
            }
            echo '</ul>' . PHP_EOL;
        }
        echo '</div>' . PHP_EOL;
    }
}
