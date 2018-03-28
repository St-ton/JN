<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class AdminSession
 */
class AdminSession
{
    /**
     * @var int
     */
    public $lifeTime;

    /**
     * @var AdminSession
     */
    private static $_instance;

    /**
     * @return AdminSession
     */
    public static function getInstance()
    {
        return self::$_instance ?? new self();
    }

    /**
     *
     */
    public function __construct()
    {
        self::$_instance = $this;
        session_name('eSIdAdm');

        if (ES_SESSIONS === 1) {
            // Sessions in DB speichern
            session_set_save_handler(
                [&$this, 'open'],
                [&$this, 'close'],
                [&$this, 'read'],
                [&$this, 'write'],
                [&$this, 'destroy'],
                [&$this, 'gc']
            );
            register_shutdown_function('session_write_close');
        }

        $conf           = Shop::getSettings([CONF_GLOBAL]);
        $cookieDefaults = session_get_cookie_params();
        $set            = false;
        $lifetime       = $cookieDefaults['lifetime'] ?? 0;
        $path           = $cookieDefaults['path'] ?? '';
        $domain         = $cookieDefaults['domain'] ?? '';
        $secure         = $cookieDefaults['secure'] ?? false;
        $httpOnly       = $cookieDefaults['httponly'] ?? false;
        if (isset($conf['global']['global_cookie_secure']) && $conf['global']['global_cookie_secure'] !== 'S') {
            $set    = true;
            $secure = $conf['global']['global_cookie_secure'] === 'Y';
        }
        if (isset($conf['global']['global_cookie_httponly']) && $conf['global']['global_cookie_httponly'] !== 'S') {
            $set      = true;
            $httpOnly = $conf['global']['global_cookie_httponly'] === 'Y';
        }
        if (isset($conf['global']['global_cookie_domain']) && $conf['global']['global_cookie_domain'] !== '') {
            $set    = true;
            $domain = $conf['global']['global_cookie_domain'];
        }
        if (isset($conf['global']['global_cookie_lifetime']) &&
            is_numeric($conf['global']['global_cookie_lifetime']) &&
            (int)$conf['global']['global_cookie_lifetime'] > 0
        ) {
            $set      = true;
            $lifetime = (int)$conf['global']['global_cookie_lifetime'];
        }
        if (!empty($conf['global']['global_cookie_path'])) {
            $set  = true;
            $path = $conf['global']['global_cookie_path'];
        }
        // Ticket: #1571
        if ($set === true && isset($conf['global']['kaufabwicklung_ssl_nutzen'])) {
            $secure   = $secure === true && $conf['global']['kaufabwicklung_ssl_nutzen'] !== 'N';
            $httpOnly = $httpOnly === true && $conf['global']['kaufabwicklung_ssl_nutzen'] === 'N';
        }
        if ($set === true) {
            session_set_cookie_params($lifetime, $path, $domain, $secure, $httpOnly);
        }
        session_start();
        if ($set === true) {
            $exp = ($lifetime === 0) ? 0 : time() + $lifetime;
            setcookie(session_name(), session_id(), $exp, $path, $domain, $secure, $httpOnly);
        }
        if (!isset($_SESSION['jtl_token'])) {
            $_SESSION['jtl_token'] = generateCSRFToken();
        }
        if (!isset($_SESSION['kSprache'])) {
            $lang                 = Shop::Container()->getDB()->select('tsprache', 'cISO', 'ger');
            $_SESSION['kSprache'] = isset($lang->kSprache) ? (int)$lang->kSprache : 1;
        }
        if (isset($_SESSION['Kundengruppe']) && get_class($_SESSION['Kundengruppe']) === 'stdClass') {
            $_SESSION['Kundengruppe'] = new Kundengruppe($_SESSION['Kundengruppe']->kKundengruppe);
        }
        if (isset($_SESSION['Waehrung']) && get_class($_SESSION['Waehrung']) === 'stdClass') {
            $_SESSION['Waehrung'] = new Currency($_SESSION['Waehrung']->kWaehrung);
        }
    }

    /**
     * @param string $savePath
     * @param string $sessName
     * @return mixed
     */
    public function open($savePath, $sessName)
    {
        // get session-lifetime
        $this->lifeTime = get_cfg_var('session.gc_maxlifetime');

        // return success
        return Shop::Container()->getDB()->isConnected();
    }

    /**
     * @return bool
     */
    public function close()
    {
        // mach nichts
        return true;
    }

    /**
     * fetch session-data
     *
     * @param string $sessID
     * @return string
     */
    public function read($sessID)
    {
        $res = Shop::Container()->getDB()->executeQueryPrepared('
            SELECT cSessionData FROM tadminsession
                WHERE cSessionId = :sid
                AND nSessionExpires > :time',
            [
                'sid' => $sessID,
                'time' => time()
            ],
            NiceDB::RET_SINGLE_OBJECT
        );

        return $res->cSessionData ?? '';
    }

    /**
     * @param string $sessID
     * @param string $sessData
     * @return bool
     */
    public function write($sessID, $sessData)
    {
        // new session-expire-time
        $newExp = time() + $this->lifeTime;
        // is a session with this id in the database?
        $res = Shop::Container()->getDB()->select('tadminsession', 'cSessionId', $sessID);
        // if yes,
        if (isset($res->cSessionId)) {
            // ...update session-data
            $_upd                  = new stdClass();
            $_upd->nSessionExpires = $newExp;
            $_upd->cSessionData    = $sessData;

            return Shop::Container()->getDB()->update('tadminsession', 'cSessionId', $sessID, $_upd) >= 0;
        }
        // if no session-data was found, create a new row
        $_ins                  = new stdClass();
        $_ins->cSessionId      = $sessID;
        $_ins->nSessionExpires = $newExp;
        $_ins->cSessionData    = $sessData;

        return Shop::Container()->getDB()->insert('tadminsession', $_ins) > 0;
    }

    /**
     * delete session-data
     *
     * @param string $sessID
     * @return bool
     */
    public function destroy($sessID)
    {
        return Shop::Container()->getDB()->delete('tadminsession', 'cSessionId', $sessID) > 0;
    }

    /**
     * @param int $sessMaxLifeTime
     * @return int
     */
    public function gc($sessMaxLifeTime)
    {
        return Shop::Container()->getDB()->queryPrepared(
            'DELETE FROM tadminsession
                WHERE nSessionExpires < :time',
            ['time' => time()],
            NiceDB::RET_AFFECTED_ROWS
        );
    }
}
