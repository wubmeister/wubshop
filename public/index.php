<?php

use App\Db\Connection;
use App\Controller\Products;
use App\Router\Router;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\ServerRequest;

require_once("../vendor/autoload.php");

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

    $connection = new Connection("mysql", [ "host" => "127.0.0.1", "dbname" => "wubshop", "username" => "root", "password" => "xXjh7fNcu8G8NAU9" ]);
    $controller = new $controllerClass($connection);
    $response = $controller($request);

}

echo $response->getBody();
