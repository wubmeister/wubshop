<?php

namespace App\Db;

use Iterator;
use Exception;

class ResultSet implements Iterator
{
    protected $table;

    protected $query;
    protected $limitValue;
    protected $offsetValue;
    protected $orderBy;

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
        $sql = "SELECT * FROM {$this->table->getName()} " . $query->getSql();

        if ($this->orderBy) {
            $orders = [];
            foreach ($this->orderBy as $key => $value) {
                if (is_numeric($key)) {
                    $orders[] = "{$value} ASC";
                } else {
                    $value = stroupper($value);
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

        $db = $this->table->getSchema()->getConnection();
        $stmt = $db->prepare($sql);
        if (!$stmt) {
            $err = $db->errorInfo();
            throw new Exception("Prepare error: {$err[2]}");
        }
        foreach ($query->getBindValues() as $index => $value) {
            $stmt->bindValue($index + 1, $value);
        }
        if (!$stmt->execute()) {
            $err = $stmt->errorInfo();
            throw new Exception("Execute error: {$err[2]}");
        }
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $this->rows = [];
        foreach ($rows as $row) {
            $this->rows[] = new Row($this->table, $row, true);
        }
        $this->count = count($this->rows);
    }

    public function getAll()
    {
        $this->fetchAll();
        return $this->rows;
    }

    public function getPagination()
    {
        return new Pagination($this->rowCount, $this->page, $this->limit);
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
