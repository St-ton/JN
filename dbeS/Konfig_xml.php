<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

require_once __DIR__ . '/syncinclude.php';

$return  = 3;
$zipFile = $_FILES['data']['tmp_name'];
if (auth()) {
    checkFile();
    $return    = 2;
    $unzipPath = PFAD_ROOT . PFAD_DBES . PFAD_SYNC_TMP . basename($zipFile) . '_' . date('dhis') . '/';
    if (($syncFiles = unzipSyncFiles($zipFile, $unzipPath)) === false) {
        if (Jtllog::doLog()) {
            Jtllog::writeLog('Error: Cannot extract zip file.', JTLLOG_LEVEL_ERROR, false, 'Konfig_xml');
        }
        removeTemporaryFiles($zipFile);
    } else {
        $return = 0;
        foreach ($syncFiles as $i => $xmlFile) {
            if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
                Jtllog::writeLog(
                    'bearbeite: ' . $xmlFile . ' size: ' . filesize($xmlFile),
                    JTLLOG_LEVEL_DEBUG,
                    false,
                    'Konfig_xml'
                );
            }
            $cData = file_get_contents($xmlFile);
            $oXml  = simplexml_load_string($cData);

            switch (pathinfo($xmlFile)['basename']) {
                case 'konfig.xml':
                    bearbeiteInsert($oXml);
                    break;

                case 'del_konfig.xml':
                    bearbeiteDeletes($oXml);
                    break;

            }
            removeTemporaryFiles($xmlFile);
        }
        removeTemporaryFiles(substr($unzipPath, 0, -1), true);
    }
}

echo $return;

if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
    Jtllog::writeLog('BEENDE: ' . $zipFile, JTLLOG_LEVEL_DEBUG, false, 'Konfig_xml');
}

/**
 * @param object $oXml
 */
function bearbeiteInsert($oXml)
{
    // Konfiggruppe
    foreach ($oXml->tkonfiggruppe as $oXmlKonfiggruppe) {
        $oKonfiggruppe = JTLMapArr($oXmlKonfiggruppe, $GLOBALS['mKonfigGruppe']);
        DBUpdateInsert('tkonfiggruppe', [$oKonfiggruppe], 'kKonfiggruppe');
        // Konfiggruppesprache
        foreach ($oXmlKonfiggruppe->tkonfiggruppesprache as $oXmlKonfiggruppesprache) {
            $oKonfiggruppesprache = JTLMapArr($oXmlKonfiggruppesprache, $GLOBALS['mKonfigSprache']);
            DBUpdateInsert('tkonfiggruppesprache', [$oKonfiggruppesprache], 'kKonfiggruppe', 'kSprache');
        }
        // Konfiggruppeitem
        loescheKonfigitem($oKonfiggruppe->kKonfiggruppe);

        foreach ($oXmlKonfiggruppe->tkonfigitem as $oXmlKonfigitem) {
            $oKonfigitem = JTLMapArr($oXmlKonfigitem, $GLOBALS['mKonfigItem']);
            DBUpdateInsert('tkonfigitem', [$oKonfigitem], 'kKonfigitem');
            // Konfiggruppeitemsprache
            foreach ($oXmlKonfigitem->tkonfigitemsprache as $oXmlKonfigitemsprache) {
                $oKonfigitemsprache = JTLMapArr($oXmlKonfigitemsprache, $GLOBALS['mKonfigSprache']);
                DBUpdateInsert('tkonfigitemsprache', [$oKonfigitemsprache], 'kKonfigitem', 'kSprache');
            }
            // Konfiggruppeitemsprache
            foreach ($oXmlKonfigitem->tkonfigitempreis as $oXmlKonfigitempreis) {
                $oKonfigitempreis = JTLMapArr($oXmlKonfigitempreis, $GLOBALS['mKonfigItemPreis']);
                DBUpdateInsert('tkonfigitempreis', [$oKonfigitempreis], 'kKonfigitem', 'kKundengruppe');
            }
        }
    }
}

/**
 * @param object $oXml
 */
function bearbeiteDeletes($oXml)
{
    // Konfiggruppe
    foreach ($oXml->kKonfiggruppe as $oXmlKonfiggruppe) {
        $kKonfiggruppe = (int)$oXmlKonfiggruppe;
        if ($kKonfiggruppe > 0) {
            loescheKonfiggruppe($kKonfiggruppe);
        }
    }
}

/**
 * @param int $kKonfiggruppe
 */
function loescheKonfiggruppe($kKonfiggruppe)
{
    $kKonfiggruppe = (int)$kKonfiggruppe;
    if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
        Jtllog::writeLog('Loesche Konfiggruppe: ' . $kKonfiggruppe, JTLLOG_LEVEL_DEBUG, false, 'Konfig_xml');
    }
    if ($kKonfiggruppe > 0) {
        require_once PFAD_ROOT . PFAD_INCLUDES_EXT . 'class.JTL-Shop.Konfiggruppe.php';
        if (class_exists('Konfiggruppe')) {
            // todo: alle items löschen
            $oKonfig = new Konfiggruppe($kKonfiggruppe);
            $nRows   = $oKonfig->delete();
            if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
                Jtllog::writeLog('Rows: ' . $nRows . ' geloescht', JTLLOG_LEVEL_DEBUG, false, 'Konfig_xml');
            }
        }
    }
}

/**
 * @param int $kKonfiggruppe
 */
function loescheKonfigitem($kKonfiggruppe)
{
    $kKonfiggruppe = (int)$kKonfiggruppe;
    if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
        Jtllog::writeLog('Loesche kKonfigitem (gruppe): ' . $kKonfiggruppe, JTLLOG_LEVEL_DEBUG, false, 'Konfig_xml');
    }
    if ($kKonfiggruppe > 0) {
        Shop::DB()->delete('tkonfigitem', 'kKonfiggruppe', $kKonfiggruppe);
    }
}

/**
 * @param int $kKonfigitem
 */
function loescheKonfigitempreis($kKonfigitem)
{
    $kKonfigitem = (int)$kKonfigitem;
    if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
        Jtllog::writeLog('Loesche Konfigitempreis: ' . $kKonfigitem, JTLLOG_LEVEL_DEBUG, false, 'Konfig_xml');
    }
    if ($kKonfigitem > 0) {
        require_once PFAD_ROOT . PFAD_INCLUDES_EXT . 'class.JTL-Shop.Konfigitempreis.php';
        if (class_exists('Konfigitempreis')) {
            $oKonfig = new Konfigitempreis($kKonfigitem);
            $nRows   = $oKonfig->delete();
            if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
                Jtllog::writeLog('Rows: ' . $nRows . ' geloescht', JTLLOG_LEVEL_DEBUG, false, 'Konfig_xml');
            }
        }
    }
}
