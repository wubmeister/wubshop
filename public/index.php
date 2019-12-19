<?php

use Lib\Db\Connection\Mysql as Connection;
use Lib\HttpException;
use Lib\Router\Router;
use Lib\Session\Session;
use Lib\Tree;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\ServerRequest;

require_once("../vendor/autoload.php");

$config = array_merge(
    include("../config/config.local.php"),
    include("../config/config.global.php")
);

$request = new ServerRequest(
    $_SERVER,
    $_FILES,
    $_SERVER["REQUEST_URI"],
    $_SERVER["REQUEST_METHOD"],
    "php://input",
    getallheaders(),
    $_COOKIE,
    $_GET,
    $_POST
);

$router = new Router($config["router"]["routes"]);

$route = $router->resolve($request->getMethod(), $request->getUri()->getPath());
try {
    if (!$route) {

        throw new HttpException(404);

    } else {

        $controllerClass = $route["handler"];
        $params = $route["params"];
        foreach ($params as $key => $value) {
            $request = $request->withAttribute($key, $value);
        }

        $connection = new Connection($config["db"]["config"]);
        $session = new Session($connection->schema("webshop"));

        $navigation = Tree::fromArray([ "children" => [
            "products" => [
                "url" => "/products",
                "label" => "Products",
                "children" => [
                    "types" => [
                        "url" => "/product-types",
                        "label" => "Types"
                    ],
                ]
            ],
            "settings" => [
                "url" => "/settings",
                "label" => "Settings",
                "children" => [
                    "channels" => [
                        "url" => "/channels",
                        "label" => "Channels"
                    ],
                    "languages" => [
                        "url" => "/languages",
                        "label" => "Languages"
                    ],
                ]
            ],
        ]]);

        $controller = new $controllerClass($connection->schema("wubshop"));
        $controller->setNavigation($navigation);
        $response = $controller($request);

    }
} catch (HttpException $ex) {
    $response = new HtmlResponse("<h1>" . $ex->getMessage() . "</h1>", $ex->statusCode);
}

http_response_code($response->getStatusCode());
foreach ($response->getHeaders() as $header => $values) {
    header($header . ": " . implode("; ", $values));
}
echo $response->getBody();
