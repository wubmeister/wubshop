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

    public function insert(string $tableName, array $data)
    {
        $keys = array_keys($data);
        $sql = "INSERT INTO {$tableName} (" .
            implode(', ', $keys) .
            ") VALUES (" .
            implode(", ", array_fill(0, count($keys), '?')) .
            ")";
        $stmt = $this->connection->prepare($sql);
        if (!$stmt) {
            $err = $this->connection->errorInfo;
            throw new Exception("Prepare error: {$err[2]}");
        }
        foreach ($keys as $index => $key) {
            $stmt->bindValue($index + 1, $data[$key]);
        }
        if (!$stmt->execute()) {
            $err = $stmt->errorInfo();
            throw new Exception("Execute error: {$err[2]}");
        }
        return $this->connection->lastInsertId();
    }
}
