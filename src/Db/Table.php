<?php

namespace App\Db;

class Table
{
    protected $schema;
    protected $name;

    public function __construct(Schema $schema, string $name)
    {
        $this->schema = $schema;
        $this->name = $name;
    }

    public function getSchema()
    {
        return $this->schema;
    }

    public function getName()
    {
        return $this->name;
    }

    public function find($query = null)
    {
        return new ResultSet($this, $query);
    }

    public function findOne($query)
    {
        $resultSet = $this->find($query);
        return $resultSet->current();
    }

    public function insert($data)
    {
        $data["created"] = date("Y-m-d H:i:s");
        return $this->schema->insert($this->name, $data);
    }

    public function insertIgnore($data)
    {
        $data["created"] = date("Y-m-d H:i:s");
        return $this->schema->insert($this->name, $data, true);
    }

    public function update($data, $where)
    {
        $data["modified"] = date("Y-m-d H:i:s");
        return $this->schema->update($this->name, $data, $where);
    }

    public function delete($where)
    {
        return $this->schema->delete($this->name, $where);
    }
}
