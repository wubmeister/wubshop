<?php

namespace App\Controller;

use App\Db\Connection;
use App\Form\Field\Field;
use App\Form\Form;
use App\Template;
use App\View\View;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\RedirectResponse;

class Products
{
    protected $table;
    protected $layout;
    protected $request;

    public function __construct(Connection $dbConnection)
    {
        $this->table = $dbConnection->schema()->table("product");
    }

    public function __invoke(ServerRequestInterface $request)
    {
        $method = $request->getMethod();
        $id = $request->getAttribute("id");
        $forcedAction = $request->getAttribute("action");
        $action = "index";

        if ($forcedAction) {
            $action = $forcedAction;
        } else {
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
        }

        if (!method_exists($this, $action)) {
            return new HtmlResponse("<h1>404 Not Found</h1>", 404);
        }
        if (!$this->isAllowed($action)) {
            return new HtmlResponse("<h1>401 Unauthorized</h1>", 401);
        }

        $this->request = $request;
        $this->layout = new View(Template::find("layout"));

        if ($id) return $this->$action($id);
        return $this->$action();
    }

    protected function isAllowed($action)
    {
        return true;
    }

    public function index()
    {
        $products = $this->table->find();

        $view = new View(Template::find("products/index"));

        $view->assign("products", $products);

        $this->layout->assign("content", $view);

        return new HtmlResponse($this->layout->render());
    }

    public function show($id)
    {
        $product = $this->table->findOne([ "id" => (int)$id ]);

        if (!$product) {
            return new HtmlResponse("<h1>404 Not Found</h1>", 404);
        }

        $view = new View(Template::find("products/show"));

        $view->assign("product", $product);

        $this->layout->assign("content", $view);
        return new HtmlResponse($this->layout->render());
    }

    public function add()
    {
        return $this->create();
    }

    public function create()
    {
        $form = new Form();
        $form->addField(new Field("title"));

        if ($this->request->getMethod() == "POST") {
            $post = $this->request->getParsedBody();
            $form->setValues($post);
            $values = $form->getValues();
            $id = $this->table->insert($values);
            return new RedirectResponse("/products/{$id}");
        }

        $view = new View(Template::find("products/add"));

        $view->assign("form", $form);

        $this->layout->assign("content", $view);
        return new HtmlResponse($this->layout->render());
    }
}
