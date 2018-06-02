<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Session;

use Session\Handler\SessionHandlerJTL;

/**
 * Class SessionStorage
 */
class SessionStorage
{
    /**
     * @var SessionHandler
     */
    protected $_handler;

    /**
     * @var array
     */
    public $sessionData = [];

    /**
     * @param \SessionHandlerInterface $handler
     * @param array $options
     * @param bool  $start - call session_start()?
     */
    public function __construct(\SessionHandlerInterface $handler, array $options = [], $start = true)
    {
        ini_set('session.use_cookies', 1);
        session_register_shutdown();

        $this->setHandler($handler, $start);
    }

    /**
     * @param SessionHandlerInterface $handler
     * @param bool $start - call session_start()?
     * @return $this
     */
    public function setHandler(\SessionHandlerInterface $handler, $start = true)
    {
        $this->_handler = $handler;
        if (get_class($this->_handler) === SessionHandlerJTL::class) {
            // native php session handler
            $res = true;
        } else {
            $res = session_set_save_handler($this->_handler, true);
        }
        if ($res !== true) {
            throw new \RuntimeException('Failed to start session');
        }
        $conf           = \Shop::getSettings([CONF_GLOBAL])['global'];
        $cookieDefaults = session_get_cookie_params();
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
            //EXPERIMENTAL_MULTILANG_SHOP
            if (defined('EXPERIMENTAL_MULTILANG_SHOP')) {
                $languages = gibAlleSprachen();
                foreach ($languages as $Sprache) {
                    if (defined('URL_SHOP_' . strtoupper($Sprache->cISO))) {
                        $shopLangURL = constant('URL_SHOP_' . strtoupper($Sprache->cISO));
                        if (strpos($shopLangURL, $_SERVER['HTTP_HOST']) !== false) {
                            if (defined('COOKIE_DOMAIN_' . strtoupper($Sprache->cISO))) {
                                $domain = constant('COOKIE_DOMAIN_' . strtoupper($Sprache->cISO));
                                break;
                            }
                        }
                    }
                }
            }
            //EXPERIMENTAL_MULTILANG_SHOP END
        }
        if (isset($conf['global_cookie_lifetime'])
            && is_numeric($conf['global_cookie_lifetime'])
            && (int)$conf['global_cookie_lifetime'] > 0
        ) {
            $set      = true;
            $lifetime = (int)$conf['global_cookie_lifetime'];
        }
        if (!empty($conf['global_cookie_path'])) {
            $set  = true;
            $path = $conf['global_cookie_path'];
        }
        // only set secure if SSL is enabled
        if ($set === true) {
            $secure = $secure
                && ($conf['kaufabwicklung_ssl_nutzen'] === 'P' || strpos(URL_SHOP, 'https://') === 0);
        }
        if ($set === true) {
            session_set_cookie_params($lifetime, $path, $domain, $secure, $httpOnly);
        }
        if ($start) {
            session_start();
        }
        if ($set === true) {
            $exp = ($lifetime === 0) ? 0 : time() + $lifetime;
            setcookie(session_name(), session_id(), $exp, $path, $domain, $secure, $httpOnly);
        }

        $this->_handler->sessionData = &$_SESSION;

        return $this;
    }

    /**
     * @return SessionHandler
     */
    public function getHandler()
    {
        return $this->_handler;
    }
}
