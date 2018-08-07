<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

/**
 * Class CMSHelper
 */
class CMSHelper
{
    /**
     * @return array
     * @since 5.0.0
     * @former gibStartBoxen()
     */
    public static function getHomeBoxes(): array
    {
        $customerGroupID = Session::CustomerGroup()->getID();
        if (!$customerGroupID || !Session::CustomerGroup()->mayViewCategories()) {
            return [];
        }
        $boxes = self::getHomeBoxList(Shop::getSettings([CONF_STARTSEITE])['startseite']);
        foreach ($boxes as $box) {
            $url      = '';
            $products = [];
            switch ($box->name) {
                case 'TopAngebot':
                    $products = SearchSpecialHelper::getTopOffers($box->anzahl, $customerGroupID);
                    $url      = SEARCHSPECIALS_TOPOFFERS;
                    break;

                case 'Bestseller':
                    $products = SearchSpecialHelper::getBestsellers($box->anzahl, $customerGroupID);
                    $url      = SEARCHSPECIALS_BESTSELLER;
                    break;

                case 'Sonderangebote':
                    $products = SearchSpecialHelper::getSpecialOffers($box->anzahl, $customerGroupID);
                    $url      = SEARCHSPECIALS_SPECIALOFFERS;
                    break;

                case 'NeuImSortiment':
                    $products = SearchSpecialHelper::getNewProducts($box->anzahl, $customerGroupID);
                    $url      = SEARCHSPECIALS_NEWPRODUCTS;
                    break;
            }
            $productIDs = \Functional\map($products, function ($e) {
                return (int)$e->kArtikel;
            });
            if (count($productIDs) > 0) {
                shuffle($productIDs);
                $box->cURL    = SearchSpecialHelper::buildURL($url);
                $box->Artikel = new ArtikelListe();
                $box->Artikel->getArtikelByKeys($productIDs, 0, count($productIDs));
            }
        }
        executeHook(HOOK_BOXEN_HOME, ['boxes' => &$boxes]);

        return $boxes;
    }

    /**
     * @param array $conf
     * @return \Tightenco\Collect\Support\Collection
     * @since 5.0.0
     * @former gibNews()
     */
    public static function getHomeNews(array $conf): \Tightenco\Collect\Support\Collection
    {
        $cSQL      = '';
        $items = new \Tightenco\Collect\Support\Collection();
        if (!isset($conf['news']['news_anzahl_content']) || (int)$conf['news']['news_anzahl_content'] === 0) {
            return $items;
        }
        $cacheID = 'news_' . md5(json_encode($conf['news']) . '_' . Shop::getLanguage());
        if (true||($items = Shop::Cache()->get($cacheID)) === false) {
            if ((int)$conf['news']['news_anzahl_content'] > 0) {
                $cSQL = ' LIMIT ' . (int)$conf['news']['news_anzahl_content'];
            }
            $newsIDs    = Shop::Container()->getDB()->query(
                "SELECT tnews.kNews
                    FROM tnews
                    JOIN tnewskategorienews 
                        ON tnewskategorienews.kNews = tnews.kNews
                    JOIN tnewssprache t 
                        ON tnews.kNews = t.kNews
                    JOIN tnewskategorie 
                        ON tnewskategorie.kNewsKategorie = tnewskategorienews.kNewsKategorie
                         AND tnewskategorie.nAktiv = 1
                    LEFT JOIN tseo 
                        ON tseo.cKey = 'kNews'
                        AND tseo.kKey = tnews.kNews
                        AND tseo.kSprache = " . Shop::getLanguage() . "
                    WHERE t.languageID = " . Shop::getLanguage() . "
                        AND tnews.nAktiv = 1
                        AND tnews.dGueltigVon <= now()
                        AND (tnews.cKundengruppe LIKE '%;-1;%' 
                            OR FIND_IN_SET('" . Session::CustomerGroup()->getID() . "', 
                            REPLACE(tnews.cKundengruppe, ';', ',')) > 0)
                    GROUP BY tnews.kNews
                    ORDER BY tnews.dGueltigVon DESC" . $cSQL,
                \DB\ReturnType::ARRAY_OF_OBJECTS
            );
            $items = new \News\ItemList(Shop::Container()->getDB());
            $items->createItems(\Functional\map($newsIDs, function ($e) {
                return (int)$e->kNews;
            }));
            $items = $items->getItems();
            $cacheTags = [CACHING_GROUP_NEWS, CACHING_GROUP_OPTION];
            executeHook(HOOK_GET_NEWS, [
                'cached'    => false,
                'cacheTags' => &$cacheTags,
                'oNews_arr' => $items
            ]);
            Shop::Cache()->set($cacheID, $items, $cacheTags);

            return $items;
        }
        executeHook(HOOK_GET_NEWS, [
            'cached'    => true,
            'cacheTags' => [],
            'oNews_arr' => &$items
        ]);

        return $items;
    }

    /**
     * @param array $conf
     * @return array
     * @since 5.0.0
     */
    private static function getHomeBoxList($conf): array
    {
        $boxes       = [];
        $obj         = new stdClass();
        $obj->name   = 'Bestseller';
        $obj->anzahl = (int)$conf['startseite_bestseller_anzahl'];
        $obj->sort   = (int)$conf['startseite_bestseller_sortnr'];
        $boxes[]     = $obj;

        $obj         = new stdClass();
        $obj->name   = 'NeuImSortiment';
        $obj->anzahl = (int)$conf['startseite_neuimsortiment_anzahl'];
        $obj->sort   = (int)$conf['startseite_neuimsortiment_sortnr'];
        $boxes[]     = $obj;

        $obj         = new stdClass();
        $obj->name   = 'Sonderangebote';
        $obj->anzahl = (int)$conf['startseite_sonderangebote_anzahl'];
        $obj->sort   = (int)$conf['startseite_sonderangebote_sortnr'];
        $boxes[]     = $obj;

        $obj         = new stdClass();
        $obj->name   = 'TopAngebot';
        $obj->anzahl = (int)$conf['startseite_topangebote_anzahl'];
        $obj->sort   = (int)$conf['startseite_topangebote_sortnr'];
        $boxes[]     = $obj;

        usort($boxes, function ($a, $b) {
            return $a->sort <=> $b->sort;
        });

        return $boxes;
    }

    /**
     * @param array $conf
     * @return array
     * @since 5.0.0
     * @former gibLivesucheTop()
     */
    public static function getLiveSearchTop(array $conf): array
    {
        $limit      = (int)$conf['sonstiges']['sonstiges_livesuche_all_top_count'] > 0
            ? (int)$conf['sonstiges']['sonstiges_livesuche_all_top_count']
            : 100;
        $searchData = Shop::Container()->getDB()->queryPrepared(
            "SELECT tsuchanfrage.kSuchanfrage, tsuchanfrage.kSprache, tsuchanfrage.cSuche, tseo.cSeo, 
            tsuchanfrage.nAktiv, tsuchanfrage.nAnzahlTreffer, tsuchanfrage.nAnzahlGesuche, 
            DATE_FORMAT(tsuchanfrage.dZuletztGesucht, '%d.%m.%Y  %H:%i') AS dZuletztGesucht_de
                FROM tsuchanfrage
                LEFT JOIN tseo 
                    ON tseo.cKey = 'kSuchanfrage' 
                    AND tseo.kKey = tsuchanfrage.kSuchanfrage 
                    AND tseo.kSprache = :lid
                WHERE tsuchanfrage.kSprache = :lid
                    AND tsuchanfrage.nAktiv = 1
                ORDER BY tsuchanfrage.nAnzahlGesuche DESC
                LIMIT :lmt",
            ['lid' => Shop::getLanguageID(), 'lmt' => $limit],
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
        $count      = count($searchData);
        $search     = [];
        $priority   = $count > 0
            ? (($searchData[0]->nAnzahlGesuche - $searchData[$count - 1]->nAnzahlGesuche) / 9)
            : 0;
        foreach ($searchData as $item) {
            $item->Klasse   = $priority < 1
                ? rand(1, 10)
                : (round(($item->nAnzahlGesuche - $searchData[$count - 1]->nAnzahlGesuche) / $priority) + 1);
            $item->cURL     = UrlHelper::buildURL($item, URLART_LIVESUCHE);
            $item->cURLFull = UrlHelper::buildURL($item, URLART_LIVESUCHE, true);
            $search[]       = $item;
        }

        return $search;
    }

    /**
     * @param array $conf
     * @return array
     * @since 5.0.0
     * @former gibLivesucheLast()
     */
    public static function getLiveSearchLast(array $conf): array
    {
        $limit      = (int)$conf['sonstiges']['sonstiges_livesuche_all_last_count'] > 0
            ? (int)$conf['sonstiges']['sonstiges_livesuche_all_last_count']
            : 100;
        $searchData = Shop::Container()->getDB()->queryPrepared(
            "SELECT tsuchanfrage.kSuchanfrage, tsuchanfrage.kSprache, tsuchanfrage.cSuche, tseo.cSeo, 
            tsuchanfrage.nAktiv, tsuchanfrage.nAnzahlTreffer, tsuchanfrage.nAnzahlGesuche, 
            DATE_FORMAT(tsuchanfrage.dZuletztGesucht, '%d.%m.%Y  %H:%i') AS dZuletztGesucht_de
                FROM tsuchanfrage
                LEFT JOIN tseo 
                    ON tseo.cKey = 'kSuchanfrage' 
                    AND tseo.kKey = tsuchanfrage.kSuchanfrage 
                    AND tseo.kSprache = :lid
                WHERE tsuchanfrage.kSprache = :lid
                    AND tsuchanfrage.nAktiv = 1
                    AND tsuchanfrage.kSuchanfrage > 0
                ORDER BY tsuchanfrage.dZuletztGesucht DESC
                LIMIT :lmt",
            ['lid' => Shop::getLanguageID(), 'lmt' => $limit],
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
        $count      = count($searchData);
        $search     = [];
        $priority   = $count > 0
            ? (($searchData[0]->nAnzahlGesuche - $searchData[$count - 1]->nAnzahlGesuche) / 9)
            : 0;
        foreach ($searchData as $item) {
            $item->Klasse   = $priority < 1
                ? rand(1, 10)
                : round(($item->nAnzahlGesuche - $searchData[$count - 1]->nAnzahlGesuche) / $priority) + 1;
            $item->cURL     = UrlHelper::buildURL($item, URLART_LIVESUCHE);
            $item->cURLFull = UrlHelper::buildURL($item, URLART_LIVESUCHE, true);
            $search[]       = $item;
        }

        return $search;
    }

    /**
     * @param array $conf
     * @return array
     * @since 5.0.0
     * @former gibTagging()
     */
    public static function getTagging(array $conf): array
    {
        $limit    = (int)$conf['sonstiges']['sonstiges_tagging_all_count'] > 0
            ? (int)$conf['sonstiges']['sonstiges_tagging_all_count']
            : 100;
        $tagData  = Shop::Container()->getDB()->queryPrepared(
            "SELECT ttag.kTag, ttag.cName, tseo.cSeo, sum(ttagartikel.nAnzahlTagging) AS Anzahl
                FROM ttag
                JOIN ttagartikel 
                    ON ttagartikel.kTag = ttag.kTag
                LEFT JOIN tseo 
                    ON tseo.cKey = 'kTag' 
                    AND tseo.kKey = ttag.kTag 
                    AND tseo.kSprache = :lid
                WHERE ttag.nAktiv = 1
                    AND ttag.kSprache = :lid
                    AND ttag.kTag > 0
                GROUP BY ttag.cName
                ORDER BY Anzahl DESC LIMIT :lmt",
            ['lid' => Shop::getLanguageID(), 'lmt' => $limit],
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
        $count    = count($tagData);
        $tags     = [];
        $priority = $count > 0
            ? (($tagData[0]->Anzahl - $tagData[$count - 1]->Anzahl) / 9)
            : 0;
        foreach ($tagData as $tag) {
            $tag->Klasse   = ($priority < 1) ?
                rand(1, 10) :
                (round(($tag->Anzahl - $tagData[$count - 1]->Anzahl) / $priority) + 1);
            $tag->cURL     = UrlHelper::buildURL($tag, URLART_TAG);
            $tag->cURLFull = UrlHelper::buildURL($tag, URLART_TAG, true);
            $tags[]        = $tag;
        }
        shuffle($tags);

        return $tags;
    }

    /**
     * @return array
     * @since 5.0.0
     * @former gibNewsletterHistory()
     */
    public static function getNewsletterHistory(): array
    {
        $history = Shop::Container()->getDB()->selectAll(
            'tnewsletterhistory',
            'kSprache',
            Shop::getLanguage(),
            'kNewsletterHistory, cBetreff, DATE_FORMAT(dStart, \'%d.%m.%Y %H:%i\') AS Datum, cHTMLStatic',
            'dStart DESC'
        );
        foreach ($history as $item) {
            $item->cURL     = UrlHelper::buildURL($item, URLART_NEWS);
            $item->cURLFull = UrlHelper::buildURL($item, URLART_NEWS, true);
        }

        return $history;
    }

    /**
     * @param array $conf
     * @return array
     * @since 5.0.0
     * @former gibGratisGeschenkArtikel()
     */
    public static function getFreeGifts(array $conf): array
    {
        $gifts = [];
        $sort  = ' ORDER BY CAST(tartikelattribut.cWert AS DECIMAL) DESC';
        if ($conf['sonstiges']['sonstiges_gratisgeschenk_sortierung'] === 'N') {
            $sort = ' ORDER BY tartikel.cName';
        } elseif ($conf['sonstiges']['sonstiges_gratisgeschenk_sortierung'] === 'L') {
            $sort = ' ORDER BY tartikel.fLagerbestand DESC';
        }
        $limit    = ((int)$conf['sonstiges']['sonstiges_gratisgeschenk_anzahl'] > 0)
            ? ' LIMIT ' . (int)$conf['sonstiges']['sonstiges_gratisgeschenk_anzahl']
            : '';
        $tmpGifts = Shop::Container()->getDB()->query(
            "SELECT tartikel.kArtikel, tartikelattribut.cWert
                FROM tartikel
                JOIN tartikelattribut 
                    ON tartikelattribut.kArtikel = tartikel.kArtikel
                LEFT JOIN tartikelsichtbarkeit 
                    ON tartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                    AND tartikelsichtbarkeit.kKundengruppe = " . Session::CustomerGroup()->getID() .
            " WHERE tartikelsichtbarkeit.kArtikel IS NULL
                AND tartikelattribut.cName = '" . FKT_ATTRIBUT_GRATISGESCHENK . "' " .
            Shop::getProductFilter()->getFilterSQL()->getStockFilterSQL() .
            $sort .
            $limit,
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );

        $options = Artikel::getDefaultOptions();
        foreach ($tmpGifts as $item) {
            $product = new Artikel();
            $product->fuelleArtikel($item->kArtikel, $options);
            $product->cBestellwert = Preise::getLocalizedPriceString((float)$item->cWert);

            if ($product->kEigenschaftKombi > 0 || count($product->Variationen) === 0) {
                $gifts[] = $product;
            }
        }

        return $gifts;
    }
}
