<?php

use App\Db\Connection;
use App\Controller\Products;
use App\HttpException;
use App\Router\Router;
use App\Session\Session;
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
try {
    if (!$route) {

        throw new HttpException(404);

    } else {

        $controllerClass = $route["handler"];
        $params = $route["params"];
        foreach ($params as $key => $value) {
            $request = $request->withAttribute($key, $value);
        }

        $connection = new Connection($config["db"]["driver"], $config["db"]["config"]);
        $session = new Session($connection->schema("webshop"));

        unset($session->hello);

        $controller = new $controllerClass($connection->schema("webshop"));
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
