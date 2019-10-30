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

    protected function execute($sql, $bindValues)
    {
        $stmt = $this->connection->prepare($sql);
        if (!$stmt) {
            $err = $this->connection->errorInfo();
            throw new Exception("Prepare error: {$err[2]}");
        }
        foreach ($bindValues as $index => $value) {
            if (is_int($index)) {
                $stmt->bindValue($index + 1, $value);
            } else {
                $stmt->bindValue($index, $value);
            }
        }
        if (!$stmt->execute()) {
            $err = $stmt->errorInfo();
            throw new Exception("Execute error: {$err[2]}");
        }

        return $stmt;
    }

    public function insert(string $tableName, array $data)
    {
        $keys = array_keys($data);
        $sql = "INSERT INTO {$tableName} (" .
            implode(', ', $keys) .
            ") VALUES (" .
            implode(", ", array_fill(0, count($keys), '?')) .
            ")";
        $this->execute($sql, array_values($data));
        return $this->connection->lastInsertId();
    }

    public function update(string $tableName, array $data, $where)
    {
        $keys = array_keys($data);
        $sql = "UPDATE {$tableName} SET " . implode(' = ?, ', $keys) . " = ?";
        $query = Query::factory($where);
        $sql .= $query->getSql();
        $this->execute($sql, array_merge(array_values($data), $query->getBindValues()));
    }

    public function delete(string $tableName, $where)
    {
        $query = Query::factory($where);
        $sql = "DELETE FROM {$tableName}" . $query->getSql();
        $this->execute($sql, $query->getBindValues());
    }

    public function fetchAll($sql, $bindValues)
    {
        $stmt = $this->execute($sql, $bindValues);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
