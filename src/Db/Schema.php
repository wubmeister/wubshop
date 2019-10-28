<?php

namespace App\Db;

use Exception;

class Schema
{
    protected $conn;
    protected $name;
    protected $tables = [];

    public function __construct(Connection $connection, string $name)
    {
        $this->conn = $connection;
        $this->name = $name;
    }

    public function fetchAll($table)
    {
        $stmt = $this->conn->query("SELECT * FROM {$table}");
        if (!$stmt) {
            $err = $this->conn->errorInfo();
            throw new Exception($err[2]);
        }
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function table($name)
    {
        if (!isset($this->tables[$name])) {
            $this->tables[$name] = new Table($this, $name);
        }

        return $this->tables[$name];
    }
}
