<?php declare(strict_types=1);

namespace JTL\Session\Handler;

use JTL\Shop;

/**
 * Class Bot
 * @package JTL\Session\Handler
 */
class Bot extends JTLDefault
{
    /**
     * @var string|bool
     */
    protected string|bool $sessionID;

    /**
     * @param bool $doSave - when true, session is saved, otherwise it will be discarded immediately
     */
    public function __construct(private bool $doSave = false)
    {
        $this->sessionID = \session_id();
    }

    /**
     * @inheritDoc
     */
    public function open($path, $name): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function close(): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function read($id): string|false
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
     * @inheritDoc
     */
    public function write($id, $data): bool
    {
        if ($this->doSave === true) {
            Shop::Container()->getCache()->set($this->sessionID, $data, [\CACHING_GROUP_CORE]);
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function destroy($id): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function gc($max_lifetime): int|false
    {
        return 0;
    }
}
