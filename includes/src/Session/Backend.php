<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Session;

/**
 * Class Backend
 * @package Session
 */
class Backend extends AbstractSession
{
    private const DEFAULT_SESSION = 'eSIdAdm';

    private const SESSION_HASH_KEY = 'session.hash';

    /**
     * @var Backend
     */
    protected static $instance;

    /**
     * @return Backend
     * @throws \Exception
     */
    public static function getInstance(): self
    {
        return self::$instance ?? new self();
    }

    /**
     * Backend constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        parent::__construct(true, self::DEFAULT_SESSION);
        self::$instance        = $this;
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
            $_SESSION['Sprachen'] = \Sprache::getInstance()->gibInstallierteSprachen();
        }
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
