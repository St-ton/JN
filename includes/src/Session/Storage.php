<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Session;

use Session\Handler\Bot;
use Session\Handler\DB;
use Session\Handler\JTLDefault;
use Session\Handler\JTLHandlerInterface;

/**
 * Class Storage
 * @package Session
 */
class Storage
{
    /**
     * handle bot like normal visitor
     */
    public const SAVE_BOT_SESSIONS_NORMAL = 0;

    /**
     * use single session ID for all bot visits
     */
    public const SAVE_BOT_SESSIONS_COMBINED = 1;

    /**
     * save combined bot session to cache
     */
    public const SAVE_BOT_SESSIONS_CACHE = 2;

    /**
     * never save bot sessions
     */
    public const SAVE_BOT_SESSIONS_NEVER = 3;

    /**
     * @var \SessionHandlerInterface
     */
    protected $handler;

    /**
     * Storage constructor.
     */
    public function __construct()
    {
        \session_register_shutdown();
        $this->handler = $this->initHandler();
        $res           = \get_class($this->handler) === JTLDefault::class
            ? true
            : \session_set_save_handler($this->handler, true);
        if ($res !== true) {
            throw new \RuntimeException('Failed to set session handler');
        }
        $this->handler->setSessionData($_SESSION);
    }

    /**
     * @param string $userAgent
     * @return bool
     */
    protected static function getIsCrawler(string $userAgent): bool
    {
        return \preg_match(
            '/Google|ApacheBench|sqlmap|loader.io|bot|Rambler|Yahoo|AbachoBOT|accoona' .
            '|spider|AcioRobot|ASPSeek|CocoCrawler|Dumbot|FAST-WebCrawler|GeonaBot' .
            '|Gigabot|Lycos|alexa|AltaVista|IDBot|Scrubby/',
            $userAgent
        ) > 0;
    }

    /**
     * @return JTLHandlerInterface
     */
    private function initHandler(): JTLHandlerInterface
    {
        $bot = \SAVE_BOT_SESSION !== 0 && isset($_SERVER['HTTP_USER_AGENT'])
            ? self::getIsCrawler($_SERVER['HTTP_USER_AGENT'])
            : false;
        if ($bot === false || \SAVE_BOT_SESSION === self::SAVE_BOT_SESSIONS_NORMAL) {
            $this->handler = \ES_SESSIONS === 1
                ? new DB(\Shop::Container()->getDB())
                : new JTLDefault();
        } else {
            if (\SAVE_BOT_SESSION === self::SAVE_BOT_SESSIONS_COMBINED
                || \SAVE_BOT_SESSION === self::SAVE_BOT_SESSIONS_CACHE
            ) {
                \session_id('jtl-bot');
            }
            if (\SAVE_BOT_SESSION === self::SAVE_BOT_SESSIONS_CACHE
                || \SAVE_BOT_SESSION === self::SAVE_BOT_SESSIONS_NEVER
            ) {
                $save = \SAVE_BOT_SESSION === self::SAVE_BOT_SESSIONS_CACHE
                    && \Shop::Container()->getCache()->isAvailable()
                    && \Shop::Container()->getCache()->isActive();

                $this->handler = new Bot($save);
            } else {
                $this->handler = new JTLDefault();
            }
        }

        return $this->handler;
    }

    /**
     * @return JTLHandlerInterface
     */
    public function getHandler(): JTLHandlerInterface
    {
        return $this->handler;
    }

    /**
     * @param JTLHandlerInterface $handler
     */
    public function setHandler(JTLHandlerInterface $handler): void
    {
        $this->handler = $handler;
    }
}
