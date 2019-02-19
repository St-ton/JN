<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

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
     * @param string $savePath
     * @param string $sessName
     * @return bool
     */
    public function open($savePath, $sessName)
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
     * @param string $sessID
     * @return mixed|string
     * @throws \Exception
     */
    public function read($sessID)
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
     * @param string $sessID
     * @param array $sessData
     * @return bool
     */
    public function write($sessID, $sessData)
    {
        if ($this->doSave === true) {
            Shop::Container()->getCache()->set($this->sessionID, $sessData, [\CACHING_GROUP_CORE]);
        }

        return true;
    }

    /**
     * @param string $sessID
     * @return bool
     */
    public function destroy($sessID)
    {
        return true;
    }

    /**
     * @param int $sessMaxLifeTime
     * @return bool
     */
    public function gc($sessMaxLifeTime)
    {
        return true;
    }
}
