<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTL\Backend\AdminFavorite;
use JTL\Backend\Notification;
use JTL\Catalog\Currency;
use JTL\DB\ReturnType;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\IO\IOError;
use JTL\IO\IOResponse;
use JTL\Kampagne;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use JTL\XMLParser;

/**
 * @param int|array $configSectionID
 * @return array
 */
function getAdminSectionSettings($configSectionID)
{
    Shop::Container()->getGetText()->loadConfigLocales();

    $db = Shop::Container()->getDB();
    if (is_array($configSectionID)) {
        $confData = $db->query(
            'SELECT *
                FROM teinstellungenconf
                WHERE kEinstellungenConf IN (' . implode(',', $configSectionID) . ')
                ORDER BY nSort',
            ReturnType::ARRAY_OF_OBJECTS
        );
    } else {
        $confData = $db->selectAll(
            'teinstellungenconf',
            'kEinstellungenSektion',
            $configSectionID,
            '*',
            'nSort'
        );
    }
    foreach ($confData as $conf) {
        $conf->kEinstellungenSektion = (int)$conf->kEinstellungenSektion;
        $conf->kEinstellungenConf    = (int)$conf->kEinstellungenConf;
        $conf->nSort                 = (int)$conf->nSort;
        $conf->nStandardAnzeigen     = (int)$conf->nStandardAnzeigen;
        $conf->nModul                = (int)$conf->nModul;

        Shop::Container()->getGetText()->localizeConfig($conf);

        if ($conf->cInputTyp === 'listbox') {
            $conf->ConfWerte = $db->selectAll(
                'tkundengruppe',
                [],
                [],
                'kKundengruppe, cName',
                'cStandard DESC'
            );
        } elseif ($conf->cInputTyp === 'selectkdngrp') {
            $conf->ConfWerte = $db->query(
                'SELECT kKundengruppe, cName
                    FROM tkundengruppe
                    ORDER BY cStandard DESC',
                ReturnType::ARRAY_OF_OBJECTS
            );
        } else {
            $conf->ConfWerte = $db->selectAll(
                'teinstellungenconfwerte',
                'kEinstellungenConf',
                $conf->kEinstellungenConf,
                '*',
                'nSort'
            );

            Shop::Container()->getGetText()->localizeConfigValues($conf, $conf->ConfWerte);
        }

        if ($conf->cInputTyp === 'listbox') {
            $oSetValue = $db->selectAll(
                'teinstellungen',
                ['kEinstellungenSektion', 'cName'],
                [$conf->kEinstellungenSektion, $conf->cWertName],
                'cWert'
            );

            $conf->gesetzterWert = $oSetValue;
        } elseif ($conf->cInputTyp === 'selectkdngrp') {
            $oSetValue           = $db->selectAll(
                'teinstellungen',
                ['kEinstellungenSektion', 'cName'],
                [$conf->kEinstellungenSektion, $conf->cWertName]
            );
            $conf->gesetzterWert = $oSetValue;
        } else {
            $oSetValue           = $db->select(
                'teinstellungen',
                ['kEinstellungenSektion', 'cName'],
                [$conf->kEinstellungenSektion, $conf->cWertName]
            );
            $conf->gesetzterWert = $oSetValue->cWert ?? null;
        }
    }

    return $confData;
}

/**
 * @param array $settingsIDs
 * @param array $post
 * @param array $tags
 * @return string
 */
function saveAdminSettings(array $settingsIDs, array &$post, $tags = [CACHING_GROUP_OPTION])
{
    array_walk($settingsIDs, function (&$i) {
        $i = (int)$i;
    });
    $confData = Shop::Container()->getDB()->query(
        'SELECT *
            FROM teinstellungenconf
            WHERE kEinstellungenConf IN (' . implode(',', $settingsIDs) . ')
            ORDER BY nSort',
        ReturnType::ARRAY_OF_OBJECTS
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
                $val->cWert = mb_substr($val->cWert, 0, 255);
                break;
            case 'listbox':
                bearbeiteListBox($val->cWert, $val->cName, $val->kEinstellungenSektion);
                break;
        }
        if ($config->cInputTyp !== 'listbox') {
            Shop::Container()->getDB()->delete(
                'teinstellungen',
                ['kEinstellungenSektion', 'cName'],
                [(int)$config->kEinstellungenSektion, $config->cWertName]
            );
            Shop::Container()->getDB()->insert('teinstellungen', $val);
        }
    }
    Shop::Container()->getCache()->flushTags($tags);

    return __('successConfigSave');
}

/**
 * @param array  $listBoxes
 * @param string $cWertName
 * @param int    $configSectionID
 */
function bearbeiteListBox($listBoxes, $cWertName, int $configSectionID)
{
    $db = Shop::Container()->getDB();
    if (is_array($listBoxes) && count($listBoxes) > 0) {
        $db->delete(
            'teinstellungen',
            ['kEinstellungenSektion', 'cName'],
            [$configSectionID, $cWertName]
        );
        foreach ($listBoxes as $listBox) {
            $newConf                        = new stdClass();
            $newConf->cWert                 = $listBox;
            $newConf->cName                 = $cWertName;
            $newConf->kEinstellungenSektion = $configSectionID;

            $db->insert('teinstellungen', $newConf);
        }
    } elseif ($cWertName === 'bewertungserinnerung_kundengruppen' || $cWertName === 'kwk_kundengruppen') {
        // Leere Kundengruppen Work Around
        $customerGroup = $db->select('tkundengruppe', 'cStandard', 'Y');
        if ($customerGroup->kKundengruppe > 0) {
            $db->delete(
                'teinstellungen',
                ['kEinstellungenSektion', 'cName'],
                [$configSectionID, $cWertName]
            );
            $newConf                        = new stdClass();
            $newConf->cWert                 = $customerGroup->kKundengruppe;
            $newConf->cName                 = $cWertName;
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
function saveAdminSectionSettings(int $configSectionID, array &$post, $tags = [CACHING_GROUP_OPTION])
{
    if (!Form::validateToken()) {
        return __('errorCSRF');
    }
    $confData = Shop::Container()->getDB()->selectAll(
        'teinstellungenconf',
        ['kEinstellungenSektion', 'cConf'],
        [$configSectionID, 'Y'],
        '*',
        'nSort'
    );
    if (count($confData) === 0) {
        return __('errorConfigSave');
    }
    foreach ($confData as $config) {
        $val                        = new stdClass();
        $val->cWert                 = $post[$config->cWertName] ?? null;
        $val->cName                 = $config->cWertName;
        $val->kEinstellungenSektion = $configSectionID;
        switch ($config->cInputTyp) {
            case 'kommazahl':
                $val->cWert = (float)str_replace(',', '.', $val->cWert);
                break;
            case 'zahl':
            case 'number':
                $val->cWert = (int)$val->cWert;
                break;
            case 'text':
                $val->cWert = mb_substr($val->cWert, 0, 255);
                break;
            case 'listbox':
            case 'selectkdngrp':
                bearbeiteListBox($val->cWert, $config->cWertName, $configSectionID);
                break;
        }

        if ($config->cInputTyp !== 'listbox' && $config->cInputTyp !== 'selectkdngrp') {
            Shop::Container()->getDB()->delete(
                'teinstellungen',
                ['kEinstellungenSektion', 'cName'],
                [$configSectionID, $config->cWertName]
            );
            Shop::Container()->getDB()->insert('teinstellungen', $val);
        }
    }
    Shop::Container()->getCache()->flushTags($tags);

    return __('successConfigSave');
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
function holeAlleKampagnen(bool $internalOnly = false, bool $activeOnly = true)
{
    $activeSQL  = $activeOnly ? ' WHERE nAktiv = 1' : '';
    $interalSQL = '';
    if (!$internalOnly && $activeOnly) {
        $interalSQL = ' AND kKampagne >= 1000';
    } elseif (!$internalOnly) {
        $interalSQL = ' WHERE kKampagne >= 1000';
    }
    $campaigns = [];
    $items     = Shop::Container()->getDB()->query(
        'SELECT kKampagne
            FROM tkampagne
            ' . $activeSQL . '
            ' . $interalSQL . '
            ORDER BY kKampagne',
        ReturnType::ARRAY_OF_OBJECTS
    );
    foreach ($items as $item) {
        $campaign = new Kampagne((int)$item->kKampagne);
        if (isset($campaign->kKampagne) && $campaign->kKampagne > 0) {
            $campaigns[$campaign->kKampagne] = $campaign;
        }
    }

    return $campaigns;
}

/**
 * @param array $xml
 * @param int   $level
 * @return array
 * @deprecated since 5.0.0
 */
function getArrangedArray($xml, int $level = 1)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $parser = new XMLParser();
    return $parser->getArrangedArray($xml, $level);
}

/**
 *
 */
function setzeSprache()
{
    if (Form::validateToken() && Request::verifyGPCDataInt('sprachwechsel') === 1) {
        // Wähle explizit gesetzte Sprache als aktuelle Sprache
        $language = Shop::Container()->getDB()->select('tsprache', 'kSprache', Request::postInt('kSprache'));
        if ((int)$language->kSprache > 0) {
            $_SESSION['kSprache']    = (int)$language->kSprache;
            $_SESSION['cISOSprache'] = $language->cISO;
        }
    }

    if (!isset($_SESSION['kSprache'])) {
        // Wähle Standardsprache als aktuelle Sprache
        $language = Shop::Container()->getDB()->select('tsprache', 'cShopStandard', 'Y');
        if ((int)$language->kSprache > 0) {
            $_SESSION['kSprache']    = (int)$language->kSprache;
            $_SESSION['cISOSprache'] = $language->cISO;
        }
    }
    if (isset($_SESSION['kSprache']) && empty($_SESSION['cISOSprache'])) {
        // Fehlendes cISO ergänzen
        $language = Shop::Container()->getDB()->select('tsprache', 'kSprache', (int)$_SESSION['kSprache']);
        if ((int)$language->kSprache > 0) {
            $_SESSION['cISOSprache'] = $language->cISO;
        }
    }
}

/**
 * @param int $month
 * @param int $year
 * @return int
 */
function firstDayOfMonth(int $month = -1, int $year = -1)
{
    return mktime(
        0,
        0,
        0,
        $month > -1 ? $month : date('m'),
        1,
        $year > -1 ? $year : date('Y')
    );
}

/**
 * @param int $month
 * @param int $year
 * @return int
 */
function lastDayOfMonth(int $month = -1, int $year = -1)
{
    return mktime(
        23,
        59,
        59,
        $month > -1 ? $month : date('m'),
        date('t', firstDayOfMonth($month, $year)),
        $year > -1 ? $year : date('Y')
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
function ermittleDatumWoche(string $dateString)
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

        $daysPerMonth = date('t', mktime(0, 0, 0, $month, 1, $year));
        $day          = $daysPerMonth - $weekDay + $dayOld;
    }
    $stampStart   = mktime(0, 0, 0, $month, $day, $year);
    $days         = 6;
    $daysPerMonth = date('t', mktime(0, 0, 0, $month, 1, $year));
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
 * @param bool $bDate
 * @return int|string
 */
function getJTLVersionDB(bool $bDate = false)
{
    $ret         = 0;
    $versionData = Shop::Container()->getDB()->query(
        'SELECT nVersion, dAktualisiert FROM tversion',
        ReturnType::SINGLE_OBJECT
    );
    if (isset($versionData->nVersion)) {
        $ret = $versionData->nVersion;
    }
    if ($bDate) {
        $ret = $versionData->dAktualisiert;
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
 * @param float  $fPreisNetto
 * @param float  $fPreisBrutto
 * @param string $cTargetID
 * @return IOResponse
 */
function getCurrencyConversionIO($fPreisNetto, $fPreisBrutto, $cTargetID)
{
    $response = new IOResponse();
    $cString  = Currency::getCurrencyConversion($fPreisNetto, $fPreisBrutto);
    $response->assign($cTargetID, 'innerHTML', $cString);

    return $response;
}

/**
 * @param float  $fPreisNetto
 * @param float  $fPreisBrutto
 * @param string $cTooltipID
 * @return IOResponse
 */
function setCurrencyConversionTooltipIO($fPreisNetto, $fPreisBrutto, $cTooltipID)
{
    $response = new IOResponse();
    $cString  = Currency::getCurrencyConversion($fPreisNetto, $fPreisBrutto);
    $response->assign($cTooltipID, 'dataset.originalTitle', $cString);

    return $response;
}

/**
 * @param string $title
 * @param string $url
 * @return array|IOError
 */
function addFav($title, $url)
{
    $success     = false;
    $kAdminlogin = (int)$_SESSION['AdminAccount']->kAdminlogin;

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
function reloadFavs()
{
    global $oAccount;

    $tpl = Shop::Smarty()->assign('favorites', $oAccount->favorites())
               ->fetch('tpl_inc/favs_drop.tpl');

    return ['tpl' => $tpl];
}

/**
 * @return array
 */
function getNotifyDropIO()
{
    Shop::Smarty()->assign('notifications', Notification::getInstance());

    return [
        'tpl'  => Shop::Smarty()->fetch('tpl_inc/notify_drop.tpl'),
        'type' => 'notify'
    ];
}

/**
 * @param string $filename
 * @return string delimiter guess
 * @former guessCsvDelimiter()
 */
function getCsvDelimiter(string $filename)
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
 * @return \JTL\Smarty\JTLSmarty
 */
function getFrontendSmarty()
{
    static $frontendSmarty = null;

    if ($frontendSmarty === null) {
        $frontendSmarty = new JTLSmarty();
        $frontendSmarty
            ->assign('imageBaseURL', \Shop::getImageBaseURL())
            ->assign('NettoPreise', \JTL\Session\Frontend::getCustomerGroup()->getIsMerchant())
            ->assign('ShopURL', \Shop::getURL())
            ->assign('Suchergebnisse', new \JTL\Filter\SearchResults())
            ->assign('NaviFilter', \Shop::getProductFilter())
            ->assign('Einstellungen', \Shopsetting::getInstance()->getAll());
    }

    return $frontendSmarty;
}
