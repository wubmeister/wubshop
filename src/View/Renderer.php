<?php

namespace App\View;

class Renderer
{
    protected $vars = [];

    public function assign($name, $value)
    {
        $this->vars[$name] = $value;
    }

    public function render($file)
    {
        extract($this->vars);
        ob_start();
        include $file;
        $content = ob_get_clean();
        return $content;
    }
}
