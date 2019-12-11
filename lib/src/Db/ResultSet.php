<?php

namespace Lib\Db;

use Iterator;

/**
 * Represents the results returned by a table query
 *
 * @author Wubbo Bos
 */
class ResultSet implements Iterator
{
    /** @var Table $table */
    protected $table;

    /** @var Query|array $query */
    protected $query;

    /** @var int $limitValue */
    protected $limitValue;

    /** @var int $offsetValue */
    protected $offsetValue;

    /** @var array $orderBy */
    protected $orderBy;

    /** @var array $links Joins to other tables */
    protected $links = [];

    /** @var array $filterLink Link table to which the current table is joined */
    protected $filterLink = null;

    /** @var array $columns All the columns to fetch */
    protected $columns = [];

    /** @var mixed $pages Pagination data */
    protected $pages;

    /** @var array $rows All the fetched rows */
    protected $rows;

    /** @var int $count Number of fetched rows */
    protected $count;

    /** @var int $index Iteration index */
    protected $index;

    /**
     * Constructor
     *
     * @param Table $table
     * @param Query|array $query
     */
    public function __construct(Table $table, $query)
    {
        $this->table = $table;
        $this->query = $query;
    }

    /**
     * Orders the result
     *
     * @param array $fields [ "field", "field" => "diretion", ... ]
     * @param bool $reset Pass TRUE to reset the current order. Pass FALSE or omit to extend the current ordering
     * @return ResultSet Chainable
     */
    public function order($fields, $reset = false)
    {
        if (!is_array($fields)) {
            $fields = [ $fields ];
        }

        if ($reset) {
            $this->orderBy = $fields;
        } else {
            $this->orderBy = array_merge($this->order, $fields);
        }

        return $this;
    }

    /**
     * Limits the result
     *
     * @param int $limit Maximum number of rows to fetch
     * @return ResultSet Chainable
     */
    public function limit($limit)
    {
        $this->limitValue = $limit;

        return $this;
    }

    /**
     * Start fetching from the specified offset
     *
     * @param int $offset
     * @return ResultSet Chainable
     */
    public function offset($offset)
    {
        $this->offsetValue = $offset;

        return $this;
    }

    /**
     * Start fetching from the specified offset and limit the number of results.
     *
     * This is shorthand for $this->offset($offset)->limit($limit);
     *
     * @param int $offset Offset to start from
     * @param int $limit Maximum number of rows to fetch
     * @return ResultSet Chainable
     */
    public function slice($offset, $limit)
    {
        $this->offsetValue = $offset;
        $this->limitValue = $limit;

        return $this;
    }

    /**
     * Adds columns to fetch
     *
     * @param string $table Table name
     * @param array|null $columns The columns to fetch from this table
     */
    protected function addColumns(string $table, $columns)
    {
        $connection = $this->table->getSchema()->getConnection();
        if ($columns) {
            foreach ($columns as $key => $column) {
                if (is_numeric($key)) {
                    $this->columns[] = $connection->quoteIdentifier("{$table}.{$column}");
                } else {
                    $this->columns[] = $connection->quoteIdentifier("{$table}.{$column}") . " AS " . $connection->quoteIdentifier($key);
                }
            }
        } else {
            $this->columns[] = "{$table}.*";
        }
    }

    /**
     * Join with another table to fetch results simultaneously
     *
     * @param string $table The table to join with
     * @param array $cond [ "joined_table_column" => "base_table_column" ]
     * @param array $columns Optional. The column names to fetch from the joined table
     */
    public function link($table, $cond, $columns = null)
    {
        $connection = $this->table->getSchema()->getConnection();
        $conditions = [];
        $srcTable = $this->table->getName();
        foreach ($cond as $linked => $src) {
            $conditions[] = $connection->quoteIdentifier("{$table}.{$linked}") . ' = ' . $connection->quoteIdentifier("{$srcTable}.{$src}") ;
        }
        $this->links[] = [ "table" => $table, "cond" => implode(" AND ", $conditions) ];

        $this->addColumns($table, $columns);

        return $this;
    }

    /**
     * Join with another table to fetch results simultaneously
     *
     * @param string $table The table to join with
     * @param array $cond [ "joined_table_column" => "base_table_column" ]
     * @param array $columns Optional. The column names to fetch from the joined table
     */
    public function filter($cond)
    {
        if (!$this->query) {
            $this->query = $cond;
        } else if (is_array($this->query)) {
            $this->query = array_merge($this->query, $cond);
        } else {
            throw new \Exception("Cannot modify a Query object");
        }

        return $this;
    }

    /**
     * Join with a link table to filter the results
     *
     * @param string $table The table to join with
     * @param string $rightCol The column with the reference to the base base table
     * @param array $cond The filter condition(s)
     * @param array $columns Optional. The column names to fetch from the joined table
     */
    public function filterLinked($table, $rightCol, $cond, $columns = null)
    {
        $connection = $this->table->getSchema()->getConnection();
        $conditions = [];
        $srcTable = $this->table->getName();
        $this->filterLink = [
            "table" => $table,
            "cond" => $connection->quoteIdentifier("{$srcTable}.id") . ' = ' . $connection->quoteIdentifier("{$table}.{$rightCol}")
        ];
        $conditions = [];
        foreach ($cond as $key => $value) {
            $conditions["{$table}.{$key}"] = $value;
        }
        $this->filter($conditions);

        $this->addColumns($table, $columns);

        return $this;
    }

    public function paginate($page, $itemsPerPage)
    {
        // $countSql = "...";
        // $stmt = $stmt = $db->query($sql);
        // $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        // $stmt->closeCursor();
        // $count = $row ? (int)$row["cnt"] : 0;

        // $this->pages = new Pagination($count, $page, $itemsPerPage);
        // $this->offset = $this->pages->firstItemNumber;
        // $this->limit = $itemsPerPage;
    }

    /**
     * Fetches all the rows
     */
    protected function fetch()
    {
        $connection = $this->table->getSchema()->getConnection();
        $query = Query::factory($this->query, $connection);
        $tableName = $connection->quoteIdentifier($this->table->getName());
        $sql = "SELECT {$tableName}.*";

        if (count($this->columns)) {
            $sql .= ", " . implode(", ", $this->columns);
        }

        if ($this->filterLink) {
            $linkTable = $connection->quoteIdentifier($this->filterLink['table']);
            $sql .= " FROM {$linkTable} LEFT JOIN {$tableName} ON {$this->filterLink['cond']}";
        } else {
            $sql .= " FROM {$tableName}";
        }

        foreach ($this->links as $link) {
            $linkTable = $connection->quoteIdentifier($link['table']);
            $sql .= " LEFT JOIN {$linkTable} ON {$link['cond']}";
        }

        $sql .= $query->getSql();

        if ($this->orderBy) {
            $orders = [];
            foreach ($this->orderBy as $key => $value) {
                if (is_numeric($key)) {
                    $orders[] = $connection->quoteIdentifier($value) . " ASC";
                } else {
                    $value = strtoupper($value);
                    $value = in_array($value, [ "ASC", "DESC" ]) ? $value : "ASC";
                    $orders[] = $connection->quoteIdentifier($key) . " {$value}";
                }
            }
            $sql .= " ORDER BY " . implode(", ", $orders);
        }

        if ($this->limitValue && $this->offsetValue) {
            $sql .= " LIMIT {$this->offsetValue}, {$this->limitValue}";
        } else if ($this->limitValue) {
            $sql .= " LIMIT {$this->limitValue}";
        } else if ($this->offsetValue) {
            $sql .= " LIMIT {$this->offsetValue}, 100000000";
        }

        $rows = $this->table->getSchema()->fetchAll($sql, $query->getBindValues());
        $this->rows = [];
        foreach ($rows as $row) {
            $this->rows[] = new Row($this->table, $row, true);
        }
        $this->count = count($this->rows);
    }

    /**
     * Returns all the rows as a native array with Row objects
     *
     * @return array
     */
    public function getAll()
    {
        $this->fetch();
        return $this->rows;
    }

    /**
     * Reduces the result set to an array of options which can be used in an Options field
     *
     * @param string $labelColumn The table column containing the label
     * @param string $valueColumn The table column containing the value
     * @return array [ $value => $label, ... ]
     */
    public function getOptions($labelColumn = "name", $valueColumn = "id")
    {
        $options = [];
        foreach ($this as $row) {
            $options[$row->$valueColumn] = $row->$labelColumn;
        }
        return $options;
    }

    public function getPagination()
    {
        // return new Pagination($this->rowCount, $this->page, $this->limit);
    }

    // Here are the Iterator methods

    public function rewind()
    {
        $this->index = 0;
        if (!$this->rows) $this->fetch();
    }

    public function next()
    {
        $this->index++;
    }

    public function valid()
    {
        return $this->index < $this->count;
    }

    public function current()
    {
        if ($this->index === null) $this->rewind();
        return $this->rows[$this->index];
    }

    public function key()
    {
        return $this->index;
    }
}
