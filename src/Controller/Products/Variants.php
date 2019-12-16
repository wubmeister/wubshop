<?php

namespace App\Controller\Products;

use Lib\Controller\Crud;
use Lib\Db\Schema;
use Lib\Form\Field\Field;
use Lib\Form\Form;
use Lib\Tree;
use Psr\Http\Message\ServerRequestInterface;

class Variants extends Crud
{
    protected $table;
    protected $layout;
    protected $parentId;
    protected $parentItem;

    protected $templatePath = "products/variants";
    protected $baseRoute = "/products/ID/variants";

    public function __construct(Schema $schema)
    {
        $this->table = $schema->table("product");
        $this->subnav = Tree::fromArray([ "children" => [
            "show_parent" => [ "label" => "Product", "url" => "/products/:parent_id" ],
            "edit_parent" => [ "label" => "Edit", "url" => "/products/edit/:parent_id" ],
            "index" => [ "label" => "Variants", "url" => "/products/:parent_id/variants" ],
            "show" => [ "label" => "Product variant", "url" => "/products/:parent_id/variants/:id" ],
            "edit" => [ "label" => "Edit variant", "url" => "/products/:parent_id/variants/edit/:id" ]
        ]]);
    }

    public function setNavigation(Tree $navigation)
    {
        parent::setNavigation($navigation);

        $navigation->setPathProperty("products", "active", true);
    }

    public function __invoke(ServerRequestInterface $request)
    {
        $ids = $request->getAttribute("ids");
        $this->parentId = $ids["products"];
        $this->baseRoute = "/products/{$this->parentId}/variants";

        $this->parentItem = $this->table->findOne([ "id" => $this->parentId ]);
        if ($this->subnav) {
            $this->subnav->cascadePropertyReplace("url", ":parent_id", $this->parentId);
        }

        return parent::__invoke($request);
    }

    protected function filterItemsForIndex($items)
    {
        $this->subnav->setPathProperty("show", "hidden", true);
        $this->subnav->setPathProperty("edit", "hidden", true);

        $items->filter([ "parent_id" => $this->parentId ]);
        // $items->link("product_type", [ "id" => "product_type_id" ], [ "product_type" => "name" ]);
    }

    protected function parseItemForShow($item)
    {
        // $item->link("product_type", [ "id" => "product_type_id" ]);
    }

    protected function filterValues($values)
    {
        $values["parent_id"] = $this->parentItem->id;
        $values["product_type_id"] = $this->parentItem->product_type_id;
    }

    protected function beforeRender($view)
    {
        $view->assign("parentItem", $this->parentItem);
    }

    protected function getForm($purpose)
    {
        $form = new Form();
        $form->addField(new Field("title", [ "required" => true ]));
        $form->addField(new Field("base_price"));
        $form->addField(new Field("sku"));
        $form->addField(new Field("gtin"));

        return $form;
    }
}
