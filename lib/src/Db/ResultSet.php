<?php

namespace Lib\Db;

use Iterator;

class ResultSet implements Iterator
{
    protected $table;

    protected $query;
    protected $limitValue;
    protected $offsetValue;
    protected $orderBy;
    protected $links = [];
    protected $filterLink = null;
    protected $columns = [];

    protected $pages;
    protected $rows;
    protected $count;
    protected $index;

    public function __construct(Table $table, $query)
    {
        $this->table = $table;
        $this->query = $query;
    }

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
    }

    public function limit($limit)
    {
        $this->limitValue = $limit;
    }

    public function offset($offset)
    {
        $this->offsetValue = $offset;
    }

    public function slice($offset, $limit)
    {
        $this->offsetValue = $offset;
        $this->limitValue = $limit;
    }

    public function link($table, $cond, $columns = null)
    {
        $connection = $this->table->getSchema()->getConnection();
        $conditions = [];
        $srcTable = $this->table->getName();
        foreach ($cond as $linked => $src) {
            $conditions[] = $connection->quoteIdentifier("{$table}.{$linked}") . ' = ' . $connection->quoteIdentifier("{$srcTable}.{$src}") ;
        }
        $this->links[] = [ "table" => $table, "cond" => implode(" AND ", $conditions) ];

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

        return $this;
    }

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

    public function getAll()
    {
        $this->fetch();
        return $this->rows;
    }

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
