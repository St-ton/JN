<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Boxes\Items;

use DB\ReturnType;
use Helpers\SearchSpecial;

/**
 * Class TopRatedProducts
 * @package Boxes\Items
 */
final class TopRatedProducts extends AbstractBox
{
    /**
     * TopRatedProducts constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        parent::__construct($config);
        $this->products = [];
        $parentSQL      = ' AND tartikel.kVaterArtikel = 0';
        $cacheTags      = [\CACHING_GROUP_BOX, \CACHING_GROUP_ARTICLE];
        $cacheID        = 'bx_tprtd_' . $config['boxen']['boxen_topbewertet_minsterne'] . '_' .
            $config['boxen']['boxen_topbewertet_basisanzahl'] . \md5($parentSQL);
        $cached         = true;
        if (($topRated = \Shop::Container()->getCache()->get($cacheID)) === false) {
            $cached   = false;
            $topRated = \Shop::Container()->getDB()->query(
                'SELECT tartikel.kArtikel, tartikelext.fDurchschnittsBewertung
                    FROM tartikel
                    JOIN tartikelext 
                        ON tartikel.kArtikel = tartikelext.kArtikel
                    WHERE ROUND(fDurchschnittsBewertung) >= ' . (int)$config['boxen']['boxen_topbewertet_minsterne'] .
                    ' ' . $parentSQL . ' ORDER BY tartikelext.fDurchschnittsBewertung DESC
                    LIMIT ' . (int)$config['boxen']['boxen_topbewertet_basisanzahl'],
                ReturnType::ARRAY_OF_OBJECTS
            );
            \Shop::Container()->getCache()->set($cacheID, $topRated, $cacheTags);
        }
        if (\count($topRated) > 0) {
            $productIDs = [];
            foreach ($topRated as $product) {
                $product->kArtikel = (int)$product->kArtikel;
                $productIDs[]      = (int)$product->kArtikel;
            }
            if (\count($productIDs) > 0) {
                $max = (int)$config['boxen']['boxen_topbewertet_anzahl'];
                if (\count($topRated) < (int)$config['boxen']['boxen_topbewertet_anzahl']) {
                    $max = \count($topRated);
                }
                $defaultOptions = \Artikel::getDefaultOptions();
                foreach (\array_rand($productIDs, $max) as $id) {
                    $this->products[] = (new \Artikel())->fuelleArtikel($productIDs[$id], $defaultOptions);
                }
                foreach ($topRated as $product) {
                    foreach ($this->products as $j => $oArtikel) {
                        if ($product->kArtikel === $oArtikel->kArtikel) {
                            $this->products[$j]->fDurchschnittsBewertung =
                                \round($product->fDurchschnittsBewertung * 2) / 2;
                        }
                    }
                }
            }
            $this->setShow(true);
            $this->setProducts($this->products);
            $this->setURL(SearchSpecial::buildURL(\SEARCHSPECIALS_TOPREVIEWS));

            \executeHook(\HOOK_BOXEN_INC_TOPBEWERTET, [
                'box'        => &$this,
                'cache_tags' => &$cacheTags,
                'cached'     => $cached
            ]);
        } else {
            $this->setShow(false);
        }
    }
}
