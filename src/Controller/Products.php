<?php

namespace App\Controller;

use App\Db\Connection;
use App\Form\Field\Field;
use App\Form\Form;
use App\HttpException;
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
        $this->table = $dbConnection->schema("webshop")->table("product");
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
                    else throw new HttpException(405);
                    break;

                case "GET":
                    $action = $id ? "show" : "index";
                    break;

                default:
                    throw new HttpException(405);
            }
        }

        if (!method_exists($this, $action)) {
            throw new HttpException(404);
        }
        if (!$this->isAllowed($action)) {
            throw new HttpException(401);
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
            throw new HttpException(404);
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
        $form->addField(new Field("title", [ "required" => true ]));

        if ($this->request->getMethod() == "POST") {
            $post = $this->request->getParsedBody();
            $form->setValues($post, true);
            if ($form->isValid()) {
                $values = $form->getValues();
                $id = $this->table->insert($values);
                return new RedirectResponse("/products/{$id}");
            }
        }

        $view = new View(Template::find("products/add"));

        $view->assign("form", $form);

        $this->layout->assign("content", $view);
        return new HtmlResponse($this->layout->render());
    }

    public function edit($id)
    {
        return $this->update($id);
    }

    public function update($id)
    {
        $product = $this->table->findOne([ "id" => (int)$id ]);

        if (!$product) {
            throw new HttpException(404);
        }

        $form = new Form();
        $form->addField(new Field("title", [ "required" => true ]));
        $form->setValues($product->toArray());

        if ($this->request->getMethod() == "POST") {
            $post = $this->request->getParsedBody();
            $form->setValues($post, true);
            if ($form->isValid()) {
                $values = $form->getValues();
                $this->table->update($values, [ "id" => $id ]);
                return new RedirectResponse("/products/{$id}");
            }
        }

        $view = new View(Template::find("products/edit"));

        $view->assign("product", $product);
        $view->assign("form", $form);

        $this->layout->assign("content", $view);
        return new HtmlResponse($this->layout->render());
    }

    public function delete($id)
    {
        $product = $this->table->findOne([ "id" => (int)$id ]);

        if (!$product) {
            throw new HttpException(404);
        }

        if ($this->request->getMethod() == "POST") {
            $post = $this->request->getParsedBody();
            if (isset($post["confirm"]) && $post["confirm"] == "1") {
                $this->table->delete([ "id" => $id ]);
                return new RedirectResponse("/products");
            }
        }

        $view = new View(Template::find("products/delete"));

        $view->assign("product", $product);

        $this->layout->assign("content", $view);
        return new HtmlResponse($this->layout->render());
    }
}
