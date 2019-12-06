<?php

namespace App\View;

/**
 * Container for view helpers.
 *
 * This container will load helpers when needed.
 *
 * @author Wubbo Bos
 */
class Helper
{
    /** @var array $helpers The loaded helpers */
    protected $helpers = [];

    /**
     * Returns a helper with the given name.
     *
     * The helper should exist in the subdirectory "Helper"
     *
     * @param string $name The helper name
     * @return object The helper
     */
    public function __get($name)
    {
        if (!isset($this->helpers[$name])) {
            $this->loadHelper($name);
        }
        return $this->helpers[$name];
    }

    /**
     * Loads a helper with the given name.
     *
     * The helper should exist in the subdirectory "Helper"
     *
     * @param string $name The helper name
     */
    protected function loadHelper(string $name)
    {
        $className = get_called_class() . '\\' . ucfirst($name);
        $this->helpers[$name] = new $className;
    }
}
