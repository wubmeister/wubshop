<?php

namespace Lib;

/**
 * Class to represent a tree structure.
 *
 * An instance of this class is actually a node in a tree, but in fact a tree
 * is nothing more than a node.
 *
 * Nodes can be named, which can be useful to traverse a tree via a path.
 *
 * Nodes also have properties which can be accessed as members of the instance.
 *
 * @author Wubbo Bos
 * @version 1.0
 */
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
     * Creates a tree from an array.
     *
     * The array should be in this form:
     * [ "property" => $value, "children" => [ $key => [ ... ] ] ]
     *
     * @param array $array The input array
     * @param string $name Optional. The name for the root element.
     * @return Tree The newly generated tree
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

    /**
     * Creates a tree node with properties.
     *
     * @param array $properties
     */
    public function __construct(array $properties = [])
    {
        $this->properties = $properties;
    }

    /**
     * Gets a property value.
     *
     * @param string $name
     * @return mixed The value of the property. Returns NULL if the property doesn't exist.
     */
    public function __get($name)
    {
        return isset($this->properties[$name]) ? $this->properties[$name] : null;
    }

    /**
     * Sets a property value.
     *
     * @param string $name
     * @param mixed $value
     */
    public function __set($name, $value)
    {
        $this->properties[$name] = $value;
    }

    /**
     * Appends a child to the node.
     *
     * @param Tree $child
     * @param string $name Optional. The name for the child.
     */
    public function appendChild(Tree $child, $name = null)
    {
        if ($name) {
            $this->namedChildren[$name] = count($this->children);
            $child->name = $name;
        }
        $this->children[] = $child;
    }

    /**
     * Returns all the children of the node without naming index.
     *
     * @return array
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Returns all the children of the node with naming index.
     *
     * Naming index means that the keys in the array are the child names.
     *
     * @return array
     */
    public function getChildrenNamed()
    {
        $children = [];
        foreach ($this->namedChildren as $name => $index) {
            $children[$name] = $this->children[$index];
        }
        return $children;
    }

    /**
     * Sets a property value on all the nodes on the specified path.
     *
     * @param string|array $path
     * @param string $name The prperty name
     * @param mixed $value
     */
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

    /**
     * Does a search/replace on a specified property value for all the nodes on the specified path.
     *
     * @param string|array $path
     * @param string $name The prperty name
     * @param string $search
     * @param mixed $replace
     */
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
