<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Boxes;

use DB\ReturnType;

/**
 * Class BoxTopRatedProducts
 * @package Boxes
 */
final class BoxTopRatedProducts extends AbstractBox
{
    /**
     * BoxWishlist constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        parent::__construct($config);
        $parentSQL = ' AND tartikel.kVaterArtikel = 0';
        $cacheTags = [CACHING_GROUP_BOX, CACHING_GROUP_ARTICLE];
        $cacheID   = 'bx_tprtd_' . $config['boxen']['boxen_topbewertet_minsterne'] . '_' .
            $config['boxen']['boxen_topbewertet_basisanzahl'] . md5($parentSQL);
        $cached    = true;
        if (($productIDs = \Shop::Container()->getCache()->get($cacheID)) !== false) {
            $cached     = false;
            $productIDs = \Shop::Container()->getDB()->query(
                "SELECT tartikel.kArtikel, tartikelext.fDurchschnittsBewertung
                    FROM tartikel
                    JOIN tartikelext ON tartikel.kArtikel = tartikelext.kArtikel
                    WHERE round(fDurchschnittsBewertung) >= " . (int)$config['boxen']['boxen_topbewertet_minsterne'] . "
                    $parentSQL
                    ORDER BY tartikelext.fDurchschnittsBewertung DESC
                    LIMIT " . (int)$config['boxen']['boxen_topbewertet_basisanzahl'],
                ReturnType::ARRAY_OF_OBJECTS
            );
            \Shop::Container()->getCache()->set($cacheID, $productIDs, $cacheTags);
        }
        if (count($productIDs) > 0) {
            $kArtikel_arr = [];
            $oArtikel_arr = [];
            // Alle kArtikels aus der DB Menge in ein Array speichern
            foreach ($productIDs as $oTopBewertet) {
                $oTopBewertet->kArtikel = (int)$oTopBewertet->kArtikel;
                $kArtikel_arr[]         = (int)$oTopBewertet->kArtikel;
            }
            // Wenn das Array Elemente besitzt
            if (count($kArtikel_arr) > 0) {
                // Gib mir X viele Random Keys
                $nAnzahlKeys = (int)$config['boxen']['boxen_topbewertet_anzahl'];
                if (count($productIDs) < (int)$config['boxen']['boxen_topbewertet_anzahl']) {
                    $nAnzahlKeys = count($productIDs);
                }
                $kKey_arr = array_rand($kArtikel_arr, $nAnzahlKeys);
                if (is_array($kKey_arr) && count($kKey_arr) > 0) {
                    // Lauf die Keys durch und hole baue Artikelobjekte
                    $defaultOptions = \Artikel::getDefaultOptions();
                    foreach ($kKey_arr as $i => $kKey) {
                        $oArtikel_arr[] = (new \Artikel())->fuelleArtikel($kArtikel_arr[$kKey], $defaultOptions);
                    }
                }
                // Laufe die DB Menge durch und assigne zu jedem Artikelobjekt noch die Durchschnittsbewertung
                foreach ($productIDs as $oTopBewertet) {
                    foreach ($oArtikel_arr as $j => $oArtikel) {
                        if ($oTopBewertet->kArtikel === $oArtikel->kArtikel) {
                            $oArtikel_arr[$j]->fDurchschnittsBewertung = round($oTopBewertet->fDurchschnittsBewertung * 2) / 2;
                        }
                    }
                }
            }
            $this->setShow(true);
            $this->setProducts($oArtikel_arr);
            $this->setURL(\SearchSpecialHelper::buildURL(SEARCHSPECIALS_TOPREVIEWS));

            executeHook(HOOK_BOXEN_INC_TOPBEWERTET, [
                'box'        => &$this,
                'cache_tags' => &$cacheTags,
                'cached'     => $cached
            ]);
        } else {
            $this->setShow(false);
        }
    }
}
