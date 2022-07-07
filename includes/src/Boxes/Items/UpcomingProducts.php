<?php declare(strict_types=1);

namespace JTL\Boxes\Items;

use JTL\Catalog\Product\ArtikelListe;
use JTL\Helpers\SearchSpecial;
use JTL\Session\Frontend;
use JTL\Shop;

/**
 * Class UpcomingProducts
 * @package JTL\Boxes\Items
 */
final class UpcomingProducts extends AbstractBox
{
    /**
     * UpcomingProducts constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        parent::__construct($config);
        $this->setShow(false);
        $customerGroupID = Frontend::getCustomerGroup()->getID();
        if ($customerGroupID > 0 && Frontend::getCustomerGroup()->mayViewCategories()) {
            $cached         = true;
            $cacheTags      = [\CACHING_GROUP_BOX, \CACHING_GROUP_ARTICLE];
            $stockFilterSQL = Shop::getProductFilter()->getFilterSQL()->getStockFilterSQL();
            $parentSQL      = ' AND tartikel.kVaterArtikel = 0';
            $limit          = (int)$config['boxen']['box_erscheinende_anzahl_basis'];
            $cacheID        = 'box_ucp_' . $customerGroupID . '_' . $limit . \md5($stockFilterSQL . $parentSQL);
            if (($productIDs = Shop::Container()->getCache()->get($cacheID)) === false) {
                $cached     = false;
                $productIDs = Shop::Container()->getDB()->getInts(
                    'SELECT tartikel.kArtikel
                        FROM tartikel
                        LEFT JOIN tartikelsichtbarkeit 
                            ON tartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                            AND tartikelsichtbarkeit.kKundengruppe = :cid
                        WHERE tartikelsichtbarkeit.kArtikel IS NULL ' .
                            $stockFilterSQL . ' ' .
                            $parentSQL . '
                            AND NOW() < tartikel.dErscheinungsdatum
                        LIMIT :lmt',
                    'kArtikel',
                    ['cid' => $customerGroupID, 'lmt' => $limit]
                );
                Shop::Container()->getCache()->set($cacheID, $productIDs, $cacheTags);
            }
            \shuffle($productIDs);
            $res = \array_slice($productIDs, 0, $config['boxen']['box_erscheinende_anzahl_anzeige']);
            if (\count($res) > 0) {
                $this->setShow(true);
                $products = new ArtikelListe();
                $products->getArtikelByKeys($res, 0, \count($res));
                $this->setProducts($products);
                $this->setURL(SearchSpecial::buildURL(\SEARCHSPECIALS_UPCOMINGPRODUCTS));
                \executeHook(\HOOK_BOXEN_INC_ERSCHEINENDEPRODUKTE, [
                    'box'        => &$this,
                    'cache_tags' => &$cacheTags,
                    'cached'     => $cached
                ]);
            }
        }
    }
}
