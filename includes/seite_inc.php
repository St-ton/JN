<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once PFAD_ROOT . PFAD_INCLUDES . 'artikelsuchspecial_inc.php';

/**
 * @return array
 */
function gibStartBoxen()
{
    $kKundengruppe = Session::CustomerGroup()->getID();
    if (!$kKundengruppe || !Session::CustomerGroup()->mayViewCategories()) {
        return [];
    }
    $cURL          = 0;
    $Boxliste      = [];
    $schon_drin    = [];
    $Einstellungen = Shop::getSettings([CONF_STARTSEITE]);
    while (($obj = gibNextBoxPrio($schon_drin, $Einstellungen)) !== null) {
        $schon_drin[] = $obj->name;
        $Boxliste[]   = $obj;
    }
    foreach (array_reverse($Boxliste) as $box) {
        $kArtikel_arr = [];
        $limit_nr     = $box->anzahl;
        $menge        = null;
        switch ($box->name) {
            case 'TopAngebot':
                $menge = SearchSpecialHelper::getTopOffers($limit_nr, $kKundengruppe);
                $cURL  = SEARCHSPECIALS_TOPOFFERS;
                break;

            case 'Bestseller':
                $menge = SearchSpecialHelper::getBestsellers($limit_nr, $kKundengruppe);
                $cURL  = SEARCHSPECIALS_BESTSELLER;
                break;

            case 'Sonderangebote':
                $menge = SearchSpecialHelper::getSpecialOffers($limit_nr, $kKundengruppe);
                $cURL  = SEARCHSPECIALS_SPECIALOFFERS;
                break;

            case 'NeuImSortiment':
                $menge = SearchSpecialHelper::getNewProducts($limit_nr, $kKundengruppe);
                $cURL  = SEARCHSPECIALS_NEWPRODUCTS;
                break;
        }
        if (is_array($menge) && count($menge) > 0) {
            $rndkeys = array_rand($menge, min($limit_nr, count($menge)));

            if (is_array($rndkeys)) {
                foreach ($rndkeys as $key) {
                    if ($menge[$key]->kArtikel > 0) {
                        $kArtikel_arr[] = $menge[$key]->kArtikel;
                    }
                }
            } elseif ($rndkeys === 0) {
                $kArtikel_arr[] = $menge[0]->kArtikel;
            }
        }
        if (count($kArtikel_arr) > 0) {
            $box->cURL    = SearchSpecialHelper::buildURL($cURL);
            $box->Artikel = new ArtikelListe();
            $box->Artikel->getArtikelByKeys($kArtikel_arr, 0, count($kArtikel_arr));
        }
    }
    executeHook(HOOK_BOXEN_HOME, ['boxes' => &$Boxliste]);

    return $Boxliste;
}

/**
 * @param array $conf
 * @return array|mixed
 */
function gibNews($conf)
{
    $cSQL      = '';
    $oNews_arr = [];
    // Sollen keine News auf der Startseite angezeigt werden?
    if (!isset($conf['news']['news_anzahl_content']) ||
        (int)$conf['news']['news_anzahl_content'] === 0
    ) {
        return $oNews_arr;
    }
    $cacheID = 'news_' . md5(json_encode($conf['news']) . '_' . Shop::getLanguage());

    if (($oNews_arr = Shop::Cache()->get($cacheID)) === false) {
        if ((int)$conf['news']['news_anzahl_content'] > 0) {
            $cSQL = ' LIMIT ' . (int)$conf['news']['news_anzahl_content'];
        }
        $oNews_arr = Shop::Container()->getDB()->query(
            "SELECT tnews.kNews, tnews.kSprache, tnews.cKundengruppe, tnews.cBetreff, tnews.cText, 
                tnews.cVorschauText, tnews.cMetaTitle, tnews.cMetaDescription, tnews.cMetaKeywords, 
                tnews.nAktiv, tnews.dErstellt, tnews.cPreviewImage, tseo.cSeo,
                count(tnewskommentar.kNewsKommentar) AS nNewsKommentarAnzahl, 
                DATE_FORMAT(tnews.dGueltigVon, '%d.%m.%Y  %H:%i') AS dErstellt_de,
                DATE_FORMAT(tnews.dGueltigVon, '%d.%m.%Y  %H:%i') AS dGueltigVon_de
                FROM tnews
                JOIN tnewskategorienews 
                    ON tnewskategorienews.kNews = tnews.kNews
                JOIN tnewskategorie 
                    ON tnewskategorie.kNewsKategorie = tnewskategorienews.kNewsKategorie
                     AND tnewskategorie.nAktiv = 1
                LEFT JOIN tnewskommentar 
                    ON tnewskommentar.kNews = tnews.kNews
                    AND tnewskommentar.nAktiv = 1
                LEFT JOIN tseo ON tseo.cKey = 'kNews'
                    AND tseo.kKey = tnews.kNews
                    AND tseo.kSprache = " . Shop::getLanguage() . "
                WHERE tnews.kSprache = " . Shop::getLanguage() . "
                    AND tnews.nAktiv = 1
                    AND tnews.dGueltigVon <= now()
                    AND (
                        tnews.cKundengruppe LIKE '%;-1;%' 
                        OR FIND_IN_SET('" . Session::CustomerGroup()->getID()
                            . "', REPLACE(tnews.cKundengruppe, ';', ',')) > 0
                        )
                GROUP BY tnews.kNews
                ORDER BY tnews.dGueltigVon DESC" . $cSQL,
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
        // URLs bauen
        $shopURL      = Shop::getURL() . '/';
        $imageBaseURL = Shop::getImageBaseURL();
        foreach ($oNews_arr as $oNews) {
            $oNews->cPreviewImageFull = empty($oNews->cPreviewImage)
                ? ''
                : $imageBaseURL . $oNews->cPreviewImage;
            $oNews->cText             = StringHandler::parseNewsText($oNews->cText);
            $oNews->cURL              = UrlHelper::buildURL($oNews, URLART_NEWS);
            $oNews->cURLFull          = $shopURL . $oNews->cURL;
            $oNews->cMehrURL          = '<a href="' . $oNews->cURL . '">' .
                Shop::Lang()->get('moreLink', 'news') .
                '</a>';
            $oNews->cMehrURLFull      = '<a href="' . $oNews->cURLFull . '">' .
                Shop::Lang()->get('moreLink', 'news') .
                '</a>';
        }
        $cacheTags = [CACHING_GROUP_NEWS, CACHING_GROUP_OPTION];
        executeHook(HOOK_GET_NEWS, [
            'cached'    => false,
            'cacheTags' => &$cacheTags,
            'oNews_arr' => &$oNews_arr
        ]);
        Shop::Cache()->set($cacheID, $oNews_arr, $cacheTags);

        return $oNews_arr;
    }
    executeHook(HOOK_GET_NEWS, [
        'cached'    => true,
        'cacheTags' => [],
        'oNews_arr' => &$oNews_arr
    ]);

    return $oNews_arr;
}

/**
 * @param array $search
 * @param array $conf
 * @return null|stdClass
 */
function gibNextBoxPrio($search, $conf)
{
    $max       = -1;
    $obj       = new stdClass();
    $obj->name = '';
    if ($max < (int)$conf['startseite']['startseite_bestseller_sortnr']
        && (int)$conf['startseite']['startseite_bestseller_anzahl'] > 0
        && !in_array('Bestseller', $search, true)
    ) {
        $obj->name   = 'Bestseller';
        $obj->anzahl = (int)$conf['startseite']['startseite_bestseller_anzahl'];
        $obj->sort   = (int)$conf['startseite']['startseite_bestseller_sortnr'];
        $max         = (int)$conf['startseite']['startseite_bestseller_sortnr'];
    }
    if ($max < (int)$conf['startseite']['startseite_sonderangebote_sortnr']
        && (int)$conf['startseite']['startseite_sonderangebote_anzahl'] > 0
        && !in_array('Sonderangebote', $search, true)
    ) {
        $obj->name   = 'Sonderangebote';
        $obj->anzahl = (int)$conf['startseite']['startseite_sonderangebote_anzahl'];
        $obj->sort   = (int)$conf['startseite']['startseite_sonderangebote_sortnr'];
        $max         = (int)$conf['startseite']['startseite_sonderangebote_sortnr'];
    }
    if ($max < (int)$conf['startseite']['startseite_topangebote_sortnr']
        && (int)$conf['startseite']['startseite_topangebote_anzahl'] > 0
        && !in_array('TopAngebot', $search, true)
    ) {
        $obj->name   = 'TopAngebot';
        $obj->anzahl = (int)$conf['startseite']['startseite_topangebote_anzahl'];
        $obj->sort   = (int)$conf['startseite']['startseite_topangebote_sortnr'];
        $max         = (int)$conf['startseite']['startseite_topangebote_sortnr'];
    }
    if ($max < (int)$conf['startseite']['startseite_neuimsortiment_sortnr']
        && (int)$conf['startseite']['startseite_neuimsortiment_anzahl'] > 0
        && !in_array('NeuImSortiment', $search, true)
    ) {
        $obj->name   = 'NeuImSortiment';
        $obj->anzahl = (int)$conf['startseite']['startseite_neuimsortiment_anzahl'];
        $obj->sort   = (int)$conf['startseite']['startseite_neuimsortiment_sortnr'];
    }

    return (strlen($obj->name) > 0) ? $obj : null;
}

/**
 * @param array $conf
 * @return array
 */
function gibLivesucheTop($conf)
{
    $limit          = (isset($conf['sonstiges']['sonstiges_livesuche_all_top_count'])
        && (int)$conf['sonstiges']['sonstiges_livesuche_all_top_count'] > 0)
        ? (int)$conf['sonstiges']['sonstiges_livesuche_all_top_count']
        : 100;
    $suchwolke_objs = Shop::Container()->getDB()->query(
        "SELECT tsuchanfrage.kSuchanfrage, tsuchanfrage.kSprache, tsuchanfrage.cSuche, tseo.cSeo, 
            tsuchanfrage.nAktiv, tsuchanfrage.nAnzahlTreffer, tsuchanfrage.nAnzahlGesuche, 
            DATE_FORMAT(tsuchanfrage.dZuletztGesucht, '%d.%m.%Y  %H:%i') AS dZuletztGesucht_de
            FROM tsuchanfrage
            LEFT JOIN tseo 
                ON tseo.cKey = 'kSuchanfrage' 
                AND tseo.kKey = tsuchanfrage.kSuchanfrage 
                AND tseo.kSprache = " . Shop::getLanguage() . "
            WHERE tsuchanfrage.kSprache = " . Shop::getLanguage() . "
                AND tsuchanfrage.nAktiv = 1
            ORDER BY tsuchanfrage.nAnzahlGesuche DESC
            LIMIT " . $limit,
        \DB\ReturnType::ARRAY_OF_OBJECTS
    );
    // Priorität berechnen
    $count         = count($suchwolke_objs);
    $Suchwolke_arr = [];
    $prio_step     = $count > 0
        ? (($suchwolke_objs[0]->nAnzahlGesuche - $suchwolke_objs[$count - 1]->nAnzahlGesuche) / 9)
        : 0;
    foreach ($suchwolke_objs as $suchwolke) {
        if ($suchwolke->kSuchanfrage > 0) {
            $suchwolke->Klasse   = $prio_step < 1
                ? rand(1, 10)
                : (round(($suchwolke->nAnzahlGesuche - $suchwolke_objs[$count - 1]->nAnzahlGesuche) / $prio_step) + 1);
            $suchwolke->cURL     = UrlHelper::buildURL($suchwolke, URLART_LIVESUCHE);
            $suchwolke->cURLFull = UrlHelper::buildURL($suchwolke, URLART_LIVESUCHE, true);
            $Suchwolke_arr[]     = $suchwolke;
        }
    }

    return $Suchwolke_arr;
}

/**
 * @param array $conf
 * @return array
 */
function gibLivesucheLast($conf)
{
    $limit          = (isset($conf['sonstiges']['sonstiges_livesuche_all_last_count'])
        && (int)$conf['sonstiges']['sonstiges_livesuche_all_last_count'] > 0)
        ? (int)$conf['sonstiges']['sonstiges_livesuche_all_last_count']
        : 100;
    $suchwolke_objs = Shop::Container()->getDB()->query(
        "SELECT tsuchanfrage.kSuchanfrage, tsuchanfrage.kSprache, tsuchanfrage.cSuche, tseo.cSeo, 
            tsuchanfrage.nAktiv, tsuchanfrage.nAnzahlTreffer, tsuchanfrage.nAnzahlGesuche, 
            DATE_FORMAT(tsuchanfrage.dZuletztGesucht, '%d.%m.%Y  %H:%i') AS dZuletztGesucht_de
            FROM tsuchanfrage
            LEFT JOIN tseo 
                ON tseo.cKey = 'kSuchanfrage' 
                AND tseo.kKey = tsuchanfrage.kSuchanfrage 
                AND tseo.kSprache = " . Shop::getLanguage() . "
            WHERE tsuchanfrage.kSprache = " . Shop::getLanguage() . "
                AND tsuchanfrage.nAktiv = 1
                AND tsuchanfrage.kSuchanfrage > 0
            ORDER BY tsuchanfrage.dZuletztGesucht DESC
            LIMIT " . $limit,
        \DB\ReturnType::ARRAY_OF_OBJECTS
    );
    // Priorität berechnen
    $count         = count($suchwolke_objs);
    $Suchwolke_arr = [];
    $prio_step     = ($count > 0) ?
        (($suchwolke_objs[0]->nAnzahlGesuche - $suchwolke_objs[$count - 1]->nAnzahlGesuche) / 9) :
        0;
    foreach ($suchwolke_objs as $suchwolke) {
        $suchwolke->Klasse   = ($prio_step < 1) ?
            rand(1, 10) :
            round(($suchwolke->nAnzahlGesuche - $suchwolke_objs[$count - 1]->nAnzahlGesuche) / $prio_step) + 1;
        $suchwolke->cURL     = UrlHelper::buildURL($suchwolke, URLART_LIVESUCHE);
        $suchwolke->cURLFull = UrlHelper::buildURL($suchwolke, URLART_LIVESUCHE, true);
        $Suchwolke_arr[]     = $suchwolke;
    }

    return $Suchwolke_arr;
}

/**
 * @param array $conf
 * @return array
 */
function gibTagging($conf)
{
    $limit         = (isset($conf['sonstiges']['sonstiges_tagging_all_count'])
        && (int)$conf['sonstiges']['sonstiges_tagging_all_count'] > 0)
        ? (int)$conf['sonstiges']['sonstiges_tagging_all_count']
        : 100;
    $tagwolke_objs = Shop::Container()->getDB()->query(
        "SELECT ttag.kTag, ttag.cName, tseo.cSeo, sum(ttagartikel.nAnzahlTagging) AS Anzahl
            FROM ttag
            JOIN ttagartikel 
                ON ttagartikel.kTag = ttag.kTag
            LEFT JOIN tseo 
                ON tseo.cKey = 'kTag' 
                AND tseo.kKey = ttag.kTag 
                AND tseo.kSprache = " . Shop::getLanguage() . "
            WHERE ttag.nAktiv = 1
                AND ttag.kSprache = " . Shop::getLanguage() . "
            GROUP BY ttag.cName
            ORDER BY Anzahl DESC LIMIT " . $limit,
        \DB\ReturnType::ARRAY_OF_OBJECTS
    );
    // Priorität berechnen
    $count        = count($tagwolke_objs);
    $Tagwolke_arr = [];
    $prio_step    = ($count > 0) ?
        (($tagwolke_objs[0]->Anzahl - $tagwolke_objs[$count - 1]->Anzahl) / 9) :
        0;
    foreach ($tagwolke_objs as $tagwolke) {
        if ($tagwolke->kTag > 0) {
            $tagwolke->Klasse   = ($prio_step < 1) ?
                rand(1, 10) :
                (round(($tagwolke->Anzahl - $tagwolke_objs[$count - 1]->Anzahl) / $prio_step) + 1);
            $tagwolke->cURL     = UrlHelper::buildURL($tagwolke, URLART_TAG);
            $tagwolke->cURLFull = UrlHelper::buildURL($tagwolke, URLART_TAG, true);
            $Tagwolke_arr[]     = $tagwolke;
        }
    }
    if (count($Tagwolke_arr) > 0) {
        shuffle($Tagwolke_arr);

        return $Tagwolke_arr;
    }

    return [];
}

/**
 * @return mixed
 */
function gibNewsletterHistory()
{
    $oNewsletterHistory_arr = Shop::Container()->getDB()->selectAll(
        'tnewsletterhistory',
        'kSprache',
        Shop::getLanguage(),
        'kNewsletterHistory, cBetreff, DATE_FORMAT(dStart, \'%d.%m.%Y %H:%i\') AS Datum, cHTMLStatic',
        'dStart DESC'
    );
    // URLs bauen
    foreach ($oNewsletterHistory_arr as $oNewsletterHistory) {
        $oNewsletterHistory->cURL     = UrlHelper::buildURL($oNewsletterHistory, URLART_NEWS);
        $oNewsletterHistory->cURLFull = UrlHelper::buildURL($oNewsletterHistory, URLART_NEWS, true);
    }

    return $oNewsletterHistory_arr;
}

/**
 * @param array $conf
 * @return array
 */
function gibGratisGeschenkArtikel($conf)
{
    $oArtikelGeschenk_arr = [];
    $cSQLSort             = " ORDER BY CAST(tartikelattribut.cWert AS DECIMAL) DESC";
    if ($conf['sonstiges']['sonstiges_gratisgeschenk_sortierung'] === 'N') {
        $cSQLSort = " ORDER BY tartikel.cName";
    } elseif ($conf['sonstiges']['sonstiges_gratisgeschenk_sortierung'] === 'L') {
        $cSQLSort = " ORDER BY tartikel.fLagerbestand DESC";
    }
    $cSQLLimit = ((int)$conf['sonstiges']['sonstiges_gratisgeschenk_anzahl'] > 0)
        ? " LIMIT " . (int)$conf['sonstiges']['sonstiges_gratisgeschenk_anzahl']
        : '';
    $oArtikelGeschenkTMP_arr = Shop::Container()->getDB()->query(
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
        $cSQLSort .
        $cSQLLimit,
        \DB\ReturnType::ARRAY_OF_OBJECTS
    );

    $defaultOptions = Artikel::getDefaultOptions();
    foreach ($oArtikelGeschenkTMP_arr as $oArtikelGeschenkTMP) {
        $oArtikel = new Artikel();
        $oArtikel->fuelleArtikel($oArtikelGeschenkTMP->kArtikel, $defaultOptions);
        $oArtikel->cBestellwert = Preise::getLocalizedPriceString((float)$oArtikelGeschenkTMP->cWert);

        if ($oArtikel->kEigenschaftKombi > 0
            || !is_array($oArtikel->Variationen)
            || count($oArtikel->Variationen) === 0
        ) {
            $oArtikelGeschenk_arr[] = $oArtikel;
        }
    }

    return $oArtikelGeschenk_arr;
}

/**
 * @param array $Einstellungen
 * @return null
 * @deprecated since 5.0.0
 */
function gibAuswahlAssistentFragen($Einstellungen)
{
    trigger_error(__FUNCTION__ . ' is deprecated and does not do anything useful.', E_USER_DEPRECATED);
    return null;
}

/**
 * @return KategorieListe
 * @deprecated since 5.0.0
 */
function gibSitemapKategorien()
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $oKategorieliste           = new KategorieListe();
    $oKategorieliste->elemente = KategorieHelper::getInstance()->combinedGetAll();

    return $oKategorieliste;
}

/**
 * @return array
 * @deprecated since 5.0.0
 */
function gibSitemapGlobaleMerkmale()
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $sm = new \JTL\Sitemap(Shop::Container()->getDB(), Shop::Container()->getCache(), Shop::getConfig([CONF_SITEMAP]));

    return $sm->getGlobalAttributes();
}

/**
 * @param object $oMerkmal
 * @deprecated since 5.0.0
 */
function verarbeiteMerkmalBild(&$oMerkmal)
{
    trigger_error(__FUNCTION__ . ' is deprecated and does not do anything useful.', E_USER_DEPRECATED);
}

/**
 * @param object $oMerkmalWert
 * @deprecated since 5.0.0
 */
function verarbeiteMerkmalWertBild(&$oMerkmalWert)
{
    trigger_error(__FUNCTION__ . ' is deprecated and does not do anything useful.', E_USER_DEPRECATED);
}

/**
 * @param array $conf
 * @return array
 * @deprecated since 5.0.0
 */
function gibBoxNews($conf)
{
    trigger_error(__FUNCTION__ . ' is deprecated and does not return anything useful.', E_USER_DEPRECATED);
    return [];
}

/**
 * @return array
 * @deprecated since 5.0.0
 */
function gibSitemapNews()
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $sm = new \JTL\Sitemap(Shop::Container()->getDB(), Shop::Container()->getCache(), Shop::getConfig([CONF_NEWS]));

    return $sm->getNews();
}

/**
 * @return array
 * @deprecated since 5.0.0
 */
function gibNewsKategorie()
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $sm = new \JTL\Sitemap(Shop::Container()->getDB(), Shop::Container()->getCache(), Shop::getConfig([CONF_SITEMAP]));

    return $sm->getNewsCategories();
}

/**
 * @param array $conf
 * @param JTLSmarty $smarty
 * @deprecated since 5.0.0
 */
function gibSeiteSitemap($conf, $smarty)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    Shop::setPageType(PAGE_SITEMAP);
    $sm = new \JTL\Sitemap(Shop::Container()->getDB(), Shop::Container()->getCache(), $conf);
    $sm->assignData($smarty);
}

/**
 * @param int $nLinkart
 * @deprecated since 5.0.0
 */
function pruefeSpezialseite(int $nLinkart)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $specialPages = Shop::Container()->getLinkService()->getLinkGroupByName('specialpages');
    if ($nLinkart > 0 && $specialPages !== null) {
        $res = $specialPages->getLinks()->first(function (\Link\LinkInterface $l) use ($nLinkart) {
            return $l->getLinkType() === $nLinkart;
        });
        /** @var \Link\LinkInterface $res */
        if ($res !== null && $res->getFileName() !== null) {
            header('Location: ' . Shop::Container()->getLinkService()->getStaticRoute($res->getFileName()));
            exit();
        }
    }
}
