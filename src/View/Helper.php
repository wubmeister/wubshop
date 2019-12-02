<?php

namespace App\View;

class Helper
{
    protected $helpers = [];

    public function __get($name)
    {
        if (!isset($this->helpers[$name])) {
            $this->loadHelper($name);
        }
        return $this->helpers[$name];
    }

    protected function loadHelper(string $name)
    {
        $className = get_called_class() . '\\' . ucfirst($name);
        $this->helpers[$name] = new $className;
    }
}
