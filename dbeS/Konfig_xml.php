<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

require_once __DIR__ . '/syncinclude.php';

$return  = 3;
$zipFile = $_FILES['data']['tmp_name'];
if (auth()) {
    $zipFile   = checkFile();
    $return    = 2;
    $unzipPath = PFAD_ROOT . PFAD_DBES . PFAD_SYNC_TMP . basename($zipFile) . '_' . date('dhis') . '/';
    if (($syncFiles = unzipSyncFiles($zipFile, $unzipPath, __FILE__)) === false) {
        Shop::Container()->getLogService()->error('Error: Cannot extract zip file ' . $zipFile . ' to ' . $unzipPath);
        removeTemporaryFiles($zipFile);
    } else {
        $return = 0;
        foreach ($syncFiles as $i => $xmlFile) {
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
        loescheKonfigitem((int)$oKonfiggruppe->kKonfiggruppe);

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
function loescheKonfiggruppe(int $kKonfiggruppe)
{
    if ($kKonfiggruppe > 0 && class_exists('Konfiggruppe')) {
        // todo: alle items löschen
        $oKonfig = new Konfiggruppe($kKonfiggruppe);
        $nRows   = $oKonfig->delete();
        Shop::Container()->getLogService()->debug($nRows . ' Konfiggruppen gelöscht');
    }
}

/**
 * @param int $kKonfiggruppe
 */
function loescheKonfigitem(int $kKonfiggruppe)
{
    if ($kKonfiggruppe > 0) {
        Shop::Container()->getDB()->delete('tkonfigitem', 'kKonfiggruppe', $kKonfiggruppe);
    }
}

/**
 * @param int $kKonfigitem
 */
function loescheKonfigitempreis(int $kKonfigitem)
{
    if ($kKonfigitem > 0 && class_exists('Konfigitempreis')) {
        $oKonfig = new Konfigitempreis($kKonfigitem);
        $nRows   = $oKonfig->delete();
        Shop::Container()->getLogService()->debug($nRows . ' Konfigitempreise gelöscht');
    }
}
