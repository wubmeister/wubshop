<?php

namespace Lib\Router;

/**
 * A router to route URLs to handlers.
 *
 * @author Wubbo Bos
 */
class Router
{
    /** @var array $routes */
    protected $routes;

    /**
     * Constructor
     *
     * @param array $routes
     */
    public function __construct(array $routes)
    {
        $this->routes = $routes;
    }

    /**
     * Resolves a URL to a handler with parameters.
     *
     * @return array [ "hander" => $handler, "params" => $parameters ]
     */
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
                    if ($chunk == "add" || $chunk == "edit" || $chunk == "delete") {
                        $params["action"] = $chunk;
                    } else {
                        $params["id"] = $chunk;
                        $params["ids"][$key] = $chunk;
                        $key = null;
                    }
                } else if (isset($collection[$chunk])) {
                    $params["id"] = null;
                    $handler = $collection[$chunk]["handler"];
                    $collection = isset($collection[$chunk]["children"]) ? $collection[$chunk]["children"] : null;
                    if (!isset($params["allowId"]) || $params["allowId"] == true) {
                        $key = $chunk;
                    }
                } else {
                    return null;
                }
            }
        }

        if (!$collection && $index < count($chunks) - 1) {
            if (isset($params["allowTail"]) && $params["allowTail"] == true) {
                $params["tail"] = implode('/', array_slice($chunks, $index + 1));
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
