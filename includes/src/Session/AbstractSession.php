<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Session;

use Session\Handler\JTLHandlerInterface;

/**
 * Class AbstractSession
 * @package Session
 */
abstract class AbstractSession
{
    /**
     * @var JTLHandlerInterface
     */
    protected static $handler;

    protected const DEFAULT_SESSION = 'JTLSHOP';

    protected static $sessionName = self::DEFAULT_SESSION;

    /**
     * AbstractSession constructor.
     * @param bool   $start
     * @param string $sessionName
     */
    public function __construct(bool $start = true, string $sessionName = self::DEFAULT_SESSION)
    {
        self::$sessionName = $sessionName;
        \session_name(self::$sessionName);
        $this->initCookie(\Shop::getSettings([\CONF_GLOBAL])['global'], $start);

        self::$handler = (new Storage())->getHandler();
    }

    /**
     * @param array $conf
     * @param bool  $start
     * @return bool
     */
    protected function initCookie(array $conf, bool $start = true): bool
    {
        $cookieDefaults                 = \session_get_cookie_params();
        $lifetime                       = $cookieDefaults['lifetime'] ?? 0;
        $path                           = $cookieDefaults['path'] ?? '';
        $domain                         = $cookieDefaults['domain'] ?? '';
        $secure                         = $cookieDefaults['secure'] ?? false;
        $httpOnly                       = $cookieDefaults['httponly'] ?? false;
        $conf['global_cookie_secure']   = $conf['global_cookie_secure'] ?? 'S';
        $conf['global_cookie_httponly'] = $conf['global_cookie_httponly'] ?? 'S';
        $conf['global_cookie_domain']   = $conf['global_cookie_domain'] ?? '';
        $conf['global_cookie_lifetime'] = $conf['global_cookie_lifetime'] ?? 0;
        if ($conf['global_cookie_secure'] !== 'S') {
            $secure = $conf['global_cookie_secure'] === 'Y';
        }
        if ($conf['global_cookie_httponly'] !== 'S') {
            $httpOnly = $conf['global_cookie_httponly'] === 'Y';
        }
        if ($conf['global_cookie_domain'] !== '') {
            $domain = $this->experimentalMultiLangDomain($conf['global_cookie_domain']);
        }
        if (\is_numeric($conf['global_cookie_lifetime']) && (int)$conf['global_cookie_lifetime'] > 0) {
            $lifetime = (int)$conf['global_cookie_lifetime'];
        }
        if (!empty($conf['global_cookie_path'])) {
            $path = $conf['global_cookie_path'];
        }
        $secure = $secure && ($conf['kaufabwicklung_ssl_nutzen'] === 'P' || \strpos(URL_SHOP, 'https://') === 0);

        if ($start) {
            \session_start([
                'use_cookies'     => '1',
                'cookie_domain'   => $domain,
                'cookie_secure'   => $secure,
                'cookie_lifetime' => $lifetime,
                'cookie_path'     => $path,
                'cookie_httponly' => $httpOnly
            ]);
        }
        \setcookie(
            \session_name(),
            \session_id(),
            ($lifetime === 0) ? 0 : \time() + $lifetime,
            $path,
            $domain,
            $secure,
            $httpOnly
        );

        return true;
    }

    /**
     * @param string $domain
     * @return mixed|string
     */
    private function experimentalMultiLangDomain(string $domain)
    {
        if (!\defined('EXPERIMENTAL_MULTILANG_SHOP')) {
            return $domain;
        }
        foreach (\Sprache::getAllLanguages() as $Sprache) {
            if (!\defined('URL_SHOP_' . \strtoupper($Sprache->cISO))) {
                continue;
            }
            $shopLangURL = \constant('URL_SHOP_' . \strtoupper($Sprache->cISO));
            if (\strpos($shopLangURL, $_SERVER['HTTP_HOST']) !== false
                && \defined('COOKIE_DOMAIN_' . \strtoupper($Sprache->cISO))
            ) {
                return \constant('COOKIE_DOMAIN_' . \strtoupper($Sprache->cISO));
            }
        }

        return $domain;
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
