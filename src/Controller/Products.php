<?php

namespace App\Controller;

use Lib\Controller\Crud;
use Lib\Db\Schema;
use Lib\Form\Field\Field;
use Lib\Form\Field\Options;
use Lib\Form\Form;
use Lib\Tree;

class Products extends Crud
{
    protected $table;
    protected $layout;
    protected $request;

    protected $templatePath = "products";
    protected $baseRoute = "/products";

    public function __construct(Schema $schema)
    {
        $this->table = $schema->table("product");
    }

    public function setNavigation(Tree $navigation)
    {
        parent::setNavigation($navigation);

        $navigation->cascadeProperty("products", "active", true);
    }

    protected function filterItemsForIndex($items)
    {
        $items
            ->filter([ "parent_id" => null ])
            ->link("product_type", [ "id" => "product_type_id" ], [ "product_type" => "name" ]);
    }

    protected function parseItemForShow($item)
    {
        $item->link("product_type", [ "id" => "product_type_id" ]);
    }

    protected function getForm($purpose)
    {
        $typesTable = $this->table->getSchema()->table("product_type");
        $types = $typesTable->find()->getOptions();

        $form = new Form();
        $form->addField(new Field("title", [ "required" => true ]));
        $form->addField(new Options("product_type_id", [ "options" => $types ]));
        $form->addField(new Field("base_price"));
        $form->addField(new Field("sku"));
        $form->addField(new Field("gtin"));

        return $form;
    }
}
