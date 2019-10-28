<?php

namespace App\Db;

use PDO;

class Connection
{
    protected $pdo;
    protected $defaultDbName = "global";
    protected $schemas = [];

    public function __construct($driver, array $options)
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
    }

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

    public function schema($name = null)
    {
        if (!$name) $name = $this->defaultDbName;
        if (!isset($this->schemas[$name])) {
            $this->schemas[$name] = new Schema($this, $name);
        }
        return $this->schemas[$name];
    }
}
