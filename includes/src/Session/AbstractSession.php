<?php

namespace JTL\Session;

use JTL\Session\Handler\JTLHandlerInterface;
use JTL\Shop;
use function Functional\last;

/**
 * Class AbstractSession
 * @package JTL\Session
 */
abstract class AbstractSession
{
    /**
     * @var JTLHandlerInterface
     */
    protected static $handler;

    /**
     * @var string
     */
    protected static $sessionName;

    /**
     * AbstractSession constructor.
     * @param bool   $start
     * @param string $sessionName
     */
    public function __construct(bool $start, string $sessionName)
    {
        self::$sessionName = $sessionName;
        \session_name(self::$sessionName);
        self::$handler = (new Storage())->getHandler();
        $this->initCookie(Shop::getSettings([\CONF_GLOBAL])['global'], $start);
        self::$handler->setSessionData($_SESSION);
    }

    /**
     * @param array $conf
     * @param bool  $start
     * @return bool
     */
    protected function initCookie(array $conf, bool $start = true): bool
    {
        $cookieConfig = new CookieConfig($conf);
        if ($start) {
            $this->start($cookieConfig);
        }
        $this->setCookie($cookieConfig);
        $this->clearDuplicateCookieHeaders();

        return true;
    }

    /**
     * @param CookieConfig $cookieConfig
     * @return bool
     */
    private function setCookie(CookieConfig $cookieConfig): bool
    {
        if (\PHP_VERSION_ID > 70300) {
            $config = [
                'expires'  => ($cookieConfig->getLifetime() === 0) ? 0 : \time() + $cookieConfig->getLifetime(),
                'path'     => $cookieConfig->getPath(),
                'domain'   => $cookieConfig->getDomain(),
                'secure'   => $cookieConfig->isSecure(),
                'httponly' => $cookieConfig->isHttpOnly(),
            ];
            if (\strlen($cookieConfig->getSameSite()) > 2) {
                $config['samesite'] = $cookieConfig->getSameSite();
            }

            return \setcookie(
                \session_name(),
                \session_id(),
                $config
            );
        }
        return \setcookie(
            \session_name(),
            \session_id(),
            ($cookieConfig->getLifetime() === 0) ? 0 : \time() + $cookieConfig->getLifetime(),
            $cookieConfig->getPath(),
            $cookieConfig->getDomain(),
            $cookieConfig->isSecure(),
            $cookieConfig->isHttpOnly()
        );
    }

    /**
     * @param CookieConfig $cookieConfig
     * @return bool
     */
    private function start(CookieConfig $cookieConfig): bool
    {
        return \session_start($cookieConfig->getSessionConfigArray());
    }

    /**
     * session_start() and setcookie both create Set-Cookie headers
     */
    private function clearDuplicateCookieHeaders(): void
    {
        if (\headers_sent()) {
            return;
        }
        $cookies = [];
        foreach (\headers_list() as $header) {
            // Identify cookie headers
            if (\strpos($header, 'Set-Cookie:') === 0) {
                $cookies[] = $header;
            }
        }
        if (\count($cookies) > 1) {
            \header_remove('Set-Cookie');
            \header(last($cookies), false);
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
}
