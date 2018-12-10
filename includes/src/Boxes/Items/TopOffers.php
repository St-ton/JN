<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Boxes\Items;

use DB\ReturnType;
use Session\Session;

/**
 * Class TopOffers
 * @package Boxes\Items
 */
final class TopOffers extends AbstractBox
{
    /**
     * TopOffers constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        parent::__construct($config);
        $this->setShow(false);
        $customerGroupID = Session::getCustomerGroup()->getID();
        if ($customerGroupID > 0 && Session::getCustomerGroup()->mayViewCategories()) {
            $cacheTags      = [\CACHING_GROUP_BOX, \CACHING_GROUP_ARTICLE];
            $cached         = true;
            $limit          = $config['boxen']['box_topangebot_anzahl_anzeige'];
            $stockFilterSQL = \Shop::getProductFilter()->getFilterSQL()->getStockFilterSQL();
            $parentSQL      = ' AND tartikel.kVaterArtikel = 0';
            $cacheID        = 'box_top_offer_' . $customerGroupID . '_' .
                $limit . \md5($stockFilterSQL . $parentSQL);
            if (($productIDs = \Shop::Container()->getCache()->get($cacheID)) === false) {
                $cached     = false;
                $productIDs = \Shop::Container()->getDB()->queryPrepared(
                    "SELECT tartikel.kArtikel
                        FROM tartikel
                        LEFT JOIN tartikelsichtbarkeit 
                            ON tartikel.kArtikel=tartikelsichtbarkeit.kArtikel
                            AND tartikelsichtbarkeit.kKundengruppe = :cid
                        WHERE tartikelsichtbarkeit.kArtikel IS NULL
                            AND tartikel.cTopArtikel = 'Y' " .
                        $stockFilterSQL .
                        $parentSQL . '
                        ORDER BY RAND() LIMIT ' . $limit,
                    ['cid' => $customerGroupID],
                    ReturnType::ARRAY_OF_OBJECTS
                );
                $productIDs = \array_map(function ($e) {
                    return (int)$e->kArtikel;
                }, $productIDs);
                \Shop::Container()->getCache()->set($cacheID, $productIDs, $cacheTags);
            }
            if (\count($productIDs) > 0) {
                $this->setShow(true);
                $products = new \ArtikelListe();
                $products->getArtikelByKeys($productIDs, 0, \count($productIDs));
                $this->setProducts($products);
                $this->setURL(\SearchSpecialHelper::buildURL(\SEARCHSPECIALS_TOPOFFERS));
                \executeHook(\HOOK_BOXEN_INC_TOPANGEBOTE, [
                    'box'        => &$this,
                    'cache_tags' => &$cacheTags,
                    'cached'     => $cached
                ]);
            }
        }
    }
}
