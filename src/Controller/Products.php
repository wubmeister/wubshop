<?php

namespace App\Controller;

use App\Db\Connection;
use App\Template;
use App\View\View;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;

class Products
{
    public function __invoke(ServerRequestInterface $request)
    {
        $method = $request->getMethod();
        $id = $request->getAttribute("id");
        $action = "index";

        switch ($method) {
            case "POST":
            case "PUT":
                $action = $id ? "update" : "create";
                break;

            case "DELETE":
                if ($id) $action = "delete";
                break;

            case "GET":
                $action = $id ? "show" : "index";
                break;

            default:
                return new HtmlResponse("<h1>405 Method Not Allowed</h1>", 405);
        }

        if (!method_exists($this, $action)) {
            return new HtmlResponse("<h1>404 Not Found</h1>", 404);
        }
        if (!$this->isAllowed($action)) {
            return new HtmlResponse("<h1>401 Unauthorized</h1>", 401);
        }

        return $this->$action();
    }

    protected function isAllowed($action)
    {
        return true;
    }

    public function index()
    {
        $connection = new Connection("mysql", [ "host" => "127.0.0.1", "dbname" => "wubshop", "username" => "root", "password" => "xXjh7fNcu8G8NAU9" ]);
        $table = $connection->schema()->table("product");
        $products = $table->fetchAll();

        $layout = new View(Template::find("layout"));
        $view = new View(Template::find("products/index"));

        $view->assign("products", $products);

        $layout->assign("content", $view);

        return new HtmlResponse($layout->render());
    }
}
