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
        if (($productData = \Shop::Container()->getCache()->get($cacheID)) !== false) {
            $cached      = false;
            $productData = \Shop::Container()->getDB()->query(
                "SELECT tartikel.kArtikel, tartikelext.fDurchschnittsBewertung
                    FROM tartikel
                    JOIN tartikelext ON tartikel.kArtikel = tartikelext.kArtikel
                    WHERE round(fDurchschnittsBewertung) >= " . (int)$config['boxen']['boxen_topbewertet_minsterne'] . "
                    $parentSQL
                    ORDER BY tartikelext.fDurchschnittsBewertung DESC
                    LIMIT " . (int)$config['boxen']['boxen_topbewertet_basisanzahl'],
                ReturnType::ARRAY_OF_OBJECTS
            );
            \Shop::Container()->getCache()->set($cacheID, $productData, $cacheTags);
        }
        if (count($productData) > 0) {
            $defaultOptions = \Artikel::getDefaultOptions();
            $products       = [];
            $max            = (int)$config['boxen']['boxen_topbewertet_anzahl'];
            shuffle($productData);
            foreach ($productData as $i => $item) {
                if ($i >= $max) {
                    break;
                }
                $product = new \Artikel();
                $product->fuelleArtikel((int)$item->kArtikel, $defaultOptions);
                $product->fDurchschnittsBewertung = round($item->fDurchschnittsBewertung * 2) / 2;
                $products[]                       = $product;
            }
            $this->setShow(true);
            $this->setProducts($products);
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
