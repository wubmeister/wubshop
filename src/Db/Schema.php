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

    public function update(string $tableName, array $data, $where)
    {
        $keys = array_keys($data);
        $sql = "UPDATE {$tableName} SET " . implode(' = ?, ', $keys) . " = ?";
        $query = Query::factory($where);
        $sql .= $query->getSql();

        $stmt = $this->connection->prepare($sql);
        if (!$stmt) {
            $err = $this->connection->errorInfo;
            throw new Exception("Prepare error: {$err[2]}");
        }
        foreach ($keys as $index => $key) {
            $stmt->bindValue($index + 1, $data[$key]);
        }
        $offset = count($keys) + 1;
        foreach ($query->getBindValues() as $index => $value) {
            $stmt->bindValue($offset + $index, $value);
        }
        if (!$stmt->execute()) {
            $err = $stmt->errorInfo();
            throw new Exception("Execute error: {$err[2]}");
        }
        return $this->connection->lastInsertId();
    }
}
