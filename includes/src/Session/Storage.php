<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Session;

use Session\Handler\JTLDefault;
use Session\Handler\JTLHandlerInterface;

/**
 * Class Storage
 * @package Session
 */
class Storage
{
    /**
     * @var \SessionHandlerInterface
     */
    protected $handler;

    /**
     * @var array
     */
    public $sessionData = [];

    /**
     * SessionStorage constructor.
     * @param JTLHandlerInterface $handler
     */
    public function __construct(JTLHandlerInterface $handler)
    {
        \session_register_shutdown();
        $this->setHandler($handler);
    }

    /**
     * @param JTLHandlerInterface $handler
     * @return Storage
     */
    public function setHandler(JTLHandlerInterface $handler): self
    {
        $this->handler = $handler;
        $res           = \get_class($this->handler) === JTLDefault::class
            ? true
            : \session_set_save_handler($this->handler, true);
        if ($res !== true) {
            throw new \RuntimeException('Failed to set session handler');
        }
        $this->handler->setSessionData($_SESSION);

        return $this;
    }

    /**
     * @return \SessionHandlerInterface
     */
    public function getHandler(): \SessionHandlerInterface
    {
        return $this->handler;
    }
}
