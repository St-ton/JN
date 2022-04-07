<?php declare(strict_types=1);

use JTL\Backend\Notification;
use JTL\Backend\Settings\Manager;
use JTL\Backend\Settings\SectionFactory;
use JTL\Backend\Settings\Sections\Subsection;
use JTL\Campaign;
use JTL\DB\SqlObject;
use JTL\Filter\SearchResults;
use JTL\Helpers\Date;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Router\Controller\Backend\AbstractBackendController;
use JTL\Session\Frontend;
use JTL\Shop;
use JTL\Shopsetting;
use JTL\Smarty\ContextType;
use JTL\Smarty\JTLSmarty;
use function Functional\pluck;

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
    $db             = Shop::Container()->getDB();
    $settingManager = new Manager(
        $db,
        Shop::Smarty(),
        Shop::Container()->getAdminAccount(),
        Shop::Container()->getGetText(),
        Shop::Container()->getAlertService()
    );
    if (Request::postVar('resetSetting') !== null) {
        $settingManager->resetSetting(Request::postVar('resetSetting'));

        return __('successConfigReset');
    }
    $where    = $byName
        ? "WHERE ec.cWertName IN ('" . implode("','", $settingsIDs) . "')"
        : 'WHERE ec.kEinstellungenConf IN (' . implode(',', array_map('\intval', $settingsIDs)) . ')';
    $confData = $db->getObjects(
        'SELECT ec.*, e.cWert AS currentValue
            FROM teinstellungenconf AS ec
            LEFT JOIN teinstellungen AS e 
                ON e.cName = ec.cWertName
            ' . $where . "
            AND ec.cConf = 'Y'
            ORDER BY ec.nSort"
    );
    if (count($confData) === 0) {
        return __('errorConfigSave');
    }
    foreach ($confData as $config) {
        $val                        = new stdClass();
        $val->cWert                 = $post[$config->cWertName] ?? null;
        $val->cName                 = $config->cWertName;
        $val->kEinstellungenSektion = (int)$config->kEinstellungenSektion;
        switch ($config->cInputTyp) {
            case 'kommazahl':
                $val->cWert = (float)$val->cWert;
                break;
            case 'zahl':
            case 'number':
                $val->cWert = (int)$val->cWert;
                break;
            case 'text':
                $val->cWert = Text::filterXSS(mb_substr($val->cWert, 0, 255));
                break;
            case 'listbox':
                bearbeiteListBox($val->cWert, $val->cName, $val->kEinstellungenSektion);
                break;
            default:
                break;
        }
        if ($config->cInputTyp !== 'listbox') {
            $db->delete(
                'teinstellungen',
                ['kEinstellungenSektion', 'cName'],
                [(int)$config->kEinstellungenSektion, $config->cWertName]
            );
            $db->insert('teinstellungen', $val);

            $settingManager->addLog($config->cWertName, $config->currentValue, $post[$config->cWertName]);
        }
    }
    Shop::Container()->getCache()->flushTags($tags);

    return __('successConfigSave');
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
        __FUNCTION__ . ' is deprecated. Use TL\Helpers\Date::getWeekStartAndEnd() instead.',
        E_USER_DEPRECATED
    );

    return Date::getWeekStartAndEnd($dateString);
}

/**
 * @param int   $configSectionID
 * @param array $post
 * @param array $tags
 * @return string
 * @todo!!!
 */
function saveAdminSectionSettings(int $configSectionID, array $post, array $tags = [CACHING_GROUP_OPTION]): string
{
    $alertService = Shop::Container()->getAlertService();
    if (!Form::validateToken()) {
        $msg = __('errorCSRF');
        $alertService->addError($msg, 'saveSettingsErrCsrf');

        return $msg;
    }
    $manager = new Manager(
        Shop::Container()->getDB(),
        Shop::Smarty(),
        Shop::Container()->getAdminAccount(),
        Shop::Container()->getGetText(),
        $alertService
    );
    if (Request::postVar('resetSetting') !== null) {
        $manager->resetSetting(Request::postVar('resetSetting'));
        return __('successConfigReset');
    }
    $section = (new SectionFactory())->getSection($configSectionID, $manager);
    $section->update($post, true, $tags);
    $invalid = $section->getUpdateErrors();

    if ($invalid > 0) {
        $msg = __('errorConfigSave');
        $alertService->addError($msg, 'saveSettingsErr');

        return $msg;
    }
    $msg = __('successConfigSave');
    $alertService->addSuccess($msg, 'saveSettings');

    return $msg;
}

/**
 * @param int|array $configSectionID
 * @param bool $byName
 * @return stdClass[]
 * @todo!!!
 */
function getAdminSectionSettings($configSectionID, bool $byName = false): array
{
    $sections       = [];
    $filterNames    = [];
    $smarty         = Shop::Smarty();
    $db             = Shop::Container()->getDB();
    $getText        = Shop::Container()->getGetText();
    $adminAccount   = Shop::Container()->getAdminAccount();
    $alertService   = Shop::Container()->getAlertService();
    $sectionFactory = new SectionFactory();
    $settingManager = new Manager($db, $smarty, $adminAccount, $getText, $alertService);
    if ($byName) {
        $sql = new SqlObject();
        $in  = [];
        foreach ($configSectionID as $i => $item) {
            $sql->addParam(':itm' . $i, $item);
            $in[] = ':itm' . $i;
        }
        $sectionIDs      = $db->getObjects(
            'SELECT DISTINCT ec.kEinstellungenSektion AS id
                FROM teinstellungenconf AS ec
                LEFT JOIN teinstellungen_default AS e
                    ON e.cName = ec.cWertName 
                    WHERE ec.cWertName IN (' . implode(',', $in) . ')
                    ORDER BY ec.nSort',
            $sql->getParams()
        );
        $filterNames     = $configSectionID;
        $configSectionID = array_map('\intval', pluck($sectionIDs, 'id'));
    }
    foreach ((array)$configSectionID as $id) {
        $section = $sectionFactory->getSection($id, $settingManager);
        $section->load();
        $sections[] = $section;
    }
    if (count($filterNames) > 0) {
        $section    = $sectionFactory->getSection(1, $settingManager);
        $subsection = new Subsection();
        foreach ($sections as $_section) {
            foreach ($_section->getSubsections() as $_subsection) {
                foreach ($_subsection->getItems() as $item) {
                    if (in_array($item->getValueName(), $filterNames, true)) {
                        $subsection->addItem($item);
                    }
                }
            }
        }
        $section->setSubsections([$subsection]);
        $sections = [$section];
    }
    $smarty->assign('sections', $sections);

    return $sections;
}

/**
 * @param mixed  $listBoxes
 * @param string $valueName
 * @param int    $configSectionID
 * @deprecated since 5.2.0
 */
function bearbeiteListBox($listBoxes, string $valueName, int $configSectionID): void
{
    $db = Shop::Container()->getDB();
    if (is_array($listBoxes) && count($listBoxes) > 0) {
        $settingManager = new Manager(
            $db,
            Shop::Smarty(),
            Shop::Container()->getAdminAccount(),
            Shop::Container()->getGetText(),
            Shop::Container()->getAlertService()
        );
        $settingManager->addLogListbox($valueName, $listBoxes);
        $db->delete(
            'teinstellungen',
            ['kEinstellungenSektion', 'cName'],
            [$configSectionID, $valueName]
        );
        foreach ($listBoxes as $listBox) {
            $newConf                        = new stdClass();
            $newConf->cWert                 = $listBox;
            $newConf->cName                 = $valueName;
            $newConf->kEinstellungenSektion = $configSectionID;

            $db->insert('teinstellungen', $newConf);
        }
    } elseif ($valueName === 'bewertungserinnerung_kundengruppen') {
        // Leere Kundengruppen Work Around
        $customerGroup = $db->select('tkundengruppe', 'cStandard', 'Y');
        if ($customerGroup->kKundengruppe > 0) {
            $db->delete(
                'teinstellungen',
                ['kEinstellungenSektion', 'cName'],
                [$configSectionID, $valueName]
            );
            $newConf                        = new stdClass();
            $newConf->cWert                 = $customerGroup->kKundengruppe;
            $newConf->cName                 = $valueName;
            $newConf->kEinstellungenSektion = CONF_BEWERTUNG;

            $db->insert('teinstellungen', $newConf);
        }
    }
}

/**
 * Return version of files
 *
 * @param bool $date
 * @return int|string
 * @todo!
 */
function getJTLVersionDB(bool $date = false)
{
    $ret = 0;
    if ($date) {
        $latestUpdate = Shop::Container()->getDB()->getSingleObject('SELECT MAX(dExecuted) AS date FROM tmigration');
        $ret          = $latestUpdate->date ?? 0;
    } else {
        $versionData = Shop::Container()->getDB()->getSingleObject('SELECT nVersion FROM tversion');
        if ($versionData !== null) {
            $ret = $versionData->nVersion;
        }
    }

    return $ret;
}

/**
 * @param string $size
 * @return mixed
 * @todo!
 */
function getMaxFileSize($size)
{
    switch (mb_substr($size, -1)) {
        case 'M':
        case 'm':
            return (int)$size * 1048576;
        case 'K':
        case 'k':
            return (int)$size * 1024;
        case 'G':
        case 'g':
            return (int)$size * 1073741824;
        default:
            return $size;
    }
}

/**
 * @return array
 * @todo!
 */
function getNotifyDropIO(): array
{
    return [
        'tpl'  => JTLSmarty::getInstance(false, ContextType::BACKEND)
            ->assign('notifications', Notification::getInstance())
            ->fetch('tpl_inc/notify_drop.tpl'),
        'type' => 'notify'
    ];
}

/**
 * @param string $filename
 * @return string delimiter guess
 * @former guessCsvDelimiter()
 * @todo!
 */
function getCsvDelimiter(string $filename): string
{
    $file      = fopen($filename, 'r');
    $firstLine = fgets($file);

    foreach ([';', ',', '|', '\t'] as $delim) {
        if (mb_strpos($firstLine, $delim) !== false) {
            fclose($file);

            return $delim;
        }
    }
    fclose($file);

    return ';';
}

/**
 * @return JTLSmarty
 * @todo!
 */
function getFrontendSmarty(): JTLSmarty
{
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
