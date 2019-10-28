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

    public function fetchAll()
    {
        return $this->schema->fetchAll($this->name);
    }
}
