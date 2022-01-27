<?php declare(strict_types=1);

use JTL\Alert\Alert;
use JTL\Backend\AdminFavorite;
use JTL\Backend\Notification;
use JTL\Backend\Settings\Manager;
use JTL\Backend\Settings\SectionFactory;
use JTL\Backend\Settings\Sections\Subsection;
use JTL\Campaign;
use JTL\Catalog\Currency;
use JTL\DB\SqlObject;
use JTL\Filter\SearchResults;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\IO\IOError;
use JTL\IO\IOResponse;
use JTL\Session\Frontend;
use JTL\Shop;
use JTL\Shopsetting;
use JTL\Smarty\ContextType;
use JTL\Smarty\JTLSmarty;
use function Functional\pluck;

/**
 * @param int|array $configSectionID
 * @param bool $byName
 * @return stdClass[]
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
            'SELECT UNIQUE ec.kEinstellungenSektion AS id
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
 * @param array $settingsIDs
 * @param array $post
 * @param array $tags
 * @param bool $byName
 * @return string
 */
function saveAdminSettings(
    array $settingsIDs,
    array $post,
    array $tags = [CACHING_GROUP_OPTION],
    bool $byName = false
): string {
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
 * @param mixed  $listBoxes
 * @param string $valueName
 * @param int    $configSectionID
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
 * @param int   $configSectionID
 * @param array $post
 * @param array $tags
 * @return string
 */
function saveAdminSectionSettings(int $configSectionID, array $post, array $tags = [CACHING_GROUP_OPTION]): string
{
    Shop::Container()->getGetText()->loadAdminLocale('configs/configs');
    if (!Form::validateToken()) {
        return __('errorCSRF');
    }
    $db      = Shop::Container()->getDB();
    $manager = new Manager(
        $db,
        Shop::Smarty(),
        Shop::Container()->getAdminAccount(),
        Shop::Container()->getGetText(),
        Shop::Container()->getAlertService()
    );
    if (Request::postVar('resetSetting') !== null) {
        $manager->resetSetting(Request::postVar('resetSetting'));
        return __('successConfigReset');
    }
    $section = (new SectionFactory())->getSection($configSectionID, $manager);
    $section->update($post, true, $tags);
    $invalid = $section->getUpdateErrors();

    if ($invalid > 0) {
        return __('errorConfigSave');
    }

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
 * Wenn $bInterneKampagne false ist, werden keine Interne Shop Kampagnen geholt
 * Wenn $bAktivAbfragen true ist, werden nur Aktive Kampagnen geholt
 *
 * @param bool $internalOnly
 * @param bool $activeOnly
 * @return array
 */
function holeAlleKampagnen(bool $internalOnly = false, bool $activeOnly = true): array
{
    $activeSQL  = $activeOnly ? ' WHERE nAktiv = 1' : '';
    $interalSQL = '';
    if (!$internalOnly && $activeOnly) {
        $interalSQL = ' AND kKampagne >= 1000';
    } elseif (!$internalOnly) {
        $interalSQL = ' WHERE kKampagne >= 1000';
    }
    $campaigns = [];
    $items     = Shop::Container()->getDB()->getInts(
        'SELECT kKampagne
            FROM tkampagne
            ' . $activeSQL . '
            ' . $interalSQL . '
            ORDER BY kKampagne',
        'kKampagne'
    );
    foreach ($items as $campaignID) {
        $campaign = new Campaign($campaignID);
        if ($campaign->kKampagne > 0) {
            $campaigns[$campaign->kKampagne] = $campaign;
        }
    }

    return $campaigns;
}

/**
 *
 */
function setzeSprache(): void
{
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
 */
function firstDayOfMonth(int $month = -1, int $year = -1)
{
    return mktime(
        0,
        0,
        0,
        $month > -1 ? $month : (int)date('m'),
        1,
        $year > -1 ? $year : (int)date('Y')
    );
}

/**
 * @param int $month
 * @param int $year
 * @return false|int
 */
function lastDayOfMonth(int $month = -1, int $year = -1)
{
    return mktime(
        23,
        59,
        59,
        $month > -1 ? $month : (int)date('m'),
        (int)date('t', firstDayOfMonth($month, $year)),
        $year > -1 ? $year : (int)date('Y')
    );
}

/**
 * Ermittelt den Wochenstart und das Wochenende
 * eines Datums im Format YYYY-MM-DD
 * und gibt ein Array mit Start als Timestamp zurück
 * Array[0] = Start
 * Array[1] = Ende
 * @param string $dateString
 * @return array
 */
function ermittleDatumWoche(string $dateString): array
{
    if (mb_strlen($dateString) < 0) {
        return [];
    }
    [$year, $month, $day] = explode('-', $dateString);
    // So = 0, SA = 6
    $weekDay = (int)date('w', mktime(0, 0, 0, (int)$month, (int)$day, (int)$year));
    // Woche soll Montag starten - also So = 6, Mo = 0
    if ($weekDay === 0) {
        $weekDay = 6;
    } else {
        $weekDay--;
    }
    // Wochenstart ermitteln
    $dayOld = (int)$day;
    $day    = $dayOld - $weekDay;
    $month  = (int)$month;
    $year   = (int)$year;
    if ($day <= 0) {
        --$month;
        if ($month === 0) {
            $month = 12;
            ++$year;
        }

        $daysPerMonth = (int)date('t', mktime(0, 0, 0, $month, 1, $year));
        $day          = $daysPerMonth - $weekDay + $dayOld;
    }
    $stampStart   = mktime(0, 0, 0, $month, $day, $year);
    $days         = 6;
    $daysPerMonth = (int)date('t', mktime(0, 0, 0, $month, 1, $year));
    $day         += $days;
    if ($day > $daysPerMonth) {
        $day -= $daysPerMonth;
        ++$month;
        if ($month > 12) {
            $month = 1;
            ++$year;
        }
    }

    $stampEnd = mktime(23, 59, 59, $month, $day, $year);

    return [$stampStart, $stampEnd];
}

/**
 * Return version of files
 *
 * @param bool $date
 * @return int|string
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
 * @param float  $netPrice
 * @param float  $grossPrice
 * @param string $targetID
 * @return IOResponse
 */
function getCurrencyConversionIO($netPrice, $grossPrice, $targetID): IOResponse
{
    $response = new IOResponse();
    $response->assignDom($targetID, 'innerHTML', Currency::getCurrencyConversion($netPrice, $grossPrice));

    return $response;
}

/**
 * @param float  $netPrice
 * @param float  $grossPrice
 * @param string $tooltipID
 * @return IOResponse
 */
function setCurrencyConversionTooltipIO($netPrice, $grossPrice, $tooltipID): IOResponse
{
    $response = new IOResponse();
    $response->assignVar('originalTilte', Currency::getCurrencyConversion($netPrice, $grossPrice));

    return $response;
}

/**
 * @param string $title
 * @param string $url
 * @return array|IOError
 */
function addFav(string $title, string $url)
{
    $success     = false;
    $kAdminlogin = Shop::Container()->getAdminAccount()->getID();

    if (!empty($title) && !empty($url)) {
        $success = AdminFavorite::add($kAdminlogin, $title, $url);
    }

    if ($success) {
        $result = [
            'title' => $title,
            'url'   => $url
        ];
    } else {
        $result = new IOError('Unauthorized', 401);
    }

    return $result;
}

/**
 * @return array
 */
function reloadFavs(): array
{
    global $oAccount;

    $tpl = Shop::Smarty()->assign('favorites', $oAccount->favorites())
               ->fetch('tpl_inc/favs_drop.tpl');

    return ['tpl' => $tpl];
}

/**
 * @return array
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
