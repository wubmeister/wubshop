<?php

namespace App\View\Helper;

class Routing
{
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
