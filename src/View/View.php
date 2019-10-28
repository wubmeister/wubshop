<?php

namespace App\View;

class View
{
    protected static $renderer = null;

    protected $vars = [];

    public static function getRenderer()
    {
        if (!self::$renderer) {
            self::$renderer = new Renderer();
        }
        return self::$renderer;
    }

    public function __construct($file)
    {
        $this->file = $file;
    }

    public function assign($name, $value)
    {
        $this->vars[$name] = $value;
    }

    public function render()
    {
        $renderer = self::getRenderer();
        $preRender = [];

        foreach ($this->vars as $key => $value) {
            if ($value instanceof View) {
                $preRender[$key] = $value;
            } else {
                $renderer->assign($key, $value);
            }
        }

        foreach ($preRender as $key => $view) {
            $content = $view->render();
            $renderer->assign($key, $content);
        }

        return $renderer->render($this->file);
    }
}
