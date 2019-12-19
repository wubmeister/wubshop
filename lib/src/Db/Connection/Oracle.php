<?php

namespace Lib\Db\Connection;

use PDO;
use Exception;
use Lib\Db\Connection;

/**
 * Provides a database connection
 *
 * @author Wubbo Bos
 */
class Oracle extends Connection
{
    /** @var string $quoteChar The character to use to quote identifiers */
    protected $quoteChar = '`';

    /**
     * Constructor
     *
     * @param array $options Options to connect, like host, username and password
     * @param array $schemas Optional. Schema aliases
     */
    public function __construct(array $options, array $schemas = [])
    {
        parent::__construct("oracle", $options, $schemas);
    }

    /**
     * Fetches the columns from a table
     *
     * @param string $schemaName
     * @param string $tableName
     * @return array [ "column_name" => $info ]
     */
    public function getTableColumns(string $schemaName, string $tableName)
    {
        $sql = "SELECT table_name, column_name, data_type, data_length
            FROM USER_TAB_COLUMNS
            WHERE table_name = '{$schemaName}.{$tableName}'";

        $stmt = $this->pdo->query($sql);
        if (!$stmt) {
            $err = $this->pdo->errorInfo();
            throw new Exception($err[2]);
        }
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $columns = [];
        foreach ($records as $record) {
            $colName = $record["column_name"];
            $columns[$colName] = $record["data_type"];
        }

        return $columns;
    }
}
