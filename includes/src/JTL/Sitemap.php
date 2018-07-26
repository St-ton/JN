<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL;


use Cache\JTLCacheInterface;
use DB\DbInterface;
use Session\Session;

/**
 * Class Sitemap
 * @package JTL
 */
class Sitemap
{
    /**
     * @var DbInterface
     */
    private $db;

    /**
     * @var array
     */
    private $conf;

    /**
     * @var JTLCacheInterface
     */
    private $cache;

    /**
     * @var int
     */
    private $langID;

    /**
     * @var int
     */
    private $customerGroupID;

    /**
     * Sitemap constructor.
     * @param DbInterface       $db
     * @param JTLCacheInterface $cache
     * @param array             $conf
     */
    public function __construct(DbInterface $db, JTLCacheInterface $cache, array $conf)
    {
        $this->db              = $db;
        $this->cache           = $cache;
        $this->conf            = $conf;
        $this->langID          = \Shop::getLanguageID();
        $this->customerGroupID = Session::CustomerGroup()->getID();
    }

    /**
     * @param \JTLSmarty $smarty
     */
    public function assignData(\JTLSmarty $smarty)
    {
        $smarty->assign('oKategorieliste', $this->getCategories())
               ->assign('oGlobaleMerkmale_arr', $this->getGlobalAttributes())
               ->assign('oHersteller_arr', $this->getManufacturers())
               ->assign('oNewsMonatsUebersicht_arr', $this->getNews())
               ->assign('oNewsKategorie_arr', $this->getNewsCategories());
    }

    /**
     * @return \KategorieListe
     */
    private function getCategories(): \KategorieListe
    {
        $catList           = new \KategorieListe();
        $catList->elemente = $this->conf['sitemap']['sitemap_kategorien_anzeigen'] === 'Y'
            ? \KategorieHelper::getInstance()->combinedGetAll()
            : [];

        return $catList;
    }

    /**
     * @return array
     */
    public function getNewsCategories(): array
    {
        if ($this->conf['sitemap']['sitemap_newskategorien_anzeigen'] !== 'Y') {
            return [];
        }
        $cacheID = 'news_category_' . $this->langID . '_' . $this->customerGroupID;
        if (($newsCategories = $this->cache->get($cacheID)) === false) {
            $newsCategories = $this->db->queryPrepared(
                "SELECT tnewskategorie.kNewsKategorie, tnewskategorie.kSprache, tnewskategorie.cName,
                tnewskategorie.cBeschreibung, tnewskategorie.cMetaTitle, tnewskategorie.cMetaDescription,
                tnewskategorie.nSort, tnewskategorie.nAktiv, tnewskategorie.dLetzteAktualisierung, 
                tnewskategorie.cPreviewImage, tseo.cSeo,
                COUNT(DISTINCT(tnewskategorienews.kNews)) AS nAnzahlNews
                    FROM tnewskategorie
                    LEFT JOIN tnewskategorienews 
                        ON tnewskategorienews.kNewsKategorie = tnewskategorie.kNewsKategorie
                    LEFT JOIN tnews 
                        ON tnews.kNews = tnewskategorienews.kNews
                    LEFT JOIN tseo 
                        ON tseo.cKey = 'kNewsKategorie'
                        AND tseo.kKey = tnewskategorie.kNewsKategorie
                        AND tseo.kSprache = :lid
                    WHERE tnewskategorie.kSprache = :lid
                        AND tnewskategorie.nAktiv = 1
                        AND tnews.nAktiv = 1
                        AND tnews.dGueltigVon <= now()
                        AND (tnews.cKundengruppe LIKE '%;-1;%' 
                            OR FIND_IN_SET(:cgid, REPLACE(tnews.cKundengruppe, ';', ',')) > 0)
                    GROUP BY tnewskategorienews.kNewsKategorie
                    ORDER BY tnewskategorie.nSort DESC",
                [
                    'lid'  => $this->langID,
                    'cgid' => $this->customerGroupID
                ],
                \DB\ReturnType::ARRAY_OF_OBJECTS
            );
            foreach ($newsCategories as $newsCategory) {
                $newsCategory->cURL     = \UrlHelper::buildURL($newsCategory, URLART_NEWSKATEGORIE);
                $newsCategory->cURLFull = \UrlHelper::buildURL($newsCategory, URLART_NEWSKATEGORIE, true);

                $entries = $this->db->queryPrepared(
                    "SELECT tnews.kNews, tnews.kSprache, tnews.cKundengruppe, tnews.cBetreff, 
                    tnews.cText, tnews.cVorschauText, tnews.cMetaTitle, tnews.cMetaDescription, 
                    tnews.cMetaKeywords, tnews.nAktiv, tnews.dErstellt, 
                    tseo.cSeo, DATE_FORMAT(tnews.dGueltigVon, '%d.%m.%Y  %H:%i') AS dGueltigVon_de
                        FROM tnews
                        JOIN tnewskategorienews 
                            ON tnewskategorienews.kNews = tnews.kNews
                        LEFT JOIN tseo 
                            ON tseo.cKey = 'kNews'
                            AND tseo.kKey = tnews.kNews
                            AND tseo.kSprache = :lid
                        WHERE tnews.kSprache = :lid
                            AND tnewskategorienews.kNewsKategorie = :cid
                            AND tnews.nAktiv = 1
                            AND tnews.dGueltigVon <= now()
                            AND (tnews.cKundengruppe LIKE '%;-1;%' 
                                OR FIND_IN_SET(:cgid, REPLACE(tnews.cKundengruppe, ';', ',')) > 0)
                        GROUP BY tnews.kNews
                        ORDER BY tnews.dGueltigVon DESC",
                    [
                        'lid'  => $this->langID,
                        'cgid' => $this->customerGroupID,
                        'cid'  => (int)$newsCategory->kNewsKategorie
                    ],
                    \DB\ReturnType::ARRAY_OF_OBJECTS
                );
                foreach ($entries as $entry) {
                    $entry->cURL     = \UrlHelper::buildURL($entry, URLART_NEWS);
                    $entry->cURLFull = \UrlHelper::buildURL($entry, URLART_NEWS, true);
                }
                $newsCategory->oNews_arr = $entries;
            }
            $this->cache->set($cacheID, $newsCategories, [CACHING_GROUP_NEWS]);
        }

        return $newsCategories;
    }

    /**
     * @return array
     */
    public function getNews(): array
    {
        if ($this->conf['news']['news_benutzen'] !== 'Y') {
            return [];
        }
        $cacheID = 'sitemap_news_' . $this->langID;
        if (($overview = $this->cache->get($cacheID)) === false) {
            $overview = $this->db->queryPrepared(
                "SELECT tseo.cSeo, tnewsmonatsuebersicht.cName, tnewsmonatsuebersicht.kNewsMonatsUebersicht, 
                month(tnews.dGueltigVon) AS nMonat, year(tnews.dGueltigVon) AS nJahr, COUNT(*) AS nAnzahl
                    FROM tnews
                    JOIN tnewsmonatsuebersicht 
                        ON tnewsmonatsuebersicht.nMonat = month(tnews.dGueltigVon)
                        AND tnewsmonatsuebersicht.nJahr = year(tnews.dGueltigVon)
                        AND tnewsmonatsuebersicht.kSprache = :lid
                    LEFT JOIN tseo 
                        ON cKey = 'kNewsMonatsUebersicht'
                        AND kKey = tnewsmonatsuebersicht.kNewsMonatsUebersicht
                        AND tseo.kSprache = :lid
                    WHERE tnews.dGueltigVon < now()
                        AND tnews.nAktiv = 1
                        AND tnews.kSprache = :lid
                    GROUP BY year(tnews.dGueltigVon) , month(tnews.dGueltigVon)
                    ORDER BY tnews.dGueltigVon DESC",
                ['lid' => $this->langID],
                \DB\ReturnType::ARRAY_OF_OBJECTS
            );
            foreach ($overview as $news) {
                $entries = $this->db->queryPrepared(
                    "SELECT tnews.kNews, tnews.kSprache, tnews.cKundengruppe, tnews.cBetreff, tnews.cText, 
                    tnews.cVorschauText, tnews.cMetaTitle, tnews.cMetaDescription, tnews.cMetaKeywords,
                    tnews.nAktiv, tnews.dErstellt, tseo.cSeo,
                    COUNT(tnewskommentar.kNewsKommentar) AS nNewsKommentarAnzahl, 
                    DATE_FORMAT(tnews.dGueltigVon, '%d.%m.%Y  %H:%i') AS dGueltigVon_de
                        FROM tnews
                        LEFT JOIN tnewskommentar 
                            ON tnews.kNews = tnewskommentar.kNews
                        LEFT JOIN tseo 
                            ON tseo.cKey = 'kNews'
                            AND tseo.kKey = tnews.kNews
                            AND tseo.kSprache = :lid
                        WHERE tnews.kSprache = :lid
                            AND tnews.nAktiv = 1
                            AND (tnews.cKundengruppe LIKE '%;-1;%' 
                                OR FIND_IN_SET(:cgid, REPLACE(tnews.cKundengruppe, ';', ',')) > 0)
                            AND (MONTH(tnews.dGueltigVon) = :mnth)  
                            AND (tnews.dGueltigVon <= now())
                            AND (YEAR(tnews.dGueltigVon) = :yr) 
                            AND (tnews.dGueltigVon <= now())
                        GROUP BY tnews.kNews
                        ORDER BY dGueltigVon DESC",
                    [
                        'lid'  => $this->langID,
                        'cgid' => $this->customerGroupID,
                        'mnth' => $news->nMonat,
                        'yr'   => $news->nJahr
                    ],
                    \DB\ReturnType::ARRAY_OF_OBJECTS
                );
                foreach ($entries as $oNews) {
                    $oNews->cURL     = \UrlHelper::buildURL($oNews, URLART_NEWS);
                    $oNews->cURLFull = \UrlHelper::buildURL($oNews, URLART_NEWS, true);
                }
                $news->oNews_arr = $entries;
                $news->cURL      = \UrlHelper::buildURL($news, URLART_NEWSMONAT);
                $news->cURLFull  = \UrlHelper::buildURL($news, URLART_NEWSMONAT, true);
            }
            $this->cache->set($cacheID, $overview, [CACHING_GROUP_NEWS]);
        }

        return $overview;
    }

    /**
     * @return array
     */
    public function getManufacturers(): array
    {
        return $this->conf['sitemap']['sitemap_hersteller_anzeigen'] === 'Y'
            ? \Hersteller::getAll()
            : [];
    }

    /**
     * @return array
     * @former gibSitemapGlobaleMerkmale()
     */
    public function getGlobalAttributes(): array
    {
        if ($this->conf['sitemap']['sitemap_globalemerkmale_anzeigen'] !== 'Y') {
            return [];
        }
        $attributeIDs = $this->db->selectAll('tmerkmal', 'nGlobal', 1, 'kMerkmal', 'nSort');
        $attributes   = [];
        foreach ($attributeIDs as $attributeID) {
            $attributes[] = new \Merkmal($attributeID->kMerkmal, true);
        }

        return $attributes;
    }
}
