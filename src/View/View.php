<?php

namespace App\View;

/**
 * Class to render a view
 *
 * @author Wubbo Bos
 */
class View
{
    /** @var Renderer $renderer */
    protected static $renderer = null;

    /** @var array $var The view variables */
    protected $vars = [];

    /**
     * Gets the shared renderer
     *
     * @return Renderer
     */
    public static function getRenderer()
    {
        if (!self::$renderer) {
            self::$renderer = new Renderer();
        }
        return self::$renderer;
    }

    /**
     * Constructor
     *
     * @param string $file
     */
    public function __construct($file)
    {
        $this->file = $file;
    }

    /**
     * Assigns a variable
     *
     * @param string $name The variable name
     * @param mixed $name The variable value
     */
    public function assign($name, $value)
    {
        $this->vars[$name] = $value;
    }

    /**
     * Renders the view
     *
     * @return string The rendered view
     */
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
