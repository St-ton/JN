<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

require_once dirname(__FILE__) . '/syncinclude.php';

$return = 3;
if (auth()) {
    checkFile();
    $return  = 2;
    $archive = new PclZip($_FILES['data']['tmp_name']);
    if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
        Jtllog::writeLog('Entpacke: ' . $_FILES['data']['tmp_name'], JTLLOG_LEVEL_DEBUG, false, 'Qucisync_xml');
    }
    if ($list = $archive->listContent()) {
        if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
            Jtllog::writeLog('Anzahl Dateien im Zip: ' . count($list), JTLLOG_LEVEL_DEBUG, false, 'QuickSync_xml');
        }
        $entzippfad = PFAD_ROOT . PFAD_DBES . PFAD_SYNC_TMP . basename($_FILES['data']['tmp_name']) . '_' . date('dhis');
        mkdir($entzippfad);
        $entzippfad .= '/';
        if ($archive->extract(PCLZIP_OPT_PATH, $entzippfad)) {
            if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
                Jtllog::writeLog('Zip entpackt in ' . $entzippfad, JTLLOG_LEVEL_DEBUG, false, 'QuickSync_xml');
            }
            $return = 0;
            foreach ($list as $i => $zip) {
                if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
                    Jtllog::writeLog('bearbeite: ' . $entzippfad . $zip['filename'] . ' size: ' . filesize($entzippfad . $zip['filename']), JTLLOG_LEVEL_DEBUG, false, 'QuickSync_xml');
                }
                $d   = file_get_contents($entzippfad . $zip['filename']);
                $xml = XML_unserialize($d);

                if ($zip['filename'] == 'quicksync.xml') {
                    bearbeiteInsert($xml);
                }

                removeTemporaryFiles($entzippfad . $zip['filename']);
            }
            removeTemporaryFiles(substr($entzippfad, 0, -1), true);
        } elseif (Jtllog::doLog(JTLLOG_LEVEL_ERROR)) {
            Jtllog::writeLog('Error : ' . $archive->errorInfo(true), JTLLOG_LEVEL_ERROR, false, 'QuickSync_xml');
        }
    } elseif (Jtllog::doLog(JTLLOG_LEVEL_ERROR)) {
        Jtllog::writeLog('Error : ' . $archive->errorInfo(true), JTLLOG_LEVEL_ERROR, false, 'QuickSync_xml');
    }
}

if ($return == 1) {
    syncException('Error : ' . $archive->errorInfo(true));
}

echo $return;
if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
    Jtllog::writeLog('BEENDE: ' . $_FILES['data']['tmp_name'], JTLLOG_LEVEL_DEBUG, false, 'QuickSync_xml');
}

/**
 * @param array $xml
 */
function bearbeiteInsert($xml)
{
    if (is_array($xml['quicksync']['tartikel'])) {
        $oArtikel_arr = mapArray($xml['quicksync'], 'tartikel', $GLOBALS['mArtikelQuickSync']);
        $nCount       = count($oArtikel_arr);

        //PREISE
        if ($nCount < 2) {
            updateXMLinDB($xml['quicksync']['tartikel'], 'tpreise', $GLOBALS['mPreise'], 'kKundengruppe', 'kArtikel');

            if (isset($xml['quicksync']['tartikel']['tpreis']) && version_compare($_POST['vers'], '099976', '>=')) {
                handleNewPriceFormat($xml['quicksync']['tartikel']);
            } else {
                handleOldPriceFormat(mapArray($xml['quicksync']['tartikel'], 'tpreise', $GLOBALS['mPreise']));
            }

            // Preise für Preisverlauf
            $oPreis_arr = mapArray($xml['quicksync']['tartikel'], 'tpreise', $GLOBALS['mPreise']);
            foreach ($oPreis_arr as $oPreis) {
                setzePreisverlauf($oPreis->kArtikel, $oPreis->kKundengruppe, $oPreis->fVKNetto);
            }
        } else {
            for ($i = 0; $i < $nCount; $i++) {
                updateXMLinDB($xml['quicksync']['tartikel'][$i], 'tpreise', $GLOBALS['mPreise'], 'kKundengruppe', 'kArtikel');

                if (version_compare($_POST['vers'], '099976', '>=')) {
                    handleNewPriceFormat(mapArray($xml['quicksync']['tartikel'][$i], 'tpreise', $GLOBALS['mPreise']));
                }

                if (isset($xml['quicksync']['tartikel'][$i]['tpreis']) && version_compare($_POST['vers'], '099976', '>=')) {
                    handleNewPriceFormat($xml['quicksync']['tartikel'][$i]);
                } else {
                    handleOldPriceFormat(mapArray($xml['quicksync']['tartikel'][$i], 'tpreise', $GLOBALS['mPreise']));
                }

                // Preise für Preisverlauf
                $oPreis_arr = mapArray($xml['quicksync']['tartikel'][$i], 'tpreise', $GLOBALS['mPreise']);
                foreach ($oPreis_arr as $oPreis) {
                    setzePreisverlauf($oPreis->kArtikel, $oPreis->kKundengruppe, $oPreis->fVKNetto);
                }
            }
        }
        $clearTags = array();
        foreach ($oArtikel_arr as $oArtikel) {
            //any new orders since last wawi-sync? see https://gitlab.jtl-software.de/jtlshop/jtl-shop/issues/304
            if (isset($oArtikel->fLagerbestand) && $oArtikel->fLagerbestand > 0) {
                $delta = Shop::DB()->query("
                  SELECT SUM(pos.nAnzahl) AS totalquantity 
                    FROM tbestellung b 
                    JOIN twarenkorbpos pos 
                      ON pos.kWarenkorb = b.kWarenkorb 
                      WHERE b.cAbgeholt = 'N' 
                        AND pos.kArtikel = " . (int)$oArtikel->kArtikel, 1
                );
                if ($delta->totalquantity > 0) {
                    $oArtikel->fLagerbestand = $oArtikel->fLagerbestand - $delta->totalquantity; //subtract delta from stocklevel
                    if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
                        Jtllog::writeLog("Artikel-Quicksync: Lagerbestand von kArtikel {$oArtikel->kArtikel} wurde wegen nicht-abgeholter Bestellungen um {$delta->totalquantity} reduziert auf {$oArtikel->fLagerbestand}." , JTLLOG_LEVEL_DEBUG, false, 'Artikel_xml');
                    }
                }
            }
            
            if ($oArtikel->fLagerbestand < 0) {
                $oArtikel->fLagerbestand = 0;
            }
            Shop::DB()->query("UPDATE tartikel SET fLagerbestand = " . $oArtikel->fLagerbestand . " WHERE kArtikel = " . (int)$oArtikel->kArtikel, 4);

            // Clear Artikel Cache
            $clearTags[] = CACHING_GROUP_ARTICLE . '_' . (int)$oArtikel->kArtikel;
            if (Shop::Cache()->isPageCacheEnabled()) {
                if (!isset($smarty)) {
                    $smarty = Shop::Smarty();
                }
                if (isset($smarty)) {
                    //@todo: smarty is null...
                    $smarty->clearCache(null, 'jtlc|article|aid' . (int)$oArtikel->kArtikel);
                }
            }
            versendeVerfuegbarkeitsbenachrichtigung($oArtikel);
        }
        Shop::Cache()->flushTags($clearTags);
    }
}
