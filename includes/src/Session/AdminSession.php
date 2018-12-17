<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Session;

use Session\Handler\SessionHandlerDB;
use Session\Handler\SessionHandlerJTL;

/**
 * Class AdminSession
 * @package Session
 */
class AdminSession
{
    public const DEFAULT_SESSION = 'JTLSHOP';

    public const SESSION_HASH_KEY = 'session.hash';

    /**
     * @var int
     */
    public $lifeTime;

    /**
     * @var \SessionHandlerInterface
     */
    private static $handler;

    /**
     * @var SessionStorage
     */
    private static $storage;

    /**
     * @var AdminSession
     */
    private static $instance;

    /**
     * @return AdminSession
     */
    public static function getInstance(): self
    {
        return self::$instance ?? new self();
    }

    /**
     * AdminSession constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        \session_name('eSIdAdm');
        self::$instance = $this;
        self::$handler  = \ES_SESSIONS === 1
            ? new SessionHandlerDB(\Shop::Container()->getDB(), 'tadminsession')
            : new SessionHandlerJTL();
        self::$storage  = new SessionStorage(self::$handler);

        $_SESSION['jtl_token'] = $_SESSION['jtl_token'] ?? \Shop::Container()->getCryptoService()->randomString(32);
        if (!isset($_SESSION['kSprache'], $_SESSION['cISOSprache'])) {
            $lang                    = \Shop::Container()->getDB()->select('tsprache', 'cShopStandard', 'Y');
            $_SESSION['kSprache']    = isset($lang->kSprache) ? (int)$lang->kSprache : 1;
            $_SESSION['cISOSprache'] = $lang->cISO ?? 'ger';
        }
        \Shop::setLanguage($_SESSION['kSprache'], $_SESSION['cISOSprache']);
        if (isset($_SESSION['Kundengruppe']) && \get_class($_SESSION['Kundengruppe']) === \stdClass::class) {
            $_SESSION['Kundengruppe'] = new \Kundengruppe($_SESSION['Kundengruppe']->kKundengruppe);
        }
        if (isset($_SESSION['Waehrung']) && \get_class($_SESSION['Waehrung']) === \stdClass::class) {
            $_SESSION['Waehrung'] = new \Currency($_SESSION['Waehrung']->kWaehrung);
        }
        if (empty($_SESSION['Sprachen'])) {
            $_SESSION['Sprachen'] = \Sprache::getInstance(false)->gibInstallierteSprachen();
        }
    }

    /**
     * @param string     $key
     * @param null|mixed $default
     * @return mixed
     */
    public static function get($key, $default = null)
    {
        return self::$handler->get($key, $default);
    }

    /**
     * @param string $key
     * @param mixed  $value
     * @return mixed
     */
    public static function set($key, $value)
    {
        return self::$handler->set($key, $value);
    }

    /**
     * @return string
     */
    private static function createHash(): string
    {
        return \function_exists('mhash')
            ? \bin2hex(\mhash(\MHASH_SHA1, \Shop::getApplicationVersion()))
            : \sha1(\Shop::getApplicationVersion());
    }

    /**
     * @return $this
     */
    public function reHash(): self
    {
        self::set(self::SESSION_HASH_KEY, self::createHash());

        return $this;
    }

    /**
     * @return bool
     */
    public function isValid(): bool
    {
        return self::get(self::SESSION_HASH_KEY, '') === self::createHash();
    }
}
