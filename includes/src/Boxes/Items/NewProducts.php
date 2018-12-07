<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Boxes\Items;

use DB\ReturnType;
use Session\Session;

/**
 * Class NewProducts
 * @package Boxes\Items
 */
final class NewProducts extends AbstractBox
{
    /**
     * NewProducts constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        parent::__construct($config);
        $this->setShow(false);
        $customerGroupID = Session::getCustomerGroup()->getID();
        if ($customerGroupID && Session::getCustomerGroup()->mayViewCategories()) {
            $cacheTags      = [\CACHING_GROUP_BOX, \CACHING_GROUP_ARTICLE];
            $cached         = true;
            $stockFilterSQL = \Shop::getProductFilter()->getFilterSQL()->getStockFilterSQL();
            $parentSQL      = ' AND tartikel.kVaterArtikel = 0';
            $limit          = $config['boxen']['box_neuimsortiment_anzahl_anzeige'];
            $days           = $config['boxen']['box_neuimsortiment_alter_tage'] > 0
                ? (int)$config['boxen']['box_neuimsortiment_alter_tage']
                : 30;
            $cacheID        = 'bx_nw_' . $customerGroupID .
                '_' . $days . '_' .
                $limit . \md5($stockFilterSQL . $parentSQL);
            if (($productIDs = \Shop::Container()->getCache()->get($cacheID)) === false) {
                $cached     = false;
                $productIDs = \Shop::Container()->getDB()->query(
                    "SELECT tartikel.kArtikel
                        FROM tartikel
                        LEFT JOIN tartikelsichtbarkeit 
                            ON tartikel.kArtikel=tartikelsichtbarkeit.kArtikel
                            AND tartikelsichtbarkeit.kKundengruppe = $customerGroupID
                        WHERE tartikelsichtbarkeit.kArtikel IS NULL
                            AND tartikel.cNeu = 'Y' " . $stockFilterSQL . $parentSQL . "
                            AND cNeu = 'Y' 
                            AND DATE_SUB(NOW(), INTERVAL " . $days . " DAY) < dErstellt
                        ORDER BY RAND() LIMIT " . $limit,
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
                $this->setURL(\SearchSpecialHelper::buildURL(\SEARCHSPECIALS_NEWPRODUCTS));
                \executeHook(\HOOK_BOXEN_INC_NEUIMSORTIMENT, [
                    'box'        => &$this,
                    'cache_tags' => &$cacheTags,
                    'cached'     => $cached
                ]);
            }
        }
    }
}
