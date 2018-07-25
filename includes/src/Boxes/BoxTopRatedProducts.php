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
        $this->products = [];
        $parentSQL      = ' AND tartikel.kVaterArtikel = 0';
        $cacheTags      = [CACHING_GROUP_BOX, CACHING_GROUP_ARTICLE];
        $cacheID        = 'bx_tprtd_' . $config['boxen']['boxen_topbewertet_minsterne'] . '_' .
            $config['boxen']['boxen_topbewertet_basisanzahl'] . md5($parentSQL);
        $cached         = true;
        if (($topRated = \Shop::Container()->getCache()->get($cacheID)) === false) {
            $cached   = false;
            $topRated = \Shop::Container()->getDB()->query(
                "SELECT tartikel.kArtikel, tartikelext.fDurchschnittsBewertung
                    FROM tartikel
                    JOIN tartikelext ON tartikel.kArtikel = tartikelext.kArtikel
                    WHERE round(fDurchschnittsBewertung) >= " . (int)$config['boxen']['boxen_topbewertet_minsterne'] . "
                    $parentSQL
                    ORDER BY tartikelext.fDurchschnittsBewertung DESC
                    LIMIT " . (int)$config['boxen']['boxen_topbewertet_basisanzahl'],
                ReturnType::ARRAY_OF_OBJECTS
            );
            \Shop::Container()->getCache()->set($cacheID, $topRated, $cacheTags);
        }
        if (count($topRated) > 0) {
            $productIDs = [];
            foreach ($topRated as $oTopBewertet) {
                $oTopBewertet->kArtikel = (int)$oTopBewertet->kArtikel;
                $productIDs[]           = (int)$oTopBewertet->kArtikel;
            }
            if (count($productIDs) > 0) {
                $max = (int)$config['boxen']['boxen_topbewertet_anzahl'];
                if (count($topRated) < (int)$config['boxen']['boxen_topbewertet_anzahl']) {
                    $max = count($topRated);
                }
                $defaultOptions = \Artikel::getDefaultOptions();
                foreach (array_rand($productIDs, $max) as $i => $id) {
                    $this->products[] = (new \Artikel())->fuelleArtikel($productIDs[$id], $defaultOptions);
                }
                foreach ($topRated as $oTopBewertet) {
                    foreach ($this->products as $j => $oArtikel) {
                        if ($oTopBewertet->kArtikel === $oArtikel->kArtikel) {
                            $this->products[$j]->fDurchschnittsBewertung = round($oTopBewertet->fDurchschnittsBewertung * 2) / 2;
                        }
                    }
                }
            }
            $this->setShow(true);
            $this->setProducts($this->products);
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
