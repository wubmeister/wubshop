<?php

namespace Lib\Controller\Feature;

use Lib\Db\Table;

class Parenting extends AbstractFeature
{
    protected $table;
    protected $parentId;
    protected $parentCol;
    protected $childCol;
    protected $linkTable;

    public function __construct(Table $table, $parentId, $parentCol, $childCol = null, $linkTable = null)
    {
        $this->table = $table;
        $this->parentId = $parentId;
        $this->parentCol = $parentCol;
        $this->childCol = $childCol;
        $this->linkTable = $linkTable;
    }

    public function filterItemsForIndex($items)
    {
        $filter = [];
        $filter[$this->parentCol] = $this->parentId;
        if ($this->linkTable) {
            $items->filterLinked($this->linkTable, $this->childCol, $filter);
        } else {
            $items->filter($filter);
        }
    }

    public function filterValues($values)
    {
        if (!$this->linkTable) {
            $values[$this->parentCol] = $this->parentId;
        }
    }

    public function afterSave($item)
    {
        if ($this->linkTable) {
            $table = $this->table->getSchema()->table($this->linkTable);
            $data = [];
            $data[$this->parentCol] = $this->parentId;
            $data[$this->childCol] = $item->id;
            $table->insertIgnore($data);
        }
    }

    public function beforeRender($view)
    {
        $this->controller->setViewVariable("parentItem", $this->table->findOne([ "id" => $this->parentId ]));
    }
}
