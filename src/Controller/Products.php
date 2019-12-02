<?php

namespace App\Controller;

use App\Db\Schema;
use App\Form\Field\Field;
use App\Form\Field\Options;
use App\Form\Form;
use App\Template;
use App\View\View;
use Zend\Diactoros\Response\HtmlResponse;

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
        $form->addField(new Field("sku"));
        $form->addField(new Field("gtin"));

        return $form;
    }
}
