<?php

namespace Lib\Db;

/**
 * Class to build a query from a conditions array
 *
 * @author Wubbo Bos
 */
class Query
{
    /** @var string $sql The generated SQL */
    protected $sql;

    /** @var Connection $connection */
    protected $connection;

    /** @var array $params */
    protected $params = [];

    /**
     * The factory ensures that you get a Query instance, whether you input a
     * conditions array or a Query instance
     *
     * @param Query|array $query
     * @param Connection $connection
     * @return Query
     */
    public static function factory($query, Connection $connection = null)
    {
        if ($query instanceof Query) return $query;
        return new Query($query, $connection);
    }

    /**
     * Constructor
     *
     * @param array $conditions Conditions array
     * @param Connection $connection
     */
    public function __construct($conditions, Connection $connection)
    {
        $this->connection = $connection;
        if (is_array($conditions)) {
            $this->parseConditions($conditions);
        }
    }

    /**
     * Converts conditions to a string
     *
     * @param array $conditions
     * @return string
     */
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

    /**
     * Returns the SQL clause as string
     *
     * @param string $clause 'WHERE' or 'HAVING'
     * @return string
     */
    public function getSql($clause = "WHERE")
    {
        $clause = strtoupper($clause);
        return $this->sql ? " {$clause} {$this->sql}" : "";
    }

    /**
     * Returns the bind values
     *
     * @return array
     */
    public function getBindValues()
    {
        return $this->params;
    }
}
