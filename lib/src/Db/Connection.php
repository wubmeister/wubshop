<?php

namespace Lib\Db;

use PDO;

/**
 * Provides a database connection
 *
 * @author Wubbo Bos
 */
class Connection
{
    /** @var PDO $pdo */
    protected $pdo;

    /** @var string $quoteChar The character to use to quote identifiers */
    protected $quoteChar = '"';

    /** @var string $defaultDbName */
    protected $defaultDbName = "global";

    /** @var array $schemas All loaded schemas */
    protected $schemas = [];

    /** @var array $schemas All registered schemas, which are basically aliases */
    protected $registeredSchemas;

    /**
     * Constructor
     *
     * @param string $driver The driver ('mysql', 'sqlite' etc.)
     * @param array $options Options to connect, like host, username and password
     * @param array $schemas Optional. Schema aliases
     */
    public function __construct(string $driver, array $options, array $schemas = [])
    {
        $username = null;
        if (isset($options["username"])) {
            $username = $options["username"];
            unset($options["username"]);
        }
        $password = null;
        if (isset($options["password"])) {
            $password = $options["password"];
            unset($options["password"]);
        }
        $opts = null;
        if (isset($options["options"])) {
            $opts = $options["options"];
            unset($options["options"]);
        }

        if (isset($options["dbname"])) {
            $this->defaultDbName = $options["dbname"];
        }

        $dsnParts = [];
        foreach ($options as $key => $value) {
            $dsnParts[] = "{$key}={$value}";
        }
        $dsn = $driver . ':' . implode(";", $dsnParts);

        $this->pdo = new PDO($dsn, $username, $password, $opts);

        $this->registeredSchemas = $schemas;

        if ($driver == "mysql") {
            $this->quoteChar = '`';
        }
    }

    // Here are some PDO pass-through methods

    public function beginTransaction()
    {
        return $this->pdo->beginTransaction();
    }

    public function commit()
    {
        return $this->pdo->commit();
    }

    public function errorCode()
    {
        return $this->pdo->errorCode();
    }

    public function errorInfo()
    {
        return $this->pdo->errorInfo();
    }

    public function exec(string $statement)
    {
        return $this->pdo->exec($statement);
    }

    public function getAttribute(int $attribute)
    {
        return $this->pdo->getAttribute($attribute);
    }

    public static function getAvailableDrivers()
    {
        return $this->pdo->getAvailableDrivers();
    }

    public function inTransaction()
    {
        return $this->pdo->inTransaction();
    }

    public function lastInsertId(string $name = null)
    {
        return $this->pdo->lastInsertId($name);
    }

    public function prepare(string $statement, array $driver_options = [])
    {
        return $this->pdo->prepare($statement, $driver_options);
    }

    public function query(string $statement)
    {
        return $this->pdo->query($statement);
    }

    public function quote(string $string, int $parameter_type = PDO::PARAM_STR)
    {
        return $this->pdo->quote($string, $parameter_type);
    }

    public function rollBack()
    {
        return $this->pdo->rollBack();
    }

    public function setAttribute(int $attribute, mixed $value)
    {
        return $this->pdo->setAttribute($attribute, $value);
    }

    /**
     * Gets a schema with the specified name
     *
     * @param string $name If no name is given, the default schema will be returned
     * @return Schema
     */
    public function schema($name = null)
    {
        if (!$name) $name = $this->defaultDbName;
        if (!isset($this->schemas[$name])) {
            $schemaName = $name;
            if (isset($this->registeredSchemas[$name])) {
                $schemaName = $this->registeredSchemas[$name];
            }
            if (isset($this->schemas[$schemaName])) {
                $this->schemas[$name] = $this->schemas[$schemaName];
            } else {
                $this->schemas[$name] = new Schema($this, $schemaName);
            }
        }
        return $this->schemas[$name];
    }

    /**
     * Quotes an identifier
     *
     * @param string $identifier
     * @return string The quoted identifier
     */
    public function quoteIdentifier($identifier)
    {
        $qc = $this->quoteChar;
        return $qc . str_replace('.', "{$qc}.{$qc}", $identifier) . $qc;
    }
}
