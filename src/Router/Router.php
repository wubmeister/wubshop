<?php

namespace App\Router;

class Router
{
    protected $routes;

    public function __construct(array $routes)
    {
        $this->routes = $routes;
    }

    public function resolve(string $method, string $uri)
    {
        $trimmedUri = trim($uri, '/');
        $chunks = explode('/', $trimmedUri);
        $collection = $this->routes;
        $handler = null;
        $params = [ "ids" => [] ];
        $key = null;

        if (empty($trimmedUri)) {
            if (isset($this->routes["/"])) {
                $handler = $this->routes["/"]["handler"];
            }
        } else {
            foreach ($chunks as $index => $chunk) {
                if ($key) {
                    if ($chunk == "add" || $chunk == "edit") {
                        $params["action"] = $chunk;
                    } else {
                        $params["id"] = $chunk;
                        $params["ids"][$key] = $chunk;
                    }
                } else if (isset($collection[$chunk])) {
                    $params["id"] = null;
                    $handler = $collection[$chunk]["handler"];
                    $collection = isset($collection[$chunk]["children"]) ? $collection[$chunk]["children"] : null;
                    if (!isset($params["allowId"]) || $params["allowId"] == true) {
                        $key = $chunk;
                    }
                    if (!$collection) {
                        if (isset($params["allowTail"]) && $params["allowTail"] == true) {
                            $params["tail"] = implode('/', array_slice($chunks, $index + 1));
                        }
                        // else if ($index < count($chunks) - 1) {
                        //     return null;
                        // }
                    }
                } else {
                    return null;
                }
            }
        }

        if (!$handler) {
            return null;
        }

        return [
            "handler" => $handler,
            "params" => $params
        ];
    }
}
