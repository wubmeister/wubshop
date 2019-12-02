<?php

namespace App\Controller;

use App\Db\Schema;
use App\Form\Field\Field;
use App\Form\Form;

class ProductTypes extends Crud
{
    protected $table;
    protected $layout;
    protected $request;

    protected $templatePath = "product-types";
    protected $baseRoute = "/product-types";

    public function __construct(Schema $schema)
    {
        $this->table = $schema->table("product_type");
    }

    protected function getForm($purpose)
    {
        $form = new Form();
        $form->addField(new Field("name", [ "required" => true ]));

        return $form;
    }
}
