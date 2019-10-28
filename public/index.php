<?php

use App\Db\Connection;
use App\Controller\Products;
use App\Router\Router;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\ServerRequest;

require_once("../vendor/autoload.php");

$config = include("../config/config.local.php");

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

$router = new Router([
    "/" => [
        "handler" => Products::class
    ],
    "products" => [
        "handler" => Products::class
    ]
]);

$route = $router->resolve($request->getMethod(), $request->getUri()->getPath());
if (!$route) {

    $response = new HtmlResponse("<h1>404 Not Found</h1>", 404);

} else {

    $controllerClass = $route["handler"];
    $params = $route["params"];
    foreach ($params as $key => $value) {
        $request = $request->withAttribute($key, $value);
    }

    $connection = new Connection($config["db"]["driver"], $config["db"]["config"]);
    $controller = new $controllerClass($connection);
    $response = $controller($request);

}

http_response_code($response->getStatusCode());
foreach ($response->getHeaders() as $header => $values) {
    header($header . ": " . implode("; ", $values));
}
echo $response->getBody();
