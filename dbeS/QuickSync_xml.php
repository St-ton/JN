<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use dbeS\TableMapper as Mapper;

require_once __DIR__ . '/syncinclude.php';

$return  = 3;
$zipFile = $_FILES['data']['tmp_name'];
if (auth()) {
    $zipFile   = checkFile();
    $return    = 2;
    $unzipPath = PFAD_ROOT . PFAD_DBES . PFAD_SYNC_TMP . basename($zipFile) . '_' . date('dhis') . '/';
    $db        = Shop::Container()->getDB();
    if (($syncFiles = unzipSyncFiles($zipFile, $unzipPath, __FILE__)) === false) {
        Shop::Container()->getLogService()->error('Error: Cannot extract zip file ' . $zipFile . ' to ' . $unzipPath);
        removeTemporaryFiles($zipFile);
    } else {
        $return = 0;
        $db->query('START TRANSACTION', \DB\ReturnType::DEFAULT);
        foreach ($syncFiles as $i => $xmlFile) {
            $d   = file_get_contents($xmlFile);
            $xml = XML_unserialize($d);

            if (strpos($xmlFile, 'quicksync.xml') !== false) {
                bearbeiteInsert($xml);
            }

            removeTemporaryFiles($xmlFile);
        }
        $db->query('COMMIT', \DB\ReturnType::DEFAULT);
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
    $products = mapArray($xml['quicksync'], 'tartikel', Mapper::getMapping('mArtikelQuickSync'));
    $count    = count($products);
    if ($count < 2) {
        updateXMLinDB(
            $xml['quicksync']['tartikel'],
            'tpreise',
            Mapper::getMapping('mPreise'),
            'kKundengruppe',
            'kArtikel'
        );

        if (isset($xml['quicksync']['tartikel']['tpreis'])) {
            handleNewPriceFormat($xml['quicksync']['tartikel']);
        } else {
            handleOldPriceFormat(mapArray($xml['quicksync']['tartikel'], 'tpreise', Mapper::getMapping('mPreise')));
        }
        $prices = mapArray($xml['quicksync']['tartikel'], 'tpreise', Mapper::getMapping('mPreise'));
        foreach ($prices as $price) {
            setzePreisverlauf($price->kArtikel, $price->kKundengruppe, $price->fVKNetto);
        }
    } else {
        for ($i = 0; $i < $count; ++$i) {
            updateXMLinDB(
                $xml['quicksync']['tartikel'][$i],
                'tpreise',
                Mapper::getMapping('mPreise'),
                'kKundengruppe',
                'kArtikel'
            );
            if (isset($xml['quicksync']['tartikel'][$i]['tpreis'])) {
                handleNewPriceFormat($xml['quicksync']['tartikel'][$i]);
            } else {
                handleOldPriceFormat(
                    mapArray($xml['quicksync']['tartikel'][$i], 'tpreise', Mapper::getMapping('mPreise'))
                );
            }
            // Preise für Preisverlauf
            $prices = mapArray($xml['quicksync']['tartikel'][$i], 'tpreise', Mapper::getMapping('mPreise'));
            foreach ($prices as $price) {
                setzePreisverlauf($price->kArtikel, $price->kKundengruppe, $price->fVKNetto);
            }
        }
    }
    $db        = Shop::Container()->getDB();
    $clearTags = [];
    foreach ($products as $product) {
        if (isset($product->fLagerbestand) && $product->fLagerbestand > 0) {
            $delta = $db->query(
                "SELECT SUM(pos.nAnzahl) AS totalquantity
                    FROM tbestellung b
                    JOIN twarenkorbpos pos
                    ON pos.kWarenkorb = b.kWarenkorb
                    WHERE b.cAbgeholt = 'N'
                        AND pos.kArtikel = " . (int)$product->kArtikel,
                \DB\ReturnType::SINGLE_OBJECT
            );
            if ($delta->totalquantity > 0) {
                $product->fLagerbestand -= $delta->totalquantity;
            }
        }

        if ($product->fLagerbestand < 0) {
            $product->fLagerbestand = 0;
        }

        $upd                        = new stdClass();
        $upd->fLagerbestand         = $product->fLagerbestand;
        $upd->fStandardpreisNetto   = $product->fStandardpreisNetto;
        $upd->dLetzteAktualisierung = 'NOW()';
        $db->update('tartikel', 'kArtikel', (int)$product->kArtikel, $upd);
        executeHook(HOOK_QUICKSYNC_XML_BEARBEITEINSERT, ['oArtikel' => $product]);
        // clear object cache for this article and its parent if there is any
        $oarentProduct = $db->select(
            'tartikel',
            'kArtikel',
            $product->kArtikel,
            null,
            null,
            null,
            null,
            false,
            'kVaterArtikel'
        );
        if (!empty($oarentProduct->kVaterArtikel)) {
            $clearTags[] = (int)$oarentProduct->kVaterArtikel;
        }
        $clearTags[] = (int)$product->kArtikel;
        versendeVerfuegbarkeitsbenachrichtigung($product);
    }
    handlePriceRange($clearTags);
    Shop::Container()->getCache()->flushTags(\Functional\map(array_unique($clearTags), function ($e) {
        return CACHING_GROUP_ARTICLE . '_' . $e;
    }));
}
