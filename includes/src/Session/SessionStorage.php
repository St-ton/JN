<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Session;

use Session\Handler\SessionHandlerJTL;

/**
 * Class SessionStorage
 * @package Session
 */
class SessionStorage
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
     * @param \SessionHandlerInterface $handler
     * @param bool                     $start
     */
    public function __construct(\SessionHandlerInterface $handler, bool $start = true)
    {
        \session_register_shutdown();
        $this->setHandler($handler, $start);
    }

    /**
     * @param \SessionHandlerInterface $handler
     * @param bool                     $start
     * @return SessionStorage
     */
    public function setHandler(\SessionHandlerInterface $handler, bool $start = true): self
    {
        $this->handler = $handler;
        $res = \get_class($this->handler) === SessionHandlerJTL::class
            ? true
            : session_set_save_handler($this->handler, true);
        if ($res !== true) {
            throw new \RuntimeException('Failed to set session handler');
        }
        $conf           = \Shop::getSettings([\CONF_GLOBAL])['global'];
        $cookieDefaults = \session_get_cookie_params();
        $lifetime       = $cookieDefaults['lifetime'] ?? 0;
        $path           = $cookieDefaults['path'] ?? '';
        $domain         = $cookieDefaults['domain'] ?? '';
        $secure         = $cookieDefaults['secure'] ?? false;
        $httpOnly       = $cookieDefaults['httponly'] ?? false;
        if (isset($conf['global_cookie_secure']) && $conf['global_cookie_secure'] !== 'S') {
            $secure = $conf['global_cookie_secure'] === 'Y';
        }
        if (isset($conf['global_cookie_httponly']) && $conf['global_cookie_httponly'] !== 'S') {
            $httpOnly = $conf['global_cookie_httponly'] === 'Y';
        }
        if (isset($conf['global_cookie_domain']) && $conf['global_cookie_domain'] !== '') {
            $domain = $conf['global_cookie_domain'];
            //EXPERIMENTAL_MULTILANG_SHOP
            if (\defined('EXPERIMENTAL_MULTILANG_SHOP')) {
                $languages = \Sprache::getAllLanguages();
                foreach ($languages as $Sprache) {
                    if (\defined('URL_SHOP_' . \strtoupper($Sprache->cISO))) {
                        $shopLangURL = \constant('URL_SHOP_' . \strtoupper($Sprache->cISO));
                        if (\strpos($shopLangURL, $_SERVER['HTTP_HOST']) !== false
                            && \defined('COOKIE_DOMAIN_' . \strtoupper($Sprache->cISO))
                        ) {
                            $domain = \constant('COOKIE_DOMAIN_' . \strtoupper($Sprache->cISO));
                            break;
                        }
                    }
                }
            }
            //EXPERIMENTAL_MULTILANG_SHOP END
        }
        if (isset($conf['global_cookie_lifetime'])
            && \is_numeric($conf['global_cookie_lifetime'])
            && (int)$conf['global_cookie_lifetime'] > 0
        ) {
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

        $this->handler->sessionData = &$_SESSION;

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
