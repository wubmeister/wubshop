<?php

namespace App\View;

/**
 * Class to render a view
 *
 * @author Wubbo Bos
 */
class Renderer
{
    /** @var array $vars Template variables */
    protected $vars = [];

    /**
     * Assigns a value to a variable
     *
     * @param string $name The variable name
     * @param mixed $value The value
     */
    public function assign(string $name, $value)
    {
        $this->vars[$name] = $value;
    }

    /**
     * Renders a file with the current variables
     *
     * @param string $file The path to the file to render
     */
    public function render(string $file)
    {
        extract($this->vars);
        $helper = new Helper();
        ob_start();
        include $file;
        $content = ob_get_clean();
        return $content;
    }
}
