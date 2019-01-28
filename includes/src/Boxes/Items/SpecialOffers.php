<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Boxes\Items;

use DB\ReturnType;
use Helpers\SearchSpecial;
use Session\Frontend;

/**
 * Class SpecialOffers
 * @package Boxes\Items
 */
final class SpecialOffers extends AbstractBox
{
    /**
     * SpecialOffers constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        parent::__construct($config);
        $this->setShow(false);
        $customerGroupID = Frontend::getCustomerGroup()->getID();
        if ($customerGroupID && Frontend::getCustomerGroup()->mayViewCategories()) {
            $cached         = true;
            $stockFilterSQL = \Shop::getProductFilter()->getFilterSQL()->getStockFilterSQL();
            $parentSQL      = ' AND tartikel.kVaterArtikel = 0';
            $limit          = $config['boxen']['box_sonderangebote_anzahl_anzeige'];
            $cacheTags      = [\CACHING_GROUP_BOX, \CACHING_GROUP_ARTICLE];
            $cacheID        = 'box_special_offer_' . $customerGroupID . '_' .
                $limit . \md5($stockFilterSQL . $parentSQL);
            if (($productIDs = \Shop::Container()->getCache()->get($cacheID)) === false) {
                $cached     = false;
                $productIDs = \Shop::Container()->getDB()->queryPrepared(
                    "SELECT tartikel.kArtikel
                        FROM tartikel
                        JOIN tartikelsonderpreis 
                            ON tartikelsonderpreis.kArtikel = tartikel.kArtikel
                        JOIN tsonderpreise 
                            ON tsonderpreise.kArtikelSonderpreis = tartikelsonderpreis.kArtikelSonderpreis
                        LEFT JOIN tartikelsichtbarkeit 
                            ON tartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                            AND tartikelsichtbarkeit.kKundengruppe = :cgid
                        WHERE tartikelsichtbarkeit.kArtikel IS NULL
                            AND tartikelsonderpreis.kArtikel = tartikel.kArtikel
                            AND tsonderpreise.kKundengruppe = :cgid
                            AND tartikelsonderpreis.cAktiv = 'Y'
                            AND tartikelsonderpreis.dStart <= NOW()
                            AND (tartikelsonderpreis.dEnde IS NULL OR tartikelsonderpreis.dEnde >= CURDATE()) " .
                            $stockFilterSQL . $parentSQL . '
                        ORDER BY rand() LIMIT :lmt',
                    ['lmt' => $limit, 'cgid' => $customerGroupID],
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
                $this->setURL(SearchSpecial::buildURL(\SEARCHSPECIALS_SPECIALOFFERS));
                \executeHook(\HOOK_BOXEN_INC_SONDERANGEBOTE, [
                    'box'        => &$this,
                    'cache_tags' => &$cacheTags,
                    'cached'     => $cached
                ]);
            }
        }
    }
}
