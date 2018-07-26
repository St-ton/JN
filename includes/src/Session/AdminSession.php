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
 */
class AdminSession
{
    const DEFAULT_SESSION = 'JTLSHOP';

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
    private static $_instance;

    /**
     * @return AdminSession
     */
    public static function getInstance(): self
    {
        return self::$_instance ?? new self();
    }

    /**
     *
     */
    public function __construct()
    {
        self::$_instance = $this;
        \session_name('eSIdAdm');

        self::$handler = ES_SESSIONS === 1
            ? new SessionHandlerDB(\Shop::Container()->getDB(), 'tadminsession')
            : new SessionHandlerJTL();
        $conf           = \Shop::getSettings([CONF_GLOBAL])['global'];
        $cookieDefaults = \session_get_cookie_params();
        $set            = false;
        $lifetime       = $cookieDefaults['lifetime'] ?? 0;
        $path           = $cookieDefaults['path'] ?? '';
        $domain         = $cookieDefaults['domain'] ?? '';
        $secure         = $cookieDefaults['secure'] ?? false;
        $httpOnly       = $cookieDefaults['httponly'] ?? false;
        if (isset($conf['global_cookie_secure']) && $conf['global_cookie_secure'] !== 'S') {
            $set    = true;
            $secure = $conf['global_cookie_secure'] === 'Y';
        }
        if (isset($conf['global_cookie_httponly']) && $conf['global_cookie_httponly'] !== 'S') {
            $set      = true;
            $httpOnly = $conf['global_cookie_httponly'] === 'Y';
        }
        if (isset($conf['global_cookie_domain']) && $conf['global_cookie_domain'] !== '') {
            $set    = true;
            $domain = $conf['global_cookie_domain'];
        }
        if (isset($conf['global_cookie_lifetime']) 
            && \is_numeric($conf['global_cookie_lifetime'])
            && (int)$conf['global_cookie_lifetime'] > 0
        ) {
            $set      = true;
            $lifetime = (int)$conf['global_cookie_lifetime'];
        }
        if (!empty($conf['global_cookie_path'])) {
            $set  = true;
            $path = $conf['global_cookie_path'];
        }
        // Ticket: #1571
        if ($set === true && isset($conf['kaufabwicklung_ssl_nutzen'])) {
            $secure   = $secure === true && $conf['kaufabwicklung_ssl_nutzen'] !== 'N';
            $httpOnly = $httpOnly === true && $conf['kaufabwicklung_ssl_nutzen'] === 'N';
        }
        if ($set === true) {
            \session_set_cookie_params($lifetime, $path, $domain, $secure, $httpOnly);
        }
        self::$storage = new SessionStorage(self::$handler, []);

        if ($set === true) {
            $exp = ($lifetime === 0) ? 0 : \time() + $lifetime;
            \setcookie(\session_name(), \session_id(), $exp, $path, $domain, $secure, $httpOnly);
        }
        if (!isset($_SESSION['jtl_token'])) {
            $_SESSION['jtl_token'] = \Shop::Container()->getCryptoService()->randomString(32);
        }
        if (!isset($_SESSION['kSprache'])) {
            $lang                    = \Shop::Container()->getDB()->select('tsprache', 'cShopStandard', 'Y');
            $_SESSION['kSprache']    = isset($lang->kSprache) ? (int)$lang->kSprache : 1;
            $_SESSION['cISOSprache'] = $lang->cISO ?? 'ger';
        }
        if (!isset($_SESSION['cISOSprache'])) {
            // after shop updates this may not be set
            $lang                    = \Shop::Container()->getDB()->select('tsprache', 'cShopStandard', 'Y');
            $_SESSION['cISOSprache'] = $lang->cISO ?? 'ger';
        }
        \Shop::setLanguage($_SESSION['kSprache'], $_SESSION['cISOSprache']);
        if (isset($_SESSION['Kundengruppe']) && \get_class($_SESSION['Kundengruppe']) === 'stdClass') {
            $_SESSION['Kundengruppe'] = new \Kundengruppe($_SESSION['Kundengruppe']->kKundengruppe);
        }
        if (isset($_SESSION['Waehrung']) && \get_class($_SESSION['Waehrung']) === 'stdClass') {
            $_SESSION['Waehrung'] = new \Currency($_SESSION['Waehrung']->kWaehrung);
        }
        if (empty($_SESSION['Sprachen'])) {
            $_SESSION['Sprachen'] = \Sprache::getInstance(false)->gibInstallierteSprachen();
        }
        unset($_SESSION['oKategorie_arr_new']);
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
}
