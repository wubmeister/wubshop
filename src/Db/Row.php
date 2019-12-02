<?php

namespace App\Db;

use Exception;

class Row
{
    protected $table;
    protected $data;
    protected $isInDb = false;

    public function __construct(Table $table, array $data = [], $isInDb = false)
    {
        $this->table = $table;
        $this->data = $data;
        $this->isInDb = $isInDb;
    }

    public function __get($name)
    {
        return isset($this->data[$name]) ? $this->data[$name] : null;
    }

    public function __set($name, $value)
    {
        $this->data[$name] = $value;
    }

    public function save()
    {
        if ($this->isInDb) {
            $data = $this->data;
            if (!isset($data["id"])) {
                throw new Exception("Row claims to be in database, but has no ID");
            }
            $id = $data["id"];
            unset($data["id"]);
            $this->table->update($data, [ "id" => $id ]);
        } else {
            $id = $this->table->insert($this->data);
            $this->data["id"] = $id;
            $this->isInDb = true;
        }
    }

    public function toArray()
    {
        return $this->data;
    }

    public function link($table, $cond, $property = null)
    {
        if (!$property) $property = $table;

        foreach ($cond as $key => $col) {
            $cond[$key] = $this->data[$col];
        }

        $this->$property = $this->table->getSchema()->table($table)->findOne($cond);

        return $this;
    }
}
