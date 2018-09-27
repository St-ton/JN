<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * @param int $configSectionID
 * @return array
 */
function getAdminSectionSettings(int $configSectionID)
{
    $confData = Shop::Container()->getDB()->selectAll(
        'teinstellungenconf',
        'kEinstellungenSektion',
        $configSectionID,
        '*',
        'nSort'
    );
    foreach ($confData as $conf) {
        if ($conf->cInputTyp === 'selectbox') {
            $conf->ConfWerte = Shop::Container()->getDB()->selectAll(
                'teinstellungenconfwerte',
                'kEinstellungenConf',
                $conf->kEinstellungenConf,
                '*',
                'nSort'
            );
        }
        $oSetValue           = Shop::Container()->getDB()->select(
            'teinstellungen',
            ['kEinstellungenSektion', 'cName'],
            [$configSectionID, $conf->cWertName]
        );
        $conf->gesetzterWert = $oSetValue->cWert ?? null;
    }

    return $confData;
}

/**
 * @param array $settingsIDs
 * @param array $cPost_arr
 * @param array $tags
 * @return string
 */
function saveAdminSettings(array $settingsIDs, array &$cPost_arr, $tags = [CACHING_GROUP_OPTION])
{
    array_walk($settingsIDs, function (&$i) {
        $i = (int)$i;
    });
    $confData = Shop::Container()->getDB()->query(
        'SELECT *
            FROM teinstellungenconf
            WHERE kEinstellungenConf IN (' . implode(',', $settingsIDs) . ')
            ORDER BY nSort',
        \DB\ReturnType::ARRAY_OF_OBJECTS
    );
    if (count($confData) === 0) {
        return 'Fehler beim Speichern Ihrer Einstellungen.';
    }
    foreach ($confData as $config) {
        $val                        = new stdClass();
        $val->cWert                 = $cPost_arr[$config->cWertName] ?? null;
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
                $val->cWert = substr($val->cWert, 0, 255);
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
    Shop::Cache()->flushTags($tags);

    return 'Ihre Einstellungen wurden erfolgreich übernommen.';
}

/**
 * @param array  $listBoxes
 * @param string $cWertName
 * @param int    $configSectionID
 */
function bearbeiteListBox($listBoxes, $cWertName, int $configSectionID)
{
    if (is_array($listBoxes) && count($listBoxes) > 0) {
        Shop::Container()->getDB()->delete(
            'teinstellungen',
            ['kEinstellungenSektion', 'cName'],
            [$configSectionID, $cWertName]
        );
        foreach ($listBoxes as $cListBox) {
            $oAktWert                        = new stdClass();
            $oAktWert->cWert                 = $cListBox;
            $oAktWert->cName                 = $cWertName;
            $oAktWert->kEinstellungenSektion = $configSectionID;

            Shop::Container()->getDB()->insert('teinstellungen', $oAktWert);
        }
    } elseif ($cWertName === 'bewertungserinnerung_kundengruppen' || $cWertName === 'kwk_kundengruppen') {
        // Leere Kundengruppen Work Around
        $oKundengruppe = Shop::Container()->getDB()->select('tkundengruppe', 'cStandard', 'Y');
        if ($oKundengruppe->kKundengruppe > 0) {
            Shop::Container()->getDB()->delete(
                'teinstellungen',
                ['kEinstellungenSektion', 'cName'],
                [$configSectionID, $cWertName]
            );
            $oAktWert                        = new stdClass();
            $oAktWert->cWert                 = $oKundengruppe->kKundengruppe;
            $oAktWert->cName                 = $cWertName;
            $oAktWert->kEinstellungenSektion = CONF_BEWERTUNG;

            Shop::Container()->getDB()->insert('teinstellungen', $oAktWert);
        }
    }
}

/**
 * @param int   $configSectionID
 * @param array $cPost_arr
 * @param array $tags
 * @return string
 */
function saveAdminSectionSettings(int $configSectionID, array &$cPost_arr, $tags = [CACHING_GROUP_OPTION])
{
    if (!FormHelper::validateToken()) {
        return 'Fehler: Cross site request forgery.';
    }
    $confData = Shop::Container()->getDB()->selectAll(
        'teinstellungenconf',
        ['kEinstellungenSektion', 'cConf'],
        [$configSectionID, 'Y'],
        '*',
        'nSort'
    );
    if (count($confData) === 0) {
        return 'Fehler beim Speichern Ihrer Einstellungen.';
    }
    foreach ($confData as $config) {
        $val                        = new stdClass();
        $val->cWert                 = $cPost_arr[$config->cWertName] ?? null;
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
                $val->cWert = substr($val->cWert, 0, 255);
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
    Shop::Cache()->flushTags($tags);

    return 'Ihre Einstellungen wurden erfolgreich übernommen.';
}

/**
 * Holt alle vorhandenen Kampagnen
 * Wenn $bInterneKampagne false ist, werden keine Interne Shop Kampagnen geholt
 * Wenn $bAktivAbfragen true ist, werden nur Aktive Kampagnen geholt
 *
 * @param bool $bInterneKampagne
 * @param bool $bAktivAbfragen
 * @return array
 */
function holeAlleKampagnen(bool $bInterneKampagne = false, bool $bAktivAbfragen = true)
{
    $cAktivSQL  = $bAktivAbfragen ? ' WHERE nAktiv = 1' : '';
    $cInternSQL = '';
    if (!$bInterneKampagne && $bAktivAbfragen) {
        $cInternSQL = ' AND kKampagne >= 1000';
    } elseif (!$bInterneKampagne) {
        $cInternSQL = ' WHERE kKampagne >= 1000';
    }
    $oKampagne_arr    = [];
    $oKampagneTMP_arr = Shop::Container()->getDB()->query(
        'SELECT kKampagne
            FROM tkampagne
            ' . $cAktivSQL . '
            ' . $cInternSQL . '
            ORDER BY kKampagne',
        \DB\ReturnType::ARRAY_OF_OBJECTS
    );
    foreach ($oKampagneTMP_arr as $oKampagneTMP) {
        $oKampagne = new Kampagne($oKampagneTMP->kKampagne);
        if (isset($oKampagne->kKampagne) && $oKampagne->kKampagne > 0) {
            $oKampagne_arr[$oKampagne->kKampagne] = $oKampagne;
        }
    }

    return $oKampagne_arr;
}

/**
 * @param array $oXML_arr
 * @param int   $nLevel
 * @return array
 */
function getArrangedArray($oXML_arr, int $nLevel = 1)
{
    if (!is_array($oXML_arr)) {
        return $oXML_arr;
    }
    $cArrayKeys = array_keys($oXML_arr);
    $nCount     = count($oXML_arr);
    for ($i = 0; $i < $nCount; $i++) {
        if (strpos($cArrayKeys[$i], ' attr') !== false) {
            //attribut array -> nicht beachten -> weiter
            continue;
        }
        if ($nLevel === 0 || (int)$cArrayKeys[$i] > 0 || $cArrayKeys[$i] == '0') {
            //int Arrayelement -> in die Tiefe gehen
            $oXML_arr[$cArrayKeys[$i]] = getArrangedArray($oXML_arr[$cArrayKeys[$i]]);
        } elseif (isset($oXML_arr[$cArrayKeys[$i]][0])) {
            $oXML_arr[$cArrayKeys[$i]] = getArrangedArray($oXML_arr[$cArrayKeys[$i]]);
        } else {
            if ($oXML_arr[$cArrayKeys[$i]] === '') {
                //empty node
                continue;
            }
            //kein Attributzweig, kein numerischer Anfang
            $tmp_arr           = [];
            $tmp_arr['0 attr'] = $oXML_arr[$cArrayKeys[$i] . ' attr'] ?? null;
            $tmp_arr['0']      = $oXML_arr[$cArrayKeys[$i]];
            unset($oXML_arr[$cArrayKeys[$i]], $oXML_arr[$cArrayKeys[$i] . ' attr']);
            $oXML_arr[$cArrayKeys[$i]] = $tmp_arr;
            if (is_array($oXML_arr[$cArrayKeys[$i]]['0'])) {
                $oXML_arr[$cArrayKeys[$i]]['0'] = getArrangedArray($oXML_arr[$cArrayKeys[$i]]['0']);
            }
        }
    }

    return $oXML_arr;
}

/**
 * @return array
 */
function holeBewertungserinnerungSettings()
{
    $conf     = [];
    $confData = Shop::Container()->getDB()->selectAll(
        'teinstellungen',
        'kEinstellungenSektion',
        CONF_BEWERTUNG
    );

    $conf['bewertung']                                       = [];
    $conf['bewertung']['bewertungserinnerung_kundengruppen'] = [];

    foreach ($confData as $data) {
        if ($data->cName) {
            if ($data->cName === 'bewertungserinnerung_kundengruppen') {
                $conf['bewertung'][$data->cName][] = $data->cWert;
            } else {
                $conf['bewertung'][$data->cName] = $data->cWert;
            }
        }
    }

    return $conf['bewertung'];
}

/**
 *
 */
function setzeSprache()
{
    if (FormHelper::validateToken() && RequestHelper::verifyGPCDataInt('sprachwechsel') === 1) {
        // Wähle explizit gesetzte Sprache als aktuelle Sprache
        $oSprache = Shop::Container()->getDB()->select('tsprache', 'kSprache', (int)$_POST['kSprache']);

        if ((int)$oSprache->kSprache > 0) {
            $_SESSION['kSprache']    = (int)$oSprache->kSprache;
            $_SESSION['cISOSprache'] = $oSprache->cISO;
        }
    }

    if (!isset($_SESSION['kSprache'])) {
        // Wähle Standardsprache als aktuelle Sprache
        $oSprache = Shop::Container()->getDB()->select('tsprache', 'cShopStandard', 'Y');

        if ((int)$oSprache->kSprache > 0) {
            $_SESSION['kSprache']    = (int)$oSprache->kSprache;
            $_SESSION['cISOSprache'] = $oSprache->cISO;
        }
    }
    if (isset($_SESSION['kSprache']) && empty($_SESSION['cISOSprache'])) {
        // Fehlendes cISO ergänzen
        $oSprache = Shop::Container()->getDB()->select('tsprache', 'kSprache', (int)$_SESSION['kSprache']);

        if ((int)$oSprache->kSprache > 0) {
            $_SESSION['cISOSprache'] = $oSprache->cISO;
        }
    }
}

/**
 *
 */
function setzeSpracheTrustedShops()
{
    $cISOSprache_arr = [
        'de' => 'Deutsch',
        'en' => 'Englisch',
        'fr' => 'Französisch',
        'pl' => 'Polnisch',
        'es' => 'Spanisch'
    ];
    if (!isset($_SESSION['TrustedShops']->oSprache->cISOSprache)) {
        if (!isset($_SESSION['TrustedShops'])) {
            $_SESSION['TrustedShops']           = new stdClass();
            $_SESSION['TrustedShops']->oSprache = new stdClass();
        }
        $_SESSION['TrustedShops']->oSprache->cISOSprache  = 'de';
        $_SESSION['TrustedShops']->oSprache->cNameSprache = $cISOSprache_arr['de'];
    }
    // setze explizit ausgewählte Sprache
    if (isset($_POST['sprachwechsel']) && (int)$_POST['sprachwechsel'] === 1 && strlen($_POST['cISOSprache']) > 0) {
        $_SESSION['TrustedShops']->oSprache->cISOSprache  =
            StringHandler::htmlentities(StringHandler::filterXSS($_POST['cISOSprache']));
        $_SESSION['TrustedShops']->oSprache->cNameSprache =
            $cISOSprache_arr[StringHandler::htmlentities(StringHandler::filterXSS($_POST['cISOSprache']))];
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
 * @param string $cDatum
 * @return array
 */
function ermittleDatumWoche(string $cDatum)
{
    if (strlen($cDatum) < 0) {
        return [];
    }
    list($cJahr, $cMonat, $cTag) = explode('-', $cDatum);
    // So = 0, SA = 6
    $nWochentag = (int)date('w', mktime(0, 0, 0, (int)$cMonat, (int)$cTag, (int)$cJahr));
    // Woche soll Montag starten - also So = 6, Mo = 0
    if ($nWochentag === 0) {
        $nWochentag = 6;
    } else {
        $nWochentag--;
    }
    // Wochenstart ermitteln
    $nTagOld = (int)$cTag;
    $nTag    = (int)$cTag - $nWochentag;
    $nMonat  = (int)$cMonat;
    $nJahr   = (int)$cJahr;
    if ($nTag <= 0) {
        --$nMonat;
        if ($nMonat === 0) {
            $nMonat = 12;
            ++$nJahr;
        }

        $daysPerMonth = date('t', mktime(0, 0, 0, $nMonat, 1, $nJahr));
        $nTag         = $daysPerMonth - $nWochentag + $nTagOld;
    }
    $nStampStart = mktime(0, 0, 0, $nMonat, $nTag, $nJahr);
    // Wochenende ermitteln
    $nTage        = 6;
    $daysPerMonth = date('t', mktime(0, 0, 0, $nMonat, 1, $nJahr));
    $nTag         += $nTage;
    if ($nTag > $daysPerMonth) {
        $nTag -= $daysPerMonth;
        ++$nMonat;
        if ($nMonat > 12) {
            $nMonat = 1;
            ++$nJahr;
        }
    }

    $nStampEnde = mktime(23, 59, 59, $nMonat, $nTag, $nJahr);

    return [$nStampStart, $nStampEnde];
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
        \DB\ReturnType::SINGLE_OBJECT
    );
    if (isset($versionData->nVersion) && is_numeric($versionData->nVersion)) {
        $ret = (int)$versionData->nVersion;
    }
    if ($bDate) {
        $ret = $versionData->dAktualisiert;
    }

    return $ret;
}

/**
 * @param string $size_str
 * @return mixed
 */
function getMaxFileSize($size_str)
{
    switch (substr($size_str, -1)) {
        case 'M':
        case 'm':
            return (int)$size_str * 1048576;
        case 'K':
        case 'k':
            return (int)$size_str * 1024;
        case 'G':
        case 'g':
            return (int)$size_str * 1073741824;
        default:
            return $size_str;
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
        if (strpos($firstLine, $delim) !== false) {
            fclose($file);

            return $delim;
        }
    }
    fclose($file);

    return ';';
}
