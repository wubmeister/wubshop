<?php

namespace App\Controller;

use App\Db\Schema;
use App\Form\Field\Field;
use App\Form\Form;
use App\Tree;

class Channels extends Crud
{
    protected $table;
    protected $layout;
    protected $request;

    protected $templatePath = "channels";
    protected $baseRoute = "/channels";

    public function __construct(Schema $schema)
    {
        $this->table = $schema->table("channel");
    }

    public function setNavigation(Tree $navigation)
    {
        parent::setNavigation($navigation);

        $navigation->cascadeProperty("settings/channels", "active", true);
    }

    protected function getForm($purpose)
    {
        $form = new Form();
        $form->addField(new Field("name", [ "required" => true ]));

        return $form;
    }
}
