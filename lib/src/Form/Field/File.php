<?php

namespace Lib\Form\Field;

/**
 * Class to represent a file upload field
 *
 * @author Wubbo Bos
 */
class File extends Field
{
    /**
     * Returns the value of the corresponding entry in the $_FILES variable
     *
     * @retun array
     */
    public function getValue()
    {
        return isset($_FILES[$this->name]) ? $_FILES[$this->name] : null;
    }

    /**
     * Overrides the setValue from Field, because no value setting is needed.
     */
    public function setValue($value, $filter = true)
    {
    }
}
