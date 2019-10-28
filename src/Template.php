<?php

namespace App;

class Template
{
    public static function find($path)
    {
        $absPath = dirname(__DIR__) . "/templates/" . $path . ".phtml";
        return $absPath;
    }
}
