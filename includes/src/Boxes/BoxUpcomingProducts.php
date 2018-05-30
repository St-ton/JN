<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Boxes;

use DB\ReturnType;

/**
 * Class BoxUpcomingProducts
 * @package Boxes
 */
final class BoxUpcomingProducts extends AbstractBox
{
    /**
     * BoxCart constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        parent::__construct($config);
        $this->setShow(false);
        $customerGroupID = \Session::CustomerGroup()->getID();
        if ($customerGroupID > 0 && \Session::CustomerGroup()->mayViewCategories()) {
            $cached         = true;
            $cacheTags      = [CACHING_GROUP_BOX, CACHING_GROUP_ARTICLE];
            $stockFilterSQL = \Shop::getProductFilter()->getFilterSQL()->getStockFilterSQL();
            $parentSQL      = ' AND tartikel.kVaterArtikel = 0';
            $limit          = (int)$config['boxen']['box_erscheinende_anzahl_anzeige'];
            $cacheID        = 'box_ikv_' . $customerGroupID . '_' . $limit . md5($stockFilterSQL . $parentSQL);
            if (($productIDs = \Shop::Container()->getCache()->get($cacheID)) !== false) {
                $productIDs = \Shop::Container()->getDB()->queryPrepared(
                    "SELECT tartikel.kArtikel
                        FROM tartikel
                        LEFT JOIN tartikelsichtbarkeit 
                            ON tartikel.kArtikel=tartikelsichtbarkeit.kArtikel
                            AND tartikelsichtbarkeit.kKundengruppe = :cid
                        WHERE tartikelsichtbarkeit.kArtikel IS NULL
                            $stockFilterSQL
                            $parentSQL
                            AND now() < tartikel.dErscheinungsdatum
                        ORDER BY rand() LIMIT :lmt",
                    ['cid' => $customerGroupID, 'lmt' => $limit],
                    ReturnType::ARRAY_OF_OBJECTS
                );
                $productIDs = array_map(function ($e) {
                    return (int)$e->kArtikel;
                }, $productIDs);
                \Shop::Container()->getCache()->set($cacheID, $productIDs, $cacheTags);
            }

            if (count($productIDs) > 0) {
                $this->setShow(true);
                $products = new \ArtikelListe();
                $products->getArtikelByKeys($productIDs, 0, count($productIDs));
                $this->setProducts($products);
                $this->setURL(baueSuchSpecialURL(SEARCHSPECIALS_UPCOMINGPRODUCTS));
                executeHook(HOOK_BOXEN_INC_ERSCHEINENDEPRODUKTE, [
                    'box'        => &$this,
                    'cache_tags' => &$cacheTags,
                    'cached'     => $cached
                ]);
            }
        }
    }
}
