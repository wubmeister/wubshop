<?php

namespace App\View\Helper;

/**
 * Helper with all routing related methods
 *
 * @author Wubbo Bos
 */
class Routing
{
    /**
     * Outputs a URL for the specified routes
     *
     * @param array $path
     * @param srting $action Optional.
     */
    public function route(array $path, $action = null)
    {
        $output = "";
        $count = count($path);
        $index = 0;

        foreach ($path as $key => $id) {
            if (is_numeric($key)) {
                if ($action && $index == $count - 1) {
                    $output .= "/{$id}/{$action}";
                } else {
                    $output .= "/{$id}";
                }
            } else {
                if ($action && $index == $count - 1) {
                    $output .= "/{$key}/{$action}/{$id}";
                } else {
                    $output .= "/{$key}/{$id}";
                }
            }
            $index++;
        }

        echo $output;
    }
}
