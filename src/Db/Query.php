<?php

namespace App\Db;

class Query
{
    public static function factory($query)
    {
        if ($query instanceof Query) return $query;
        return new Query();
    }

    public function getSql($clause = "WHERE")
    {
        return "";
    }

    public function getBindValues()
    {
        return [];
    }
}
