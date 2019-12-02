<?php

namespace App\Controller;

use App\HttpException;
use App\Template;
use App\View\View;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\RedirectResponse;

abstract class Crud
{
    protected $table;
    protected $layout;
    protected $request;

    protected $templatePath = "crud";
    protected $baseRoute = "/crud";

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
        $items = $this->table->find();

        $view = new View(Template::find("{$this->templatePath}/index"));

        $view->assign("items", $items);

        $this->layout->assign("content", $view);

        return new HtmlResponse($this->layout->render());
    }

    public function show($id)
    {
        $item = $this->table->findOne([ "id" => (int)$id ]);

        if (!$item) {
            throw new HttpException(404);
        }

        $view = new View(Template::find("{$this->templatePath}/show"));

        $view->assign("item", $item);

        $this->layout->assign("content", $view);
        return new HtmlResponse($this->layout->render());
    }

    public function createOrUpdate($id = null)
    {
        $purpose = "add";
        $item = null;

        if ($id) {
            $item = $this->table->findOne([ "id" => (int)$id ]);

            if (!$item) {
                throw new HttpException(404);
            }

            $purpose = "edit";
        }

        $form = $this->getForm($purpose);
        if ($item) {
            $form->setValues($item->toArray());
        }

        if ($this->request->getMethod() == "POST") {
            $post = $this->request->getParsedBody();
            $form->setValues($post, true);
            if ($form->isValid()) {
                $values = $form->getValues();
                if ($purpose == "add") {
                    $id = $this->table->insert($values);
                } else {
                    $this->table->update($values, [ "id" => $id ]);
                }
                return new RedirectResponse("{$this->baseRoute}/{$id}");
            }
        }

        $view = new View(Template::find("{$this->templatePath}/{$purpose}"));

        $view->assign("form", $form);
        $view->assign("item", $item);
        $view->assign("purpose", $purpose);

        $this->layout->assign("content", $view);
        return new HtmlResponse($this->layout->render());
    }

    public function add()
    {
        return $this->createOrUpdate();
    }

    public function create()
    {
        return $this->createOrUpdate();
    }

    public function edit($id)
    {
        return $this->createOrUpdate($id);
    }

    public function update($id)
    {
        return $this->createOrUpdate($id);
    }

    public function delete($id)
    {
        $item = $this->table->findOne([ "id" => (int)$id ]);

        if (!$item) {
            throw new HttpException(404);
        }

        if ($this->request->getMethod() == "POST") {
            $post = $this->request->getParsedBody();
            if (isset($post["confirm"]) && $post["confirm"] == "1") {
                $this->table->delete([ "id" => $id ]);
                return new RedirectResponse("{$this->baseRoute}");
            }
        } else if ($this->request->getMethod() == "DELETE") {
            $this->table->delete([ "id" => $id ]);
            return new RedirectResponse("{$this->baseRoute}");
        }

        $view = new View(Template::find("{$this->templatePath}/delete"));

        $view->assign("item", $item);

        $this->layout->assign("content", $view);
        return new HtmlResponse($this->layout->render());
    }

    abstract protected function getForm($purpose);
}
