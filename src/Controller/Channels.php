<?php

namespace App\Controller;

use Lib\Controller\Crud;
use Lib\Db\Schema;
use Lib\Form\Field\Field;
use Lib\Form\Form;
use Lib\Tree;

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

        $navigation->setPathProperty("settings/channels", "active", true);
    }

    protected function getForm($purpose)
    {
        $form = new Form();
        $form->addField(new Field("name", [ "required" => true ]));

        return $form;
    }
}
