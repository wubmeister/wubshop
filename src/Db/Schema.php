<?php

namespace App\Db;

use Exception;

class Schema
{
    protected $connection;
    protected $name;
    protected $tables = [];

    public function __construct(Connection $connection, string $name)
    {
        $this->connection = $connection;
        $this->name = $name;
    }

    public function getConnection()
    {
        return $this->connection;
    }

    public function table($name)
    {
        if (!isset($this->tables[$name])) {
            $this->tables[$name] = new Table($this, $name);
        }

        return $this->tables[$name];
    }
}
