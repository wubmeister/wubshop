<?php

namespace Lib;

/**
 * This class is used for all template related stuff.
 *
 * @author Wubbo Bos
 */
class Template
{
    /**
     * Resolves a template path
     *
     * @param string $path The relative path without extension.
     */
    public static function find($path)
    {
        $absPath = dirname(__DIR__, 2) . "/templates/" . $path . ".phtml";
        return $absPath;
    }
}
