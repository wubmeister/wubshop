<?php

namespace App\Db;

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

    public function link($table, $cond)
    {
        $conditions = [];
        $srcTable = $this->table->getName();
        foreach ($cond as $linked => $src) {
            $conditions[] = "{$table}.{$linked} = {$srcTable}.{$src}";
        }
        $this->links[] = [ "table" => $table, "cond" => implode(" AND ", $conditions) ];

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

    public function filterLinked($table, $rightCol, $cond)
    {
        $conditions = [];
        $srcTable = $this->table->getName();
        $this->filterLink = [ "table" => $table, "cond" => "{$srcTable}.id = {$table}.{$rightCol}" ];
        $conditions = [];
        foreach ($cond as $key => $value) {
            $conditions["{$table}.{$key}"] = $value;
        }
        $this->filter($conditions);

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
        $query = Query::factory($this->query);
        $sql = "SELECT *";

        if ($this->filterLink) {
            $sql .= " FROM {$this->filterLink['table']} LEFT JOIN {$this->table->getName()} ON {$this->filterLink['cond']}";
        } else {
            $sql .= " FROM {$this->table->getName()}";
        }

        foreach ($this->links as $link) {
            $sql .= " LEFT JOIN {$link['table']} ON {$link['cond']}";
        }

        $sql .= $query->getSql();

        if ($this->orderBy) {
            $orders = [];
            foreach ($this->orderBy as $key => $value) {
                if (is_numeric($key)) {
                    $orders[] = "{$value} ASC";
                } else {
                    $value = strtoupper($value);
                    $value = in_array($value, [ "ASC", "DESC" ]) ? $value : "ASC";
                    $orders[] = "{$key} {$value}";
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
