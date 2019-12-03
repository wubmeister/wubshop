<?php

namespace App\Controller;

use App\Db\Schema;
use App\Form\Field\Field;
use App\Form\Form;

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

    protected function getForm($purpose)
    {
        $form = new Form();
        $form->addField(new Field("name", [ "required" => true ]));

        return $form;
    }
}
