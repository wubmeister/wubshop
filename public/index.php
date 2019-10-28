<?php

use App\Db\Connection;
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

$connection = new Connection("mysql", [ "host" => "127.0.0.1", "dbname" => "wubshop", "username" => "root", "password" => "xXjh7fNcu8G8NAU9" ]);
$controller = new Products($connection);
$response = $controller($request);
echo $response->getBody();
