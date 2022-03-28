<?php declare(strict_types=1);

namespace JTL\Boxes\Items;

use JTL\Catalog\Product\ArtikelListe;
use JTL\Catalog\Product\Bestseller;
use JTL\Helpers\SearchSpecial;
use JTL\Session\Frontend;
use JTL\Shop;

/**
 * Class BestsellingProducts
 * @package JTL\Boxes\Items
 */
final class BestsellingProducts extends AbstractBox
{
    /**
     * BestsellingProducts constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        parent::__construct($config);
        $this->setShow(false);
        $customerGroupID = Frontend::getCustomerGroup()->getID();
        if ($customerGroupID && Frontend::getCustomerGroup()->mayViewCategories()) {
            $cached         = true;
            $cacheTags      = [\CACHING_GROUP_BOX, \CACHING_GROUP_ARTICLE];
            $stockFilterSQL = Shop::getProductFilter()->getFilterSQL()->getStockFilterSQL();
            $parentSQL      = ' AND tartikel.kVaterArtikel = 0';
            $cacheID        = 'bx_bsp_' . $customerGroupID . '_' . \md5($parentSQL . $stockFilterSQL);
            if (($productIDs = Shop::Container()->getCache()->get($cacheID)) === false) {
                $cached     = false;
                $minCount   = (int)$this->config['global']['global_bestseller_minanzahl'] > 0
                    ? (int)$this->config['global']['global_bestseller_minanzahl']
                    : 100;
                $limit      = (int)$this->config['boxen']['box_bestseller_anzahl_basis'] > 0
                    ? (int)$this->config['boxen']['box_bestseller_anzahl_basis']
                    : 10;
                $productIDs = (new Bestseller([
                    'Customergroup' => $customerGroupID,
                    'Limit'         => $limit,
                    'MinSales'      => $minCount
                ]))->fetch($parentSQL, false);

                Shop::Container()->getCache()->set($cacheID, $productIDs, $cacheTags);
            }
            \shuffle($productIDs);
            $res = \array_slice($productIDs, 0, $this->config['boxen']['box_bestseller_anzahl_anzeige']);

            if (\count($res) > 0) {
                $this->setShow(true);
                $products = new ArtikelListe();
                $products->getArtikelByKeys($res, 0, \count($res));
                $this->setProducts($products);
                $this->setURL(SearchSpecial::buildURL(\SEARCHSPECIALS_BESTSELLER));
            }

            \executeHook(\HOOK_BOXEN_INC_BESTSELLER, [
                'box'        => &$this,
                'cache_tags' => &$cacheTags,
                'cached'     => $cached
            ]);
        }
    }
}
