<?php

namespace Lib\Controller\Feature;

use Lib\Controller\Crud;

class AbstractFeature
{
    protected $controller;

    public function setController(Crud $controller)
    {
        $this->controller = $controller;
    }

    /**
     * Dummy funtions allowing all conrtoller life cycle functions to be called on each feature.
     */

    public function filterItemsForIndex($items){}
    public function parseItemForShow($item){}
    public function filterValues($values){}
    public function afterSave($item){}
    public function beforeRender($view){}
}
