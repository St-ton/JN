<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Boxes\Items;

use DB\ReturnType;

/**
 * Class NewsCategories
 * @package Boxes
 */
final class NewsCategories extends AbstractBox
{
    /**
     * Wishlist constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        parent::__construct($config);
        parent::addMapping('oNewsKategorie_arr', 'Items');
        $cSQL      = (int)$config['news']['news_anzahl_box'] > 0
            ? ' LIMIT ' . (int)$config['news']['news_anzahl_box']
            : '';
        $langID    = \Shop::getLanguageID();
        $cacheID   = 'bnk_' . $langID . '_' . \Session\Session::getCustomerGroup()->getID() . '_' . \md5($cSQL);
        $cached    = true;
        $cacheTags = [\CACHING_GROUP_BOX, \CACHING_GROUP_NEWS];
        if (($newsCategories = \Shop::Container()->getCache()->get($cacheID)) === false) {
            $cached         = false;
            $newsCategories = \Shop::Container()->getDB()->queryPrepared(
                "SELECT tnewskategorie.kNewsKategorie, t.languageID AS kSprache, t.name AS cName,
                    t.description AS cBeschreibung, t.metaTitle AS cMetaTitle, t.metaDescription AS cMetaDescription,
                    tnewskategorie.nSort, tnewskategorie.nAktiv, tnewskategorie.dLetzteAktualisierung,
                    tnewskategorie.cPreviewImage, tseo.cSeo,
                    COUNT(DISTINCT(tnewskategorienews.kNews)) AS nAnzahlNews
                    FROM tnewskategorie
                    JOIN tnewskategoriesprache t 
                        ON tnewskategorie.kNewsKategorie = t.kNewsKategorie
                    LEFT JOIN tnewskategorienews 
                        ON tnewskategorienews.kNewsKategorie = tnewskategorie.kNewsKategorie
                    LEFT JOIN tnews 
                        ON tnews.kNews = tnewskategorienews.kNews
                    LEFT JOIN tseo 
                        ON tseo.cKey = 'kNewsKategorie'
                        AND tseo.kKey = tnewskategorie.kNewsKategorie
                        AND tseo.kSprache = :lid
                    WHERE t.languageID = :lid
                        AND tnewskategorie.nAktiv = 1
                        AND tnews.nAktiv = 1
                        AND tnews.dGueltigVon <= NOW()
                        AND (tnews.cKundengruppe LIKE '%;-1;%' 
                            OR FIND_IN_SET(':cid', REPLACE(tnews.cKundengruppe, ';', ',')) > 0)
                        AND t.languageID = :lid
                    GROUP BY tnewskategorienews.kNewsKategorie
                    ORDER BY tnewskategorie.nSort DESC" . $cSQL,
                ['lid' => $langID, 'cid' => \Session\Session::getCustomerGroup()->getID()],
                ReturnType::ARRAY_OF_OBJECTS
            );
            \Shop::Container()->getCache()->set($cacheID, $newsCategories, $cacheTags);
        }
        foreach ($newsCategories as $newsCategory) {
            $newsCategory->cURL     = \UrlHelper::buildURL($newsCategory, \URLART_NEWSKATEGORIE);
            $newsCategory->cURLFull = \UrlHelper::buildURL($newsCategory, \URLART_NEWSKATEGORIE, true);
        }
        $this->setShow(\count($newsCategories) > 0);
        $this->setItems($newsCategories);
        \executeHook(\HOOK_BOXEN_INC_NEWSKATEGORIE, [
            'box'        => &$this,
            'cache_tags' => &$cacheTags,
            'cached'     => $cached
        ]);
    }
}
