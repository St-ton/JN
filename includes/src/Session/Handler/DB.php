<?php

namespace JTL\Session\Handler;

use JTL\DB\DbInterface;
use JTL\DB\ReturnType;
use stdClass;

/**
 * Class DB
 * @package JTL\Session\Handler
 */
class DB extends JTLDefault
{
    /**
     * @var int
     */
    protected $lifeTime;

    /**
     * @var DbInterface
     */
    protected $db;

    /**
     * @var string
     */
    protected $tableName;

    /**
     * SessionHandlerDB constructor.
     * @param DbInterface $db
     * @param string      $tableName
     */
    public function __construct(DbInterface $db, string $tableName = 'tsession')
    {
        $this->db        = $db;
        $this->tableName = $tableName;
    }

    /**
     * @param string $path
     * @param string $name
     * @return bool
     */
    public function open($path, $name)
    {
        $this->lifeTime = \get_cfg_var('session.gc_maxlifetime');

        return $this->db->isConnected();
    }

    /**
     * @return bool
     */
    public function close()
    {
        return true;
    }

    /**
     * @param string $id
     * @return string
     */
    public function read($id)
    {
        $res = $this->db->queryPrepared(
            'SELECT cSessionData FROM ' . $this->tableName . '
                WHERE cSessionId = :id
                AND nSessionExpires > :time',
            [
                'id'   => $id,
                'time' => \time()
            ],
            ReturnType::SINGLE_OBJECT
        );

        return $res->cSessionData ?? '';
    }

    /**
     * @param string $id
     * @param string $data
     * @return bool
     */
    public function write($id, $data)
    {
        // set new session expiration
        $newExp = \time() + $this->lifeTime;
        // is a session with this id already in the database?
        $res = $this->db->select($this->tableName, 'cSessionId', $id);
        // if yes,
        if (!empty($res)) {
            //...update session data
            $update                  = new stdClass();
            $update->nSessionExpires = $newExp;
            $update->cSessionData    = $data;
            // if something happened, return true
            if ($this->db->update($this->tableName, 'cSessionId', $id, $update) > 0) {
                return true;
            }
        } else {
            // if no session was found, create a new row
            $session                  = new stdClass();
            $session->cSessionId      = $id;
            $session->nSessionExpires = $newExp;
            $session->cSessionData    = $data;

            return $this->db->insert($this->tableName, $session) > 0;
        }

        return false;
    }

    /**
     * delete session data
     *
     * @param string $sessID
     * @return bool
     */
    public function destroy($sessID)
    {
        // if session was deleted, return true,
        return $this->db->delete($this->tableName, 'cSessionId', $sessID) > 0;
    }

    /**
     * delete old sessions
     *
     * @param int $max_lifetime
     * @return int
     */
    public function gc($max_lifetime)
    {
        // return affected rows
        return $this->db->query(
            'DELETE FROM ' . $this->tableName . ' WHERE nSessionExpires < ' . \time(),
            ReturnType::AFFECTED_ROWS
        );
    }
}
