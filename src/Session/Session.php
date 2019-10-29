<?php

namespace App\Session;

use App\Db\Connection;

class Session
{
    protected $data = [];
    protected $sid;
    protected $conn;

    public function __construct(Connection $conn, $sid = null)
    {
        $this->conn = $conn;

        $needToFetch = false;
        if (!$sid) {
            if (isset($_COOKIE["cst"])) {
                $sid = $_COOKIE["cst"];
            } else {
                $sid = $this->makeNewSession($conn);
                $needToFetch = false;
            }
        }

        if ($needToFetch) {
            $sql = "SELECT * FROM s_session WHERE sid = ?";
            $rows = $conn->fetchAll($sql, [ $sid ]);
            if (count($rows) == 0 || (int)$rows[0]["expires"] < time()) {
                $sid = $this->makeNewSession($conn);
            } else {
                $this->data = json_decode($rows[0]["data"], true);
            }
        }

        setcookie("cst", $sid, time() + 3600, "/");

        $this->sid = $sid;
    }

    public function __destruct()
    {
        $this->conn->schema()->update("s_session", [
            "data" => empty($this->data) ? null : json_encode($this->data),
            "expires" => time() + 3600
        ], [ "sid" => $this->sid ]);
    }

    protected function makeNewSession(Connection $conn)
    {
        $it = 0;
        while ($it < 100) {
            $sid = bin2hex(random_bytes(16));
            $sql = "SELECT * FROM s_session WHERE sid = ?";
            $rows = $conn->fetchAll($sql, [ $sid ]);
            if (count($rows) == 0) break;
            $it++;
        }
        if ($it == 100 && count($rows) > 0) {
            throw new Exception("Could not create a new session");
        }
        $conn->schema()->insert("s_session", [ "sid" => $sid, "expires" => time() + 3600 ]);
        return $sid;
    }

    public function __get($name)
    {
        return isset($this->data[$name]) ? $this->data[$name] : null;
    }

    public function __set($name, $value)
    {
        $this->data[$name] = $value;
    }

    public function __unset($name)
    {
        unset($this->data[$name]);
    }

    public function __isset($name)
    {
        return isset($this->data[$name]);
    }
}
