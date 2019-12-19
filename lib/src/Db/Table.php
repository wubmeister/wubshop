<?php

namespace Lib\Db;

/**
 * Represents a table in a database
 *
 * @author Wubbo Bos
 */
class Table
{
    /** @var Schema $schema */
    protected $schema;

    /** @var string $name */
    protected $name;

    /** @var array $columns */
    protected $columns;

    /**
     * Constructor
     *
     * @param Schema $schema
     * @param string $name
     */
    public function __construct(Schema $schema, string $name)
    {
        $this->schema = $schema;
        $this->name = $name;
        $this->columns = $this->schema->getColumns($name);
    }

    /**
     * Gets the schema this table belongs to
     *
     * @return Schema
     */
    public function getSchema()
    {
        return $this->schema;
    }

    /**
     * Gets the name of the table
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Finds rows in the table matching the specified conditions
     *
     * @param Query|array $query
     * @return ResultSet
     */
    public function find($query = null)
    {
        return new ResultSet($this, $query);
    }

    /**
     * Finds one row in the table matching the specified conditions
     *
     * @param Query|array $query
     * @return Row
     */
    public function findOne($query)
    {
        $resultSet = $this->find($query);
        return $resultSet->current();
    }

    /**
     * Inserts a row in the table
     *
     * @param array $data
     * @return mixed The ID of the inserted row
     */
    public function insert($data)
    {
        if (isset($this->columns["created"])) {
            $data["created"] = date("Y-m-d H:i:s");
        }
        return $this->schema->insert($this->name, $data);
    }

    /**
     * Inserts a row in the table using an INSERT IGNORE
     *
     * @param array $data
     * @return mixed The ID of the inserted row
     */
    public function insertIgnore($data)
    {
        if (isset($this->columns["created"])) {
            $data["created"] = date("Y-m-d H:i:s");
        }
        return $this->schema->insert($this->name, $data, true);
    }

    /**
     * Updates one or more rows in the table
     *
     * @param array $data
     * @param Query|array The conditions to filter the rows to update
     */
    public function update($data, $where)
    {
        if (isset($this->columns["modified"])) {
            $data["modified"] = date("Y-m-d H:i:s");
        }
        return $this->schema->update($this->name, $data, $where);
    }

    /**
     * Deletes one or more rows from the table
     *
     * @param Query|array The conditions to filter the rows to delete
     */
    public function delete($where)
    {
        return $this->schema->delete($this->name, $where);
    }
}
