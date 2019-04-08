<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\dbeS\Sync;

use JTL\DB\ReturnType;
use JTL\dbeS\Starter;
use JTL\Shop;
use stdClass;
use function Functional\map;

/**
 * Class QuickSync
 * @package JTL\dbeS\Sync
 */
final class QuickSync extends AbstractSync
{
    /**
     * @param Starter $starter
     * @return mixed|null
     */
    public function handle(Starter $starter)
    {
        $this->db->query('START TRANSACTION', ReturnType::DEFAULT);
        foreach ($starter->getXML() as $i => $item) {
            [$file, $xml] = [\key($item), \reset($item)];
            if (\strpos($file, 'quicksync.xml') !== false) {
                $this->handleInserts($xml);
            }
        }
        $this->db->query('COMMIT', ReturnType::DEFAULT);

        return null;
    }

    /**
     * @param array $xml
     */
    private function handleInserts(array $xml): void
    {
        if (!\is_array($xml['quicksync']['tartikel'])) {
            return;
        }
        $products = $this->mapper->mapArray($xml['quicksync'], 'tartikel', 'mArtikelQuickSync');
        $count    = \count($products);
        if ($count < 2) {
            $this->updateXMLinDB(
                $xml['quicksync']['tartikel'],
                'tpreise',
                'mPreise',
                'kKundengruppe',
                'kArtikel'
            );

            if (isset($xml['quicksync']['tartikel']['tpreis'])) {
                $this->handleNewPriceFormat($xml['quicksync']['tartikel']);
            } else {
                $this->handleOldPriceFormat(
                    $this->mapper->mapArray(
                        $xml['quicksync']['tartikel'],
                        'tpreise',
                        'mPreise'
                    )
                );
            }
            $prices = $this->mapper->mapArray($xml['quicksync']['tartikel'], 'tpreise', 'mPreise');
            foreach ($prices as $price) {
                $this->setzePreisverlauf($price->kArtikel, $price->kKundengruppe, $price->fVKNetto);
            }
        } else {
            for ($i = 0; $i < $count; ++$i) {
                $this->updateXMLinDB(
                    $xml['quicksync']['tartikel'][$i],
                    'tpreise',
                    'mPreise',
                    'kKundengruppe',
                    'kArtikel'
                );
                if (isset($xml['quicksync']['tartikel'][$i]['tpreis'])) {
                    $this->handleNewPriceFormat($xml['quicksync']['tartikel'][$i]);
                } else {
                    $this->handleOldPriceFormat(
                        $this->mapper->mapArray($xml['quicksync']['tartikel'][$i], 'tpreise', 'mPreise')
                    );
                }
                // Preise für Preisverlauf
                $prices = $this->mapper->mapArray($xml['quicksync']['tartikel'][$i], 'tpreise', 'mPreise');
                foreach ($prices as $price) {
                    $this->setzePreisverlauf($price->kArtikel, $price->kKundengruppe, $price->fVKNetto);
                }
            }
        }
        $clearTags = [];
        $conf      = Shop::getSettings([\CONF_ARTIKELDETAILS]);
        foreach ($products as $product) {
            if (isset($product->fLagerbestand) && $product->fLagerbestand > 0) {
                $delta = $this->db->query(
                    "SELECT SUM(pos.nAnzahl) AS totalquantity
                    FROM tbestellung b
                    JOIN twarenkorbpos pos
                    ON pos.kWarenkorb = b.kWarenkorb
                    WHERE b.cAbgeholt = 'N'
                        AND pos.kArtikel = " . (int)$product->kArtikel,
                    ReturnType::SINGLE_OBJECT
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
            $this->db->update('tartikel', 'kArtikel', (int)$product->kArtikel, $upd);
            \executeHook(\HOOK_QUICKSYNC_XML_BEARBEITEINSERT, ['oArtikel' => $product]);
            // clear object cache for this article and its parent if there is any
            $oarentProduct = $this->db->select(
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
            $this->versendeVerfuegbarkeitsbenachrichtigung($product, $conf);
        }
        $this->handlePriceRange($clearTags);
        $this->cache->flushTags(map(\array_unique($clearTags), function ($e) {
            return \CACHING_GROUP_ARTICLE . '_' . $e;
        }));
    }
}
