<?php

namespace Lib\Db;

use Exception;

/**
 * Represents a database (schema)
 *
 * @author Wubbo Bos
 */
class Schema
{
    /** @var Connection $connection */
    protected $connection;

    /** @var string $name */
    protected $name;

    /** @var array $tables The loaded tables */
    protected $tables = [];

    /**
     * Constructor
     *
     * @param Connection $connection
     * @param string $name
     */
    public function __construct(Connection $connection, string $name)
    {
        $this->connection = $connection;
        $this->name = $name;
    }

    /**
     * Returns the schema's database connection
     *
     * return Connection
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * Returns a table from the schema
     *
     * @param string $name The table name
     * return Table
     */
    public function table(string $name)
    {
        if (!isset($this->tables[$name])) {
            $this->tables[$name] = new Table($this, $name);
        }

        return $this->tables[$name];
    }

    /**
     * Executes an SQL query with the proved bind values
     *
     * @param string $sql
     * @param array $bindValues
     */
    protected function execute(string $sql, array $bindValues)
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

    /**
     * Logs a change in the schema's changes table
     *
     * @param string $action
     * @param string $tableName
     * @param mixed $rowId Optional.
     * @param array $changes Optional. The fields which have changed with their new values
     */
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

    /**
     * Inserts a new row in a table
     *
     * @param string $tableName
     * @param array $data
     * @param bool $ignore Optional. Pass TRUE to do an INSERT IGNORE
     * @return mixed The ID of the inserted row
     */
    public function insert(string $tableName, array $data, bool $ignore = false)
    {
        $keys = array_keys($data);
        $ignoreStr = $ignore ? " IGNORE" : "";
        $sql = "INSERT{$ignoreStr} INTO {$tableName} (" .
            implode(', ', $keys) .
            ") VALUES (" .
            implode(", ", array_fill(0, count($keys), '?')) .
            ")";

        $this->execute($sql, array_values($data));
        $rowId = $this->connection->lastInsertId();
        if ($rowId) {
            $this->logChange("insert", $tableName, $rowId, $data);
        }

        return $rowId;
    }

    /**
     * Updates one or more rows in a table
     *
     * @param string $tableName
     * @param array $data
     * @param Query|array The conditions to filter the rows to update
     */
    public function update(string $tableName, array $data, $where)
    {
        $query = Query::factory($where, $this->connection);
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

    /**
     * Deletes one or more rows from a table
     *
     * @param string $tableName
     * @param Query|array The conditions to filter the rows to update
     */
    public function delete(string $tableName, $where)
    {
        $query = Query::factory($where, $this->connection);
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

    /**
     * Fetch all results from an SQL query and return the values as an associative array
     *
     * @param string $sql
     * @param array $bindValues
     * @return array
     */
    public function fetchAll(string $sql, array $bindValues)
    {
        $stmt = $this->execute($sql, $bindValues);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Fetches the columns from a table
     *
     * @param string $tableName
     * @return array [ "column_name" => $info ]
     */
    public function getColumns(string $tableName)
    {
        return $this->connection->getTableColumns($this->name, $tableName);
    }
}
