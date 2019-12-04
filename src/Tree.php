<?php

namespace App;

class Tree
{
    /** @var array $properties */
    protected $properties;

    /** @var array $children */
    protected $children = [];

    /** @var array $namedChildren */
    protected $namedChildren = [];

    /** @var string $name */
    protected $name;

    /**
     * [ "property" => $value, "children" => [ $key => [ ... ] ] ]
     */
    public static function fromArray(array $array, $name = null)
    {
        $children = null;
        if (isset($array["children"])) {
            $children = $array["children"];
            unset($array["children"]);
        }
        $root = new Tree($array);
        if ($name) {
            $root->name = $name;
        }
        if ($children) {
            foreach ($children as $key => $child) {
                $root->appendChild(self::fromArray($child), is_numeric($key) ? null : $key);
            }
        }

        return $root;
    }

    public function __construct(array $properties = [])
    {
        $this->properties = $properties;
    }

    public function __get($name)
    {
        return isset($this->properties[$name]) ? $this->properties[$name] : null;
    }

    public function __set($name, $value)
    {
        $this->properties[$name] = $value;
    }

    public function appendChild(Tree $child, $name = null)
    {
        if ($name) {
            $this->namedChildren[$name] = count($this->children);
            $child->name = $name;
        }
        $this->children[] = $child;
    }

    public function getChildren()
    {
        return $this->children;
    }

    public function getChildrenNamed()
    {
        $children = [];
        foreach ($this->namedChildren as $name => $index) {
            $children[$name] = $this->children[$index];
        }
        return $children;
    }

    public function cascadeProperty($path, $name, $value)
    {
        if (!is_array($path)) {
            $path = explode('/', trim($path, '/'));
        }

        if (count($path) == 0 || ($this->name && $path[0] != $this->name)) return;

        if ($this->name) {
            $this->properties[$name] = $value;
            array_shift($path);
        }

        if (count($path) > 0 && isset($this->namedChildren[$path[0]])) {
            $this->children[$this->namedChildren[$path[0]]]->cascadeProperty($path, $name, $value);
        }
    }

    public function cascadePropertyReplace($path, $name, $search, $replace)
    {
        if (!is_array($path)) {
            $path = explode('/', trim($path, '/'));
        }

        if (count($path) == 0 || ($this->name && $path[0] != $this->name)) return;

        if ($this->name) {
            if (isset($this->properties[$name])) {
                $this->properties[$name] = str_replace($search, $replace, $this->properties[$name]);
            }
            array_shift($path);
        }

        if (count($path) > 0 && isset($this->namedChildren[$path[0]])) {
            $this->children[$this->namedChildren[$path[0]]]->cascadePropertyReplace($path, $name, $search, $replace);
        }
    }
}
