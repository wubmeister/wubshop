<?php

namespace App\Controller;

use App\Db\Schema;
use App\Form\Field\Field;
use App\Form\Form;
use App\Tree;

class Languages extends Crud
{
    protected $table;
    protected $layout;
    protected $request;

    protected $templatePath = "languages";
    protected $baseRoute = "/languages";

    public function __construct(Schema $schema)
    {
        $this->table = $schema->table("language");
    }

    public function setNavigation(Tree $navigation)
    {
        parent::setNavigation($navigation);

        $navigation->cascadeProperty("settings/languages", "active", true);
    }

    protected function getForm($purpose)
    {
        $form = new Form();
        $form->addField(new Field("name", [ "required" => true ]));
        $form->addField(new Field("localized_name", [ "required" => true ]));
        $form->addField(new Field("lang_code", [ "required" => true ]));

        return $form;
    }
}
