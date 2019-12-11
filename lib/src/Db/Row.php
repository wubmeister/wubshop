<?php

namespace Lib\Db;

use Exception;

/**
 * Class to contain a row returned from a table
 *
 * @author Wubbo Bos
 */
class Row
{
    /** @var Table $table */
    protected $table;

    /** @var array $data */
    protected $data;

    /** @var bool $isInDb */
    protected $isInDb = false;

    /**
     * Constructor
     *
     * @param Table $table
     * @param array $data
     * @param bool $isInDb
     */
    public function __construct(Table $table, array $data = [], $isInDb = false)
    {
        $this->table = $table;
        $this->data = $data;
        $this->isInDb = $isInDb;
    }

    /**
     * Returns the value of a column
     *
     * @param string $name
     * @return mixed The value of the specified column, or NULL if that column doesn't exist
     */
    public function __get($name)
    {
        return isset($this->data[$name]) ? $this->data[$name] : null;
    }

    /**
     * Sets the value of a column
     *
     * @param string $name
     * @param mixed $value
     */
    public function __set($name, $value)
    {
        $this->data[$name] = $value;
    }

    /**
     * Saves the row to the table
     *
     * This means performing either an insert o an update
     */
    public function save()
    {
        if ($this->isInDb) {
            $data = $this->data;
            if (!isset($data["id"])) {
                throw new Exception("Row claims to be in database, but has no ID");
            }
            $id = $data["id"];
            unset($data["id"]);
            $this->table->update($data, [ "id" => $id ]);
        } else {
            $id = $this->table->insert($this->data);
            $this->data["id"] = $id;
            $this->isInDb = true;
        }
    }

    /**
     * Returns the array equivalent of the row.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->data;
    }

    /**
     * Fetch a row in a linked table corresponding to this row
     *
     * @param string $table The table name
     * @param array $cond The link condition(s)
     * @param string $property Optional. Tells the method in which property to put the retrieved row. Defauls to the table name.
     */
    public function link($table, $cond, $property = null)
    {
        if (!$property) $property = $table;

        foreach ($cond as $key => $col) {
            $cond[$key] = $this->data[$col];
        }

        $this->$property = $this->table->getSchema()->table($table)->findOne($cond);

        return $this;
    }
}
