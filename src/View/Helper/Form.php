<?php

namespace App\View\Helper;

class Form
{
    public function textField($field, $label = null)
    {
        echo '<div class="field">' . PHP_EOL;
        if ($label) {
            echo '<label for="' . $field->getId() . '">' . $label . '</label>' . PHP_EOL;
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
}