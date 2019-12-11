<?php

namespace Lib\Db;

class Query
{
    protected $sql;
    protected $connection;
    protected $params = [];

    public static function factory($query, Connection $connection = null)
    {
        if ($query instanceof Query) return $query;
        return new Query($query, $connection);
    }

    public function __construct($conditions, Connection $connection)
    {
        $this->connection = $connection;
        if (is_array($conditions)) {
            $this->parseConditions($conditions);
        }
    }

    protected function parseConditions($conditions)
    {
        $parts = [];
        foreach ($conditions as $key => $value) {
            $key = $this->connection->quoteIdentifier($key);
            if ($value === null) {
                $parts[] = "{$key} IS NULL";
            } else {
                $parts[] = "{$key} = ?";
                $this->params[] = $value;
            }
        }

        $this->sql = implode(" AND ", $parts);
    }

    public function getSql($clause = "WHERE")
    {
        return $this->sql ? " WHERE {$this->sql}" : "";
    }

    public function getBindValues()
    {
        return $this->params;
    }
}
