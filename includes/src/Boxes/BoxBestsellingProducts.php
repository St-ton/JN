<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Boxes;

use DB\ReturnType;

/**
 * Class BoxBestsellingProducts
 * @package Boxes
 */
final class BoxBestsellingProducts extends AbstractBox
{
    /**
     * BoxCompareList constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        parent::__construct($config);
        $this->setShow(false);
        $customerGroupID = \Session::CustomerGroup()->getID();
        if ($customerGroupID && \Session::CustomerGroup()->mayViewCategories()) {
            $kArtikel_arr   = [];
            $cached         = true;
            $cacheTags      = [\CACHING_GROUP_BOX, \CACHING_GROUP_ARTICLE];
            $stockFilterSQL = \Shop::getProductFilter()->getFilterSQL()->getStockFilterSQL();
            $parentSQL      = ' AND tartikel.kVaterArtikel = 0';
            $anzahl         = (int)$this->config['boxen']['box_bestseller_anzahl_anzeige'];
            $cacheID        = 'bx_bstsl_' . $customerGroupID . '_' . \md5($parentSQL . $stockFilterSQL);
            if (($productIDs = \Shop::Container()->getCache()->get($cacheID)) === false) {
                $cached   = false;
                $minCount = ((int)$this->config['global']['global_bestseller_minanzahl'] > 0)
                    ? (int)$this->config['global']['global_bestseller_minanzahl']
                    : 100;
                $limit    = (int)$this->config['boxen']['box_bestseller_anzahl_basis'];
                if ($limit < 1) {
                    $limit = 10;
                }
                $productIDs = \Shop::Container()->getDB()->query(
                    "SELECT tartikel.kArtikel
                        FROM tbestseller, tartikel
                        LEFT JOIN tartikelsichtbarkeit 
                            ON tartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                            AND tartikelsichtbarkeit.kKundengruppe = $customerGroupID
                        WHERE tartikelsichtbarkeit.kArtikel IS NULL
                            AND tbestseller.kArtikel = tartikel.kArtikel
                            AND round(tbestseller.fAnzahl) >= " . $minCount . "
                            $parentSQL
                            $stockFilterSQL
                        ORDER BY fAnzahl DESC LIMIT " . $limit,
                    ReturnType::ARRAY_OF_OBJECTS
                );
                \Shop::Container()->getCache()->set($cacheID, $productIDs, $cacheTags);
            }
            if (\count($productIDs) > 0) {
                $rndkeys = \array_rand($productIDs, \min($anzahl, \count($productIDs)));
                if (\is_array($rndkeys)) {
                    foreach ($rndkeys as $key) {
                        if (isset($productIDs[$key]->kArtikel) && $productIDs[$key]->kArtikel > 0) {
                            $kArtikel_arr[] = (int)$productIDs[$key]->kArtikel;
                        }
                    }
                } elseif (\is_int($rndkeys)) {
                    if (isset($productIDs[$rndkeys]->kArtikel) && $productIDs[$rndkeys]->kArtikel > 0) {
                        $kArtikel_arr[] = (int)$productIDs[$rndkeys]->kArtikel;
                    }
                }
            }

            if (\count($kArtikel_arr) > 0) {
                $this->setShow(true);
                $products = new \ArtikelListe();
                $products->getArtikelByKeys($kArtikel_arr, 0, \count($kArtikel_arr));
                $this->setProducts($products);
                $this->setURL(\SearchSpecialHelper::buildURL(\SEARCHSPECIALS_BESTSELLER));
            }

            \executeHook(\HOOK_BOXEN_INC_BESTSELLER, [
                'box'        => &$this,
                'cache_tags' => &$cacheTags,
                'cached'     => $cached
            ]);
        }
    }
}
