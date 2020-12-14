<?php

namespace JTL\Session\Handler;

use JTL\Shop;

/**
 * Class Bot
 * @package JTL\Session\Handler
 */
class Bot extends JTLDefault
{
    /**
     * @var string
     */
    protected $sessionID = '';

    /**
     * @var bool
     */
    private $doSave;

    /**
     * @param bool $doSave - when true, session is saved, otherwise it will be discarded immediately
     */
    public function __construct($doSave = false)
    {
        $this->sessionID = \session_id();
        $this->doSave    = $doSave;
    }

    /**
     * @param string $save_path
     * @param string $name
     * @return bool
     */
    public function open($save_path, $name)
    {
        return true;
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
     * @return mixed|string
     * @throws \Exception
     */
    public function read($id)
    {
        $sessionData = '';
        if ($this->doSave === true) {
            $sessionData = (($sessionData = Shop::Container()->getCache()->get($this->sessionID)) !== false)
                ? $sessionData
                : '';
        }

        return $sessionData;
    }

    /**
     * @param string $id
     * @param array  $data
     * @return bool
     */
    public function write($id, $data)
    {
        if ($this->doSave === true) {
            Shop::Container()->getCache()->set($this->sessionID, $sessData, [\CACHING_GROUP_CORE]);
        }

        return true;
    }

    /**
     * @param string $id
     * @return bool
     */
    public function destroy($id)
    {
        return true;
    }

    /**
     * @param int $max_lifetime
     * @return bool
     */
    public function gc($max_lifetime)
    {
        return true;
    }
}
