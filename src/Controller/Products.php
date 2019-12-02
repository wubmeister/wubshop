<?php

namespace App\Controller;

use App\Db\Schema;
use App\Form\Field\Field;
use App\Form\Form;

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

    protected function getForm($purpose)
    {
        $form = new Form();
        $form->addField(new Field("title", [ "required" => true ]));
        $form->addField(new Field("sku"));
        $form->addField(new Field("gtin"));

        return $form;
    }
}
