<?php

namespace App\Db;

class Query
{
    protected $sql;
    protected $params = [];

    public static function factory($query)
    {
        if ($query instanceof Query) return $query;
        return new Query($query);
    }

    public function __construct($conditions)
    {
        if (is_array($conditions)) {
            $this->parseConditions($conditions);
        }
    }

    protected function parseConditions($conditions)
    {
        $parts = [];
        foreach ($conditions as $key => $value) {
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
