<?php

namespace App\Controller\ProductTypes;

use Lib\Controller\Crud;
use Lib\Controller\Feature\Parenting;
use Lib\Db\Schema;
use Lib\Form\Field\Field;
use Lib\Form\Field\Options;
use Lib\Form\Form;
use Lib\Tree;
use Psr\Http\Message\ServerRequestInterface;

class Attributes extends Crud
{
    protected $templatePath = "product-types/attributes";
    protected $baseRoute = "/product-types/#parent_id#/attributes";

    public function __construct(Schema $schema)
    {
        $this->table = $schema->table("attribute");
    }

    public function setNavigation(Tree $navigation)
    {
        parent::setNavigation($navigation);

        $navigation->setPathProperty("products/types", "active", true);
    }

    public function __invoke(ServerRequestInterface $request)
    {
        $ids = $request->getAttribute("ids");
        $parentId = $ids["product-types"];
        $this->baseRoute = "/product-types/{$parentId}/attributes";

        $this->addFeature("parenting", new Parenting(
            $this->table->getSchema()->table("product_type"),
            $parentId,
            "product_type_id",
            "attribute_id",
            "product_type_has_attribute"));

        return parent::__invoke($request);
    }

    protected function getForm($purpose)
    {
        $form = new Form();
        $form->addField(new Field("name", [ "required" => true ]));
        $form->addField(new Options("data_type", [ "options" => [
            "bool" => "Boolean",
            "int" => "Integer",
            "float" => "Number with decimalen",
            "string" => "String",
            "options" => "Multiple choice"
        ], "required" => true ]));
        $form->addField(new Field("is_array"));
        $form->addField(new Field("units"));

        return $form;
    }
}
