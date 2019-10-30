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

    protected function logChange($action, $tableName, $rowId = null, $changes = null)
    {
        if (substr($tableName, 0, 3) == "s_") return;

        $sql = "INSERT INTO s_change (created, table_name, row_id, action) VALUES (?, ?, ?, ?)";
        $this->execute($sql, [
            date('Y-m-d H:i:s'),
            $tableName,
            $rowId,
            $action
        ]);
        if ($changes) {
            $changeId = $this->connection->lastInsertId();
            $sql = "INSERT INTO s_change_data (change_id, is_compressed, changes) VALUES (?, ?, ?)";
            $this->execute($sql, [
                $changeId,
                0,
                json_encode($changes)
            ]);
        }
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
        $rowId = $this->connection->lastInsertId();
        if ($rowId) {
            $this->logChange("insert", $tableName, $rowId, $data);
        }
    }

    public function update(string $tableName, array $data, $where)
    {
        $query = Query::factory($where);
        $rows = $this->fetchAll("SELECT * FROM {$tableName}" . $query->getSql(), $query->getBindValues());
        if (count($rows)) {
            $keys = array_keys($data);
            $sql = "UPDATE {$tableName} SET " . implode(' = ?, ', $keys) . " = ?";
            $sql .= $query->getSql();
            $this->execute($sql, array_merge(array_values($data), $query->getBindValues()));

            if (substr($tableName, 0, 3) != "s_") {
                foreach ($rows as $row) {
                    if (isset($row["id"])) {
                        $changes = [];
                        foreach ($data as $key => $value) {
                            if ($key == "modified") continue;
                            if ($row[$key] != $value) $changes[$key] = $value;
                        }
                        if (count($changes) > 0) {
                            $this->logChange("update", $tableName, $row["id"], $changes);
                        }
                    }
                }
            }
        }
    }

    public function delete(string $tableName, $where)
    {
        $query = Query::factory($where);
        $rows = $this->fetchAll("SELECT * FROM {$tableName}" . $query->getSql(), $query->getBindValues());
        if (count($rows)) {
            $sql = "DELETE FROM {$tableName}" . $query->getSql();
            $this->execute($sql, $query->getBindValues());

            foreach ($rows as $row) {
                if (isset($row["id"])) {
                    $this->logChange("delete", $tableName, $row["id"]);
                }
            }
        }
    }

    public function fetchAll($sql, $bindValues)
    {
        $stmt = $this->execute($sql, $bindValues);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
