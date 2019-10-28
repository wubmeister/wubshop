<?php

use App\Controller\Products;
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

$controller = new Products();
$response = $controller($request);
echo $response->getBody();
