<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Session\Handler;

use DB\DbInterface;
use DB\ReturnType;

/**
 * Class SessionHandlerDB
 */
class SessionHandlerDB extends SessionHandlerJTL implements \SessionHandlerInterface
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
     * @param string $savePath
     * @param string $sessName
     * @return bool
     */
    public function open($savePath, $sessName)
    {
        $this->lifeTime = get_cfg_var('session.gc_maxlifetime');

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
     * @param string $sessID
     * @return string
     */
    public function read($sessID)
    {
        $res = $this->db->queryPrepared(
            'SELECT cSessionData FROM ' . $this->tableName . '
                WHERE cSessionId = :id
                AND nSessionExpires > :time',
            [
                'id'   => $sessID,
                'time' => time()
            ],
            ReturnType::SINGLE_OBJECT
        );

        return $res->cSessionData ?? '';
    }

    /**
     * @param string $sessID
     * @param string $sessData
     * @return bool
     */
    public function write($sessID, $sessData)
    {
        // set new session expiration
        $newExp = time() + $this->lifeTime;
        // is a session with this id already in the database?
        $res = $this->db->select($this->tableName, 'cSessionId', $sessID);
        // if yes,
        if (!empty($res)) {
            //...update session data
            $update                  = new \stdClass();
            $update->nSessionExpires = $newExp;
            $update->cSessionData    = $sessData;
            // if something happened, return true
            if ($this->db->update($this->tableName, 'cSessionId', $sessID, $update) > 0) {
                return true;
            }
        } else {
            // if no session was found, create a new row
            $session                  = new \stdClass();
            $session->cSessionId      = $sessID;
            $session->nSessionExpires = $newExp;
            $session->cSessionData    = $sessData;

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
     * @param int $sessMaxLifeTime
     * @return int
     */
    public function gc($sessMaxLifeTime)
    {
        // return affected rows
        return $this->db->query(
            'DELETE FROM ' . $this->tableName . ' WHERE nSessionExpires < ' . time(),
            ReturnType::AFFECTED_ROWS
        );
    }
}
