<?php

namespace JTL\Session;

use JTL\Catalog\Currency;
use JTL\Customer\CustomerGroup;
use JTL\Helpers\Text;
use JTL\Language\LanguageHelper;
use JTL\Language\LanguageModel;
use JTL\Shop;
use stdClass;
use function Functional\first;
use function Functional\map;

/**
 * Class Backend
 * @package JTL\Session
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
        $_SESSION['jtl_token'] = $_SESSION['jtl_token'] ?? Shop::Container()->getCryptoService()->randomString(32);
        if (!isset($_SESSION['kSprache'], $_SESSION['cISOSprache'])) {
            $languages    = LanguageHelper::getInstance()->gibInstallierteSprachen();
            $lang         = first($languages, static function (LanguageModel $e) {
                return $e->isShopDefault() === true;
            });
            $allowed      = map($languages, static function (LanguageModel $e) {
                return $e->getIso639();
            });
            $adminLocales = Shop::Container()->getGetText()->getAdminLanguages();
            if ($lang !== null) {
                $default = Text::convertISO6392ISO($this->getBrowserLanguage($allowed, $lang->cISO));
                foreach ($languages as $language) {
                    if ($language->cISO === $default) {
                        foreach ($adminLocales as $tag => $adminLocale) {
                            if ($adminLocale === $language->getNameDE() || $adminLocale === $language->getNameEN()) {
                                $_SESSION['kSprache']    = (int)$language->kSprache;
                                $_SESSION['cISOSprache'] = $language->cISO;
                                Shop::Container()->getGetText()->setLanguage($tag);
                                break 2;
                            }
                        }
                    }
                }
            }
            $_SESSION['kSprache']    = $_SESSION['kSprache'] ?? 1;
            $_SESSION['cISOSprache'] = $_SESSION['cISOSprache'] ?? 'ger';
        }
        Shop::setLanguage($_SESSION['kSprache'], $_SESSION['cISOSprache']);
        if (isset($_SESSION['Kundengruppe']) && \get_class($_SESSION['Kundengruppe']) === stdClass::class) {
            $_SESSION['Kundengruppe'] = new CustomerGroup($_SESSION['Kundengruppe']->kKundengruppe);
        }
        if (isset($_SESSION['Waehrung']) && \get_class($_SESSION['Waehrung']) === stdClass::class) {
            $_SESSION['Waehrung'] = new Currency($_SESSION['Waehrung']->kWaehrung);
        }
        if (empty($_SESSION['Sprachen']) || \get_class($_SESSION['Sprachen'][0]) === stdClass::class) {
            $_SESSION['Sprachen'] = LanguageHelper::getInstance()->gibInstallierteSprachen();
        }
    }

    /**
     * @return string
     */
    private static function createHash(): string
    {
        return \function_exists('mhash')
            ? \bin2hex(\mhash(\MHASH_SHA1, Shop::getApplicationVersion()))
            : \sha1(Shop::getApplicationVersion());
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
