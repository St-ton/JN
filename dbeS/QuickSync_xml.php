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
            $d   = file_get_contents($xmlFile);
            $xml = XML_unserialize($d);

            if (strpos($xmlFile, 'quicksync.xml') !== false) {
                bearbeiteInsert($xml);
            }

            removeTemporaryFiles($xmlFile);
        }
        removeTemporaryFiles(substr($unzipPath, 0, -1), true);
    }
}

echo $return;

/**
 * @param array $xml
 */
function bearbeiteInsert($xml)
{
    if (!is_array($xml['quicksync']['tartikel'])) {
        return;
    }
    $oArtikel_arr = mapArray($xml['quicksync'], 'tartikel', $GLOBALS['mArtikelQuickSync']);
    $nCount       = count($oArtikel_arr);
    if ($nCount < 2) {
        updateXMLinDB($xml['quicksync']['tartikel'], 'tpreise', $GLOBALS['mPreise'], 'kKundengruppe', 'kArtikel');

        if (isset($xml['quicksync']['tartikel']['tpreis']) && version_compare($_POST['vers'], '099976', '>=')) {
            handleNewPriceFormat($xml['quicksync']['tartikel']);
        } else {
            handleOldPriceFormat(mapArray($xml['quicksync']['tartikel'], 'tpreise', $GLOBALS['mPreise']));
        }
        $oPreis_arr = mapArray($xml['quicksync']['tartikel'], 'tpreise', $GLOBALS['mPreise']);
        foreach ($oPreis_arr as $oPreis) {
            setzePreisverlauf($oPreis->kArtikel, $oPreis->kKundengruppe, $oPreis->fVKNetto);
        }
    } else {
        for ($i = 0; $i < $nCount; ++$i) {
            updateXMLinDB(
                $xml['quicksync']['tartikel'][$i],
                'tpreise',
                $GLOBALS['mPreise'],
                'kKundengruppe',
                'kArtikel'
            );
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
    $clearTags = [];
    foreach ($oArtikel_arr as $oArtikel) {
        if (isset($oArtikel->fLagerbestand) && $oArtikel->fLagerbestand > 0) {
            $delta = Shop::Container()->getDB()->query(
                "SELECT SUM(pos.nAnzahl) AS totalquantity
                    FROM tbestellung b
                    JOIN twarenkorbpos pos
                    ON pos.kWarenkorb = b.kWarenkorb
                    WHERE b.cAbgeholt = 'N'
                        AND pos.kArtikel = " . (int)$oArtikel->kArtikel,
                \DB\ReturnType::SINGLE_OBJECT
            );
            if ($delta->totalquantity > 0) {
                $oArtikel->fLagerbestand -= $delta->totalquantity;
                if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
                    Jtllog::writeLog('Artikel-Quicksync: Lagerbestand von kArtikel ' .
                        $oArtikel->kArtikel . ' wurde wegen nicht-abgeholter Bestellungen '.
                        'um ' . $delta->totalquantity . ' auf ' . $oArtikel->fLagerbestand . ' reduziert.',
                        JTLLOG_LEVEL_DEBUG,
                        false,
                        'Artikel_xml'
                    );
                }
            }
        }

        if ($oArtikel->fLagerbestand < 0) {
            $oArtikel->fLagerbestand = 0;
        }

        $upd                        = new stdClass();
        $upd->fLagerbestand         = $oArtikel->fLagerbestand;
        $upd->fStandardpreisNetto   = $oArtikel->fStandardpreisNetto;
        $upd->dLetzteAktualisierung = 'now()';
        Shop::Container()->getDB()->update('tartikel', 'kArtikel', (int)$oArtikel->kArtikel, $upd);
        executeHook(HOOK_QUICKSYNC_XML_BEARBEITEINSERT, ['oArtikel' => $oArtikel]);
        handlePriceRange((int)$oArtikel->kArtikel);
        // clear object cache for this article and its parent if there is any
        $parentArticle = Shop::Container()->getDB()->select(
            'tartikel',
            'kArtikel', $oArtikel->kArtikel,
            null, null,
            null, null,
            false,
            'kVaterArtikel'
        );
        if (!empty($parentArticle->kVaterArtikel)) {
            $clearTags[] = (int)$parentArticle->kVaterArtikel;
        }
        $clearTags[] = (int)$oArtikel->kArtikel;
        versendeVerfuegbarkeitsbenachrichtigung($oArtikel);
    }
    Shop::Cache()->flushTags(\Functional\map(array_unique($clearTags), function ($e) {
        return CACHING_GROUP_ARTICLE . '_' . $e;
    }));
}
