<?php

namespace Lib\Controller;

use Lib\Controller\Feature\AbstractFeature;
use Lib\HttpException;
use Lib\MutableArray;
use Lib\Template;
use Lib\Tree;
use Lib\View\View;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\RedirectResponse;

abstract class Crud extends Rest
{
    protected $table;
    protected $view;
    protected $navigation;
    protected $features = [];
    protected $subnav;

    protected $templatePath = "crud";
    protected $baseRoute = "/crud";

    protected function beforeDispatch(string $action)
    {
        if ($this->subnav) {
            $this->subnav->setPathProperty($action, "active", true);
            if ($this->id) {
                $this->subnav->cascadePropertyReplace("url", ":id", $this->id);
            }
        }

        $this->trigger("setupNavigation", $this->navigation, $this->subnav);

        $this->layout->assign("navigation", $this->navigation);
        $this->layout->assign("subnav", $this->subnav);
    }

    public function addFeature(string $name, AbstractFeature $feature)
    {
        $feature->setController($this);
        $this->features[$name] = $feature;
    }

    public function setNavigation(Tree $navigation)
    {
        $this->navigation = $navigation;
    }

    protected function isAllowed($action)
    {
        return true;
    }

    public function index()
    {
        $this->view = new View(Template::find("{$this->templatePath}/index"));

        $items = $this->table->find();

        $this->trigger("filterItemsForIndex", $items);

        $this->view->assign("items", $items);
        $this->trigger("beforeRender", $this->view);
        $this->layout->assign("content", $this->view);


        return new HtmlResponse($this->layout->render());
    }

    public function show()
    {
        $item = $this->table->findOne([ "id" => (int)$this->id ]);

        if (!$item) {
            throw new HttpException(404);
        }

        $this->parseItemForShow($item);

        $this->view = new View(Template::find("{$this->templatePath}/show"));

        $this->view->assign("item", $item);
        $this->trigger("beforeRender", $this->view);
        $this->layout->assign("content", $this->view);

        return new HtmlResponse($this->layout->render());
    }

    public function createOrUpdate()
    {
        $purpose = "add";
        $item = null;

        if ($this->id) {
            $item = $this->table->findOne([ "id" => (int)$this->id ]);

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
                $values = new MutableArray($form->getValues());
                $this->trigger("filterValues", $values);
                if ($purpose == "add") {
                    $id = $this->table->insert($values->getArrayCopy());
                } else {
                    $this->table->update($values->getArrayCopy(), [ "id" => $id ]);
                }
                if ($id) {
                    $item = $this->table->findOne([ "id" => (int)$id ]);
                    $this->trigger("afterSave", $item);
                }
                return new RedirectResponse("{$this->baseRoute}/{$id}");
            }
        }

        $this->view = new View(Template::find("{$this->templatePath}/{$purpose}"));

        $this->view->assign("form", $form);
        $this->view->assign("item", $item);
        $this->view->assign("purpose", $purpose);

        $this->trigger("beforeRender", $this->view);
        $this->layout->assign("content", $this->view);

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

    public function edit()
    {
        return $this->createOrUpdate();
    }

    public function update()
    {
        return $this->createOrUpdate();
    }

    public function delete()
    {
        $item = $this->table->findOne([ "id" => (int)$this->id ]);

        if (!$item) {
            throw new HttpException(404);
        }

        if ($this->request->getMethod() == "POST") {
            $post = $this->request->getParsedBody();
            if (isset($post["confirm"]) && $post["confirm"] == "1") {
                $this->table->delete([ "id" => $this->id ]);
                return new RedirectResponse("{$this->baseRoute}");
            }
        } else if ($this->request->getMethod() == "DELETE") {
            $this->table->delete([ "id" => $this->id ]);
            return new RedirectResponse("{$this->baseRoute}");
        }

        $this->view = new View(Template::find("{$this->templatePath}/delete"));

        $this->view->assign("item", $item);
        $this->trigger("beforeRender", $this->view);

        $this->layout->assign("content", $this->view);
        return new HtmlResponse($this->layout->render());
    }

    public function setLayoutVariable($name, $value)
    {
        $this->layout->assign($name, $value);
    }

    public function setViewVariable($name, $value)
    {
        $this->view->assign($name, $value);
    }

    abstract protected function getForm($purpose);

    protected function setupNavigation($navigation, $subnav){}
    protected function filterItemsForIndex($items){}
    protected function parseItemForShow($item){}
    protected function filterValues($values){}
    protected function afterSave($item){}
    protected function beforeRender($view){}

    protected function trigger($event, ...$args)
    {
        call_user_func_array([ $this, $event ], $args);
        foreach ($this->features as $feature) {
            call_user_func_array([ $feature, $event ], $args);
        }
    }
}
