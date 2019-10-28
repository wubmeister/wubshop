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

    public function find()
    {
        return new ResultSet($this, null);
    }

    public function findOne($query)
    {
        $resultSet = $this->find($query);
        return $resultSet->current();
    }
}
