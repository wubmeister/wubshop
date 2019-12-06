<?php

namespace Lib\View\Helper;

/**
 * Helper to render form elements
 *
 * @author Wubbo Bos
 */
class Form
{
    /**
     * Renders the label for a field
     *
     * @param Lib\Form\Field $field
     * @param string $label
     */
    public function label($field, $label)
    {
        echo '<label for="' . $field->getId() . '">' . $label . '</label>' . PHP_EOL;
    }

    /**
     * Renders the error messages for a field, if there are any
     *
     * @param Lib\Form\Field $field
     */
    public function errors($field)
    {
        if (!$field->isValid()) {
            echo '<ul class="errors">' . PHP_EOL;
            foreach ($field->getErrors() as $error) {
                echo '<li>' . $error . '</li>' . PHP_EOL;
            }
            echo '</ul>' . PHP_EOL;
        }
    }

    /**
     * Renders a text field
     *
     * @param Lib\Form\Field $field
     * @param string $label Optional. If specified, the label will be rendered within the field element
     */
    public function textField($field, $label = null)
    {
        echo '<div class="field">' . PHP_EOL;
        if ($label) {
            $this->label($field, $label);
        }
        echo '<input type="text" name="' . $field->getFullName() . '" id="' .
            $field->getId() . '" value="' . $field->getValue() . '" />' . PHP_EOL;

        $this->errors($field);

        echo '</div>' . PHP_EOL;
    }

    /**
     * Renders a selection dropdown
     *
     * @param Lib\Form\Field $field
     * @param string $label Optional. If specified, the label will be rendered within the field element
     */
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

        $this->errors($field);

        echo '</div>' . PHP_EOL;
    }

    /**
     * Renders a checkbox
     *
     * @param Lib\Form\Field $field
     * @param string $label Optional. If specified, the label will be rendered within the field element
     */
    public function checkbox($field, $label = null)
    {
        echo '<div class="field">' . PHP_EOL;
        if ($label) {
            $this->label($field, $label);
        }

        echo '<input type="hidden" name="' . $field->getFullName() . '" value="0" />' . PHP_EOL;
        echo '<input type="checkbox" name="' . $field->getFullName() . '" id="' .
            $field->getId() . '" value="1"' . ($field->getValue() == '1' ? ' checked' : '') . ' />' . PHP_EOL;

        $this->errors($field);
        echo '</div>' . PHP_EOL;
    }
}
