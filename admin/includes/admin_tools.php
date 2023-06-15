<?php declare(strict_types=1);

use JTL\Backend\Notification;
use JTL\CSV\Import;
use JTL\Filter\SearchResults;
use JTL\Helpers\Date;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Router\Controller\Backend\AbstractBackendController;
use JTL\Session\Frontend;
use JTL\Shop;
use JTL\Shopsetting;
use JTL\Smarty\JTLSmarty;

/**
 * @param array $settingsIDs
 * @param array $post
 * @param array $tags
 * @param bool $byName
 * @return string
 * @deprecated since 5.2.0
 */
function saveAdminSettings(
    array $settingsIDs,
    array $post,
    array $tags = [CACHING_GROUP_OPTION],
    bool $byName = false
): string {
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return __('errorConfigSave');
}

/**
 * @param stdClass $setting
 * @return bool
 * @deprecated since 5.2.0
 */
function validateSetting(stdClass $setting): bool
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return false;
}

/**
 * @param int      $min
 * @param int      $max
 * @param stdClass $setting
 * @return bool
 * @deprecated since 5.2.0
 */
function validateNumberRange(int $min, int $max, stdClass $setting): bool
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return false;
}

/**
 * Holt alle vorhandenen Kampagnen
 * Wenn $getInternal false ist, werden keine Interne Shop Kampagnen geholt
 * Wenn $activeOnly true ist, werden nur Aktive Kampagnen geholt
 *
 * @param bool $getInternal
 * @param bool $activeOnly
 * @return array
 * @deprecated since 5.2.0
 */
function holeAlleKampagnen(bool $getInternal = false, bool $activeOnly = true): array
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use JTL\Router\Controller\Backend::getCampaigns() instead.',
        E_USER_DEPRECATED
    );
    return AbstractBackendController::getCampaigns($getInternal, $activeOnly, Shop::Container()->getDB());
}

/**
 * @deprecated since 5.2.0
 */
function setzeSprache(): void
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    if (Form::validateToken() && Request::verifyGPCDataInt('sprachwechsel') === 1) {
        // Wähle explizit gesetzte Sprache als aktuelle Sprache
        $language = Shop::Container()->getDB()->select('tsprache', 'kSprache', Request::postInt('kSprache'));
        if ((int)$language->kSprache > 0) {
            $_SESSION['editLanguageID']   = (int)$language->kSprache;
            $_SESSION['editLanguageCode'] = $language->cISO;
        }
    }

    if (!isset($_SESSION['editLanguageID'])) {
        // Wähle Standardsprache als aktuelle Sprache
        $language = Shop::Container()->getDB()->select('tsprache', 'cShopStandard', 'Y');
        if ((int)$language->kSprache > 0) {
            $_SESSION['editLanguageID']   = (int)$language->kSprache;
            $_SESSION['editLanguageCode'] = $language->cISO;
        }
    }
    if (isset($_SESSION['editLanguageID']) && empty($_SESSION['editLanguageCode'])) {
        // Fehlendes cISO ergänzen
        $language = Shop::Container()->getDB()->select('tsprache', 'kSprache', (int)$_SESSION['editLanguageID']);
        if ((int)$language->kSprache > 0) {
            $_SESSION['editLanguageCode'] = $language->cISO;
        }
    }
}

/**
 * @param int $month
 * @param int $year
 * @return false|int
 * @deprecated since 5.2.0
 */
function firstDayOfMonth(int $month = -1, int $year = -1)
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use JTL\Helpers\Date::getFirstDayOfMonth() instead.',
        E_USER_DEPRECATED
    );
    return Date::getFirstDayOfMonth($month, $year);
}

/**
 * @param int $month
 * @param int $year
 * @return false|int
 * @deprecated since 5.2.0
 */
function lastDayOfMonth(int $month = -1, int $year = -1)
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use JTL\Helpers\Date::getLastDayOfMonth() instead.',
        E_USER_DEPRECATED
    );
    return Date::getLastDayOfMonth($month, $year);
}

/**
 * @param string $dateString
 * @return array
 * @deprecated since 5.2.0
 */
function ermittleDatumWoche(string $dateString): array
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use JTL\Helpers\Date::getWeekStartAndEnd() instead.',
        E_USER_DEPRECATED
    );

    return Date::getWeekStartAndEnd($dateString);
}

/**
 * @param int   $configSectionID
 * @param array $post
 * @param array $tags
 * @return string
 * @deprecated since 5.2.0
 */
function saveAdminSectionSettings(int $configSectionID, array $post, array $tags = [CACHING_GROUP_OPTION]): string
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return __('errorConfigSave');
}

/**
 * @param int|array $configSectionID
 * @param bool $byName
 * @return stdClass[]
 * @deprecated since 5.2.0
 */
function getAdminSectionSettings($configSectionID, bool $byName = false): array
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return [];
}

/**
 * @param mixed  $listBoxes
 * @param string $valueName
 * @param int    $configSectionID
 * @deprecated since 5.2.0
 */
function bearbeiteListBox($listBoxes, string $valueName, int $configSectionID): void
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
}

/**
 * @param bool $date
 * @return int|string
 * @deprecated since 5.2.0
 */
function getJTLVersionDB(bool $date = false)
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return 0;
}

/**
 * @param string $size
 * @return mixed
 * @deprecated since 5.2.0
 */
function getMaxFileSize($size)
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return match (mb_substr($size, -1)) {
        'M', 'm' => (int)$size * 1048576,
        'K', 'k' => (int)$size * 1024,
        'G', 'g' => (int)$size * 1073741824,
        default => $size,
    };
}

/**
 * @return array
 * @deprecated since 5.2.0
 */
function getNotifyDropIO(): array
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use JTL\Backend\Notification::getNotifyDropIO() instead.',
        E_USER_DEPRECATED
    );
    return Notification::getNotifyDropIO();
}

/**
 * @param string $filename
 * @return string delimiter guess
 * @former guessCsvDelimiter()
 * @deprecated since 5.2.0
 */
function getCsvDelimiter(string $filename): string
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use JTL\CSV\Import::getCsvDelimiter() instead.',
        E_USER_DEPRECATED
    );
    return Import::getCsvDelimiter($filename);
}

/**
 * @return JTLSmarty
 * @deprecated since 5.2.0
 */
function getFrontendSmarty(): JTLSmarty
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    static $frontendSmarty = null;

    if ($frontendSmarty === null) {
        $frontendSmarty = new JTLSmarty();
        $frontendSmarty->assign('imageBaseURL', Shop::getImageBaseURL())
            ->assign('NettoPreise', Frontend::getCustomerGroup()->getIsMerchant())
            ->assign('ShopURL', Shop::getURL())
            ->assign('Suchergebnisse', new SearchResults())
            ->assign('NaviFilter', Shop::getProductFilter())
            ->assign('Einstellungen', Shopsetting::getInstance()->getAll());
    }

    return $frontendSmarty;
}
