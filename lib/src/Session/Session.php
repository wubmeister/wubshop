<?php

namespace Lib\Session;

use Lib\Db\Schema;

/**
 * Class to store session data
 *
 * @author Wubbo Bos
 */
class Session
{
    /** @var array $data The session data */
    protected $data = [];

    /** @var string $sid The session ID */
    protected $sid;

    /** @var Lib\Db\Schema $schema The schema to save the session in */
    protected $schema;

    /**
     * Constructor
     *
     * @param Lib\Db\Schema $schema The schema to save the session in
     * @param string $sid Pass a session ID to restore a specific session instead of looking up the session ID in the cookie
     */
    public function __construct(Schema $schema, $sid = null)
    {
        $this->schema = $schema;

        $needToFetch = false;
        if (!$sid) {
            if (isset($_COOKIE["cst"])) {
                $sid = $_COOKIE["cst"];
            } else {
                $sid = $this->makeNewSession();
                $needToFetch = false;
            }
        }

        if ($needToFetch) {
            $sql = "SELECT * FROM s_session WHERE sid = ?";
            $rows = $schema->fetchAll($sql, [ $sid ]);
            if (count($rows) == 0 || (int)$rows[0]["expires"] < time()) {
                $sid = $this->makeNewSession();
            } else {
                $this->data = json_decode($rows[0]["data"], true);
            }
        }

        setcookie("cst", $sid, time() + 3600, "/");

        $this->sid = $sid;
    }

    /**
     * Upon destructing the session object, save the current state to the database
     */
    public function __destruct()
    {
        $this->schema->update("s_session", [
            "data" => empty($this->data) ? null : json_encode($this->data),
            "expires" => time() + 3600
        ], [ "sid" => $this->sid ]);
    }

    /**
     * Creates a new session with a unique session ID
     *
     * @return string The session ID
     */
    protected function makeNewSession()
    {
        $it = 0;
        while ($it < 100) {
            $sid = bin2hex(random_bytes(16));
            $sql = "SELECT * FROM s_session WHERE sid = ?";
            $rows = $this->schema->fetchAll($sql, [ $sid ]);
            if (count($rows) == 0) break;
            $it++;
        }
        if ($it == 100 && count($rows) > 0) {
            throw new Exception("Could not create a new session");
        }
        $this->schema->insert("s_session", [ "sid" => $sid, "expires" => time() + 3600 ]);
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
