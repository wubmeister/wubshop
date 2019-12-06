<?php

namespace App\View\Helper;

use App\Tree;

/**
 * Helper to provide some trivial methods
 *
 * @author Wubbo Bos
 */
class General
{
    /**
     * Renders a tree as an unordered list
     *
     * @param App\Tree $tree
     * @param int|null $maxLevel The maximum depth to render
     */
    public function tree(Tree $tree, $maxLevel = null)
    {
        if ($maxLevel !== null && !$maxLevel) {
            return;
        }

        if ($tree->label) {
            echo '<li' . ($tree->active ? ' class="active"' : '') . '><a href="' . $tree->url . '">' . $tree->label . '</a>';
        }

        $children = $tree->getChildren();
        if (count($children)) {
            echo '<ul class="nav">';
            foreach ($children as $child) {
                $this->tree($child, $maxLevel === null ? null : $maxLevel - 1);
            }
            echo '</ul>';
        }

        if ($tree->label) {
            echo '</li>';
        }
    }
}
