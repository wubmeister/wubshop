<?php

namespace App\Controller;

use Lib\Controller\Crud;
use Lib\Db\Schema;
use Lib\Form\Field\Field;
use Lib\Form\Form;
use Lib\Tree;

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

    public function setNavigation(Tree $navigation)
    {
        parent::setNavigation($navigation);

        $navigation->setPathProperty("products/types", "active", true);
    }

    protected function getForm($purpose)
    {
        $form = new Form();
        $form->addField(new Field("name", [ "required" => true ]));

        return $form;
    }
}
