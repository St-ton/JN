<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * @param int $kEinstellungenSektion
 * @return array
 */
function getAdminSectionSettings($kEinstellungenSektion)
{
    $kEinstellungenSektion = intval($kEinstellungenSektion);
    $oConfig_arr           = array();
    if ($kEinstellungenSektion > 0) {
        $oConfig_arr = Shop::DB()->query(
            "SELECT *
                FROM teinstellungenconf
                WHERE kEinstellungenSektion = " . $kEinstellungenSektion . "
                ORDER BY nSort", 2
        );
        if (is_array($oConfig_arr) && count($oConfig_arr) > 0) {
            $count = count($oConfig_arr);
            for ($i = 0; $i < $count; $i++) {
                if ($oConfig_arr[$i]->cInputTyp === 'selectbox') {
                    $oConfig_arr[$i]->ConfWerte = Shop::DB()->query(
                        "SELECT *
                            FROM teinstellungenconfwerte
                            WHERE kEinstellungenConf = " . intval($oConfig_arr[$i]->kEinstellungenConf) . "
                            ORDER BY nSort", 2
                    );
                }
                $oSetValue = Shop::DB()->query(
                    "SELECT cWert
                        FROM teinstellungen
                        WHERE kEinstellungenSektion = " . $kEinstellungenSektion . "
                            AND cName = '" . $oConfig_arr[$i]->cWertName . "'", 1
                );
                $oConfig_arr[$i]->gesetzterWert = (isset($oSetValue->cWert)) ? $oSetValue->cWert : null;
            }
        }
    }

    return $oConfig_arr;
}

/**
 * @param array $settingsIDs
 * @param array $cPost_arr
 * @param array $tags
 * @return string
 */
function saveAdminSettings($settingsIDs, &$cPost_arr, $tags = array(CACHING_GROUP_OPTION))
{
    $oConfig_arr = Shop::DB()->query(
        "SELECT *
            FROM teinstellungenconf
            WHERE kEinstellungenConf IN (" . implode(',', $settingsIDs) . ")
            ORDER BY nSort", 2
    );
    $configCount = count($oConfig_arr);
    if (is_array($oConfig_arr) && count($oConfig_arr) > 0) {
        for ($i = 0; $i < $configCount; $i++) {
            $aktWert                        = new stdClass();
            $aktWert->cWert                 = (isset($cPost_arr[$oConfig_arr[$i]->cWertName])) ? $cPost_arr[$oConfig_arr[$i]->cWertName] : null;
            $aktWert->cName                 = $oConfig_arr[$i]->cWertName;
            $aktWert->kEinstellungenSektion = (int)$oConfig_arr[$i]->kEinstellungenSektion;
            switch ($oConfig_arr[$i]->cInputTyp) {
                case 'kommazahl':
                    $aktWert->cWert = floatval($aktWert->cWert);
                    break;
                case 'zahl':
                case 'number':
                    $aktWert->cWert = intval($aktWert->cWert);
                    break;
                case 'text':
                    $aktWert->cWert = substr($aktWert->cWert, 0, 255);
                    break;
                case 'listbox':
                    bearbeiteListBox($aktWert->cWert, $aktWert->cName, $aktWert->kEinstellungenSektion);
                    break;
            }
            if ($oConfig_arr[$i]->cInputTyp !== 'listbox') {
                Shop::DB()->delete('teinstellungen', array('kEinstellungenSektion', 'cName'), array((int)$oConfig_arr[$i]->kEinstellungenSektion, $oConfig_arr[$i]->cWertName));
                Shop::DB()->insert('teinstellungen', $aktWert);
            }
        }
        Shop::Cache()->flushTags($tags);

        return 'Ihre Einstellungen wurden erfolgreich &uuml;bernommen.';
    }

    return 'Fehler beim Speichern Ihrer Einstellungen.';
}

/**
 * @param array $cListBox_arr
 * @param string $cWertName
 * @param int $kEinstellungenSektion
 */
function bearbeiteListBox($cListBox_arr, $cWertName, $kEinstellungenSektion)
{
    $kEinstellungenSektion = intval($kEinstellungenSektion);
    if (is_array($cListBox_arr) && count($cListBox_arr) > 0) {
        Shop::DB()->delete('teinstellungen', array('kEinstellungenSektion', 'cName'), array($kEinstellungenSektion, $cWertName));
        foreach ($cListBox_arr as $cListBox) {
            $oAktWert                        = new stdClass();
            $oAktWert->cWert                 = $cListBox;
            $oAktWert->cName                 = $cWertName;
            $oAktWert->kEinstellungenSektion = $kEinstellungenSektion;

            Shop::DB()->insert('teinstellungen', $oAktWert);
        }
    } else {
        // Leere Kundengruppen Work Around
        if ($cWertName === 'bewertungserinnerung_kundengruppen' || $cWertName === 'kwk_kundengruppen') {
            // Standard Kundengruppe aus DB holen
            $oKundengruppe = Shop::DB()->query(
                "SELECT kKundengruppe
                    FROM tkundengruppe
                    WHERE cStandard = 'Y'", 1
            );
            if ($oKundengruppe->kKundengruppe > 0) {
                Shop::DB()->delete('teinstellungen', array('kEinstellungenSektion', 'cName'), array($kEinstellungenSektion, $cWertName));
                $oAktWert                        = new stdClass();
                $oAktWert->cWert                 = $oKundengruppe->kKundengruppe;
                $oAktWert->cName                 = $cWertName;
                $oAktWert->kEinstellungenSektion = CONF_BEWERTUNG;

                Shop::DB()->insert('teinstellungen', $oAktWert);
            }
        }
    }
}

/**
 * @param int $kEinstellungenSektion
 * @param array $cPost_arr
 * @param array $tags
 * @return string
 */
function saveAdminSectionSettings($kEinstellungenSektion, &$cPost_arr, $tags = array(CACHING_GROUP_OPTION))
{
    if (!validateToken()) {
        return 'Fehler: Cross site request forgery.';
    }
    $kEinstellungenSektion = intval($kEinstellungenSektion);
    $oConfig_arr           = Shop::DB()->query(
        "SELECT *
             FROM teinstellungenconf
             WHERE kEinstellungenSektion = " . $kEinstellungenSektion . "
                AND cConf = 'Y'
             ORDER BY nSort", 2
    );

    if (is_array($oConfig_arr) && count($oConfig_arr) > 0) {
        $count = count($oConfig_arr);
        for ($i = 0; $i < $count; $i++) {
            $aktWert                        = new stdClass();
            $aktWert->cWert                 = (isset($cPost_arr[$oConfig_arr[$i]->cWertName])) ? $cPost_arr[$oConfig_arr[$i]->cWertName] : null;
            $aktWert->cName                 = $oConfig_arr[$i]->cWertName;
            $aktWert->kEinstellungenSektion = $kEinstellungenSektion;
            switch ($oConfig_arr[$i]->cInputTyp) {
                case 'kommazahl':
                    $aktWert->cWert = floatval(str_replace(',', '.', $aktWert->cWert));
                    break;
                case 'zahl':
                case 'number':
                    $aktWert->cWert = intval($aktWert->cWert);
                    break;
                case 'text':
                    $aktWert->cWert = substr($aktWert->cWert, 0, 255);
                    break;
                case 'listbox':
                case 'selectkdngrp':
                    bearbeiteListBox($aktWert->cWert, $oConfig_arr[$i]->cWertName, $kEinstellungenSektion);
                    break;
            }

            if ($oConfig_arr[$i]->cInputTyp !== 'listbox' && $oConfig_arr[$i]->cInputTyp !== 'selectkdngrp') {
                Shop::DB()->delete('teinstellungen', array('kEinstellungenSektion', 'cName'), array($kEinstellungenSektion, $oConfig_arr[$i]->cWertName));
                Shop::DB()->insert('teinstellungen', $aktWert);
            }
        }
        Shop::Cache()->flushTags($tags);

        return 'Ihre Einstellungen wurden erfolgreich &uuml;bernommen.';
    }

    return 'Fehler beim Speichern Ihrer Einstellungen.';
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
function holeAlleKampagnen($bInterneKampagne = false, $bAktivAbfragen = true)
{
    $cAktivSQL  = ($bAktivAbfragen) ? " WHERE nAktiv = 1" : '';
    $cInternSQL = '';
    if (!$bInterneKampagne && $bAktivAbfragen) {
        $cInternSQL = " AND kKampagne >= 1000";
    } elseif (!$bInterneKampagne) {
        $cInternSQL = " WHERE kKampagne >= 1000";
    }
    $oKampagne_arr    = array();
    $oKampagneTMP_arr = Shop::DB()->query(
        "SELECT kKampagne
            FROM tkampagne
            " . $cAktivSQL . "
            " . $cInternSQL . "
            ORDER BY kKampagne", 2
    );

    if (is_array($oKampagneTMP_arr) && count($oKampagneTMP_arr) > 0) {
        foreach ($oKampagneTMP_arr as $oKampagneTMP) {
            $oKampagne = new Kampagne($oKampagneTMP->kKampagne);
            if (isset($oKampagne->kKampagne) && $oKampagne->kKampagne > 0) {
                $oKampagne_arr[$oKampagne->kKampagne] = $oKampagne;
            }
        }
    }

    return $oKampagne_arr;
}

/**
 * @param array $oXML_arr
 * @param int   $nLevel
 * @return array
 */
function getArrangedArray($oXML_arr, $nLevel = 1)
{
    if (is_array($oXML_arr)) {
        $cArrayKeys = array_keys($oXML_arr);
        $nCount     = count($oXML_arr);
        for ($i = 0; $i < $nCount; $i++) {
            if (strpos($cArrayKeys[$i], ' attr') !== false) {
                //attribut array -> nicht beachten -> weiter
                continue;
            } else {
                if (intval($cArrayKeys[$i]) > 0 || $cArrayKeys[$i] == '0' || $nLevel == 0) {
                    //int Arrayelement -> in die Tiefe gehen
                    $oXML_arr[$cArrayKeys[$i]] = getArrangedArray($oXML_arr[$cArrayKeys[$i]]);
                } else {
                    if (isset($oXML_arr[$cArrayKeys[$i]][0])) {
                        $oXML_arr[$cArrayKeys[$i]] = getArrangedArray($oXML_arr[$cArrayKeys[$i]]);
                    } else {
                        if ($oXML_arr[$cArrayKeys[$i]] === '') {
                            //empty node
                            continue;
                        }
                        //kein Attributzweig, kein numerischer Anfang
                        $tmp_arr           = array();
                        $tmp_arr['0 attr'] = (isset($oXML_arr[$cArrayKeys[$i] . ' attr'])) ? $oXML_arr[$cArrayKeys[$i] . ' attr'] : null;
                        $tmp_arr['0']      = $oXML_arr[$cArrayKeys[$i]];
                        unset($oXML_arr[$cArrayKeys[$i]]);
                        unset($oXML_arr[$cArrayKeys[$i] . ' attr']);
                        $oXML_arr[$cArrayKeys[$i]] = $tmp_arr;
                        if (is_array($oXML_arr[$cArrayKeys[$i]]['0'])) {
                            $oXML_arr[$cArrayKeys[$i]]['0'] = getArrangedArray($oXML_arr[$cArrayKeys[$i]]['0']);
                        }
                    }
                }
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
    $Einstellungen = array();
    // Einstellungen für die Bewertung holen
    $oEinstellungen_arr = Shop::DB()->query("
        SELECT cName, cWert 
          FROM teinstellungen 
          WHERE kEinstellungenSektion = " . CONF_BEWERTUNG, 2
    );
    if (is_array($oEinstellungen_arr) && count($oEinstellungen_arr) > 0) {
        $Einstellungen['bewertung']                                       = array();
        $Einstellungen['bewertung']['bewertungserinnerung_kundengruppen'] = array();

        foreach ($oEinstellungen_arr as $oEinstellungen) {
            if ($oEinstellungen->cName) {
                if ($oEinstellungen->cName === 'bewertungserinnerung_kundengruppen') {
                    $Einstellungen['bewertung'][$oEinstellungen->cName][] = $oEinstellungen->cWert;
                } else {
                    $Einstellungen['bewertung'][$oEinstellungen->cName] = $oEinstellungen->cWert;
                }
            }
        }

        return $Einstellungen['bewertung'];
    }

    return $Einstellungen;
}

/**
 *
 */
function setzeSprache()
{
    //setze std Sprache als aktuelle Sprache
    if (!isset($_SESSION['kSprache'])) {
        $StdSprache = Shop::DB()->query("SELECT kSprache FROM tsprache ORDER BY cShopStandard DESC LIMIT 1", 1);
        if ($StdSprache->kSprache > 0) {
            $_SESSION['kSprache'] = $StdSprache->kSprache;
        }
    }
    //setze explizit ausgewählte Sprache
    if (isset($_POST['sprachwechsel']) && intval($_POST['sprachwechsel']) === 1) {
        $StdSprache = Shop::DB()->query("SELECT kSprache, cISO FROM tsprache WHERE kSprache = " . intval($_POST['kSprache']), 1);
        if ($StdSprache->kSprache > 0) {
            $_SESSION['kSprache']    = $StdSprache->kSprache;
            $_SESSION['cISOSprache'] = $StdSprache->cISO;
        }
    }
}

/**
 *
 */
function setzeSpracheTrustedShops()
{
    $cISOSprache_arr = array(
        'de' => 'Deutsch',
        'en' => 'Englisch',
        'fr' => 'Französisch',
        'pl' => 'Polnisch',
        'es' => 'Spanisch'
    );
    //setze std Sprache als aktuelle Sprache
    if (!isset($_SESSION['TrustedShops']->oSprache->cISOSprache)) {
        if (!isset($_SESSION['TrustedShops'])) {
            $_SESSION['TrustedShops']           = new stdClass();
            $_SESSION['TrustedShops']->oSprache = new stdClass();
        }
        $_SESSION['TrustedShops']->oSprache->cISOSprache  = 'de';
        $_SESSION['TrustedShops']->oSprache->cNameSprache = $cISOSprache_arr['de'];
    }

    //setze explizit ausgewählte Sprache
    if (isset($_POST['sprachwechsel']) && intval($_POST['sprachwechsel']) === 1) {
        if (strlen($_POST['cISOSprache']) > 0) {
            $_SESSION['TrustedShops']->oSprache->cISOSprache  = StringHandler::htmlentities(StringHandler::filterXSS($_POST['cISOSprache']));
            $_SESSION['TrustedShops']->oSprache->cNameSprache = $cISOSprache_arr[StringHandler::htmlentities(StringHandler::filterXSS($_POST['cISOSprache']))];
        }
    }
}

/**
 * @param int $nMonth
 * @param int $nYear
 * @return int
 */
function firstDayOfMonth($nMonth = -1, $nYear = -1)
{
    return mktime(0, 0, 0, $nMonth > -1 ? $nMonth : date('m'), 1, $nYear > -1 ? $nYear : date('Y'));
}

/**
 * @param int $nMonth
 * @param int $nYear
 * @return int
 */
function lastDayOfMonth($nMonth = -1, $nYear = -1)
{
    return mktime(23, 59, 59, $nMonth > -1 ? $nMonth : date('m'), date('t', firstDayOfMonth($nMonth, $nYear)), $nYear > -1 ? $nYear : date('Y'));
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
function ermittleDatumWoche($cDatum)
{
    if (strlen($cDatum) > 0) {
        list($cJahr, $cMonat, $cTag) = explode('-', $cDatum);
        // So = 0, SA = 6
        $nWochentag = intval(date('w', mktime(0, 0, 0, intval($cMonat), intval($cTag), intval($cJahr))));
        // Woche soll Montag starten - also So = 6, Mo = 0
        if ($nWochentag == 0) {
            $nWochentag = 6;
        } else {
            $nWochentag--;
        }
        // Wochenstart ermitteln
        $nTagOld = intval($cTag);
        $nTag    = intval($cTag) - $nWochentag;
        $nMonat  = intval($cMonat);
        $nJahr   = intval($cJahr);
        if ($nTag <= 0) {
            $nMonat -= 1;
            if ($nMonat == 0) {
                $nMonat = 12;
                $nJahr += 1;
            }

            $nAnzahlTageProMonat = date('t', mktime(0, 0, 0, $nMonat, 1, $nJahr));
            $nTag                = $nAnzahlTageProMonat - $nWochentag + $nTagOld;
        }
        $nStampStart = mktime(0, 0, 0, $nMonat, $nTag, $nJahr);
        // Wochenende ermitteln
        $nTage               = 6;
        $nAnzahlTageProMonat = date('t', mktime(0, 0, 0, $nMonat, 1, $nJahr));
        $nTag += $nTage;
        if ($nTag > $nAnzahlTageProMonat) {
            $nTag = $nTag - $nAnzahlTageProMonat;
            $nMonat += 1;
            if ($nMonat > 12) {
                $nMonat = 1;
                $nJahr += 1;
            }
        }

        $nStampEnde = mktime(23, 59, 59, $nMonat, $nTag, $nJahr);

        return array($nStampStart, $nStampEnde);
    }

    return array();
}

/**
 * Return version of files
 *
 * @param bool $bDate
 * @return mixed
 */
function getJTLVersionDB($bDate = false)
{
    $nRet     = 0;
    $nVersion = Shop::DB()->query("SELECT nVersion, dAktualisiert FROM tversion", 1);
    if (isset($nVersion->nVersion) && is_numeric($nVersion->nVersion)) {
        $nRet = intval($nVersion->nVersion);
    }
    if ($bDate) {
        $nRet = $nVersion->dAktualisiert;
    }

    return $nRet;
}
