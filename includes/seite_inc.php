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
                $menge = gibTopAngebote($limit_nr, $kKundengruppe);
                $cURL  = SEARCHSPECIALS_TOPOFFERS;
                break;

            case 'Bestseller':
                $menge = gibBestseller($limit_nr, $kKundengruppe);
                $cURL  = SEARCHSPECIALS_BESTSELLER;
                break;

            case 'Sonderangebote':
                $menge = gibSonderangebote($limit_nr, $kKundengruppe);
                $cURL  = SEARCHSPECIALS_SPECIALOFFERS;
                break;

            case 'NeuImSortiment':
                $menge = gibNeuImSortiment($limit_nr, $kKundengruppe);
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
            $box->cURL    = baueSuchSpecialURL($cURL);
            //hole anzuzeigende Artikel
            $box->Artikel = new ArtikelListe();
            $box->Artikel->getArtikelByKeys($kArtikel_arr, 0, count($kArtikel_arr));
        }
    }
    executeHook(HOOK_BOXEN_HOME, ['boxes' => &$Boxliste]);

    return $Boxliste;
}

/**
 * @param array $Einstellungen
 * @return mixed
 */
function gibAuswahlAssistentFragen($Einstellungen)
{
    if ($Einstellungen['auswahlassistent']['auswahlassistent_anzeige_startseite'] === 'Y') {
        require_once PFAD_ROOT . PFAD_INCLUDES_EXT . 'auswahlassistent_inc.php';

        if (function_exists('gibAAFrage')) {
            $oSpracheStd = gibStandardsprache(true);

            return gibAAFrage($_SESSION['AuswahlAssistent']['nFrage'], Shop::getLanguage(), (int)$oSpracheStd->kSprache);
        }
    } else {
        unset($_SESSION['AuswahlAssistent']);
    }

    return null;
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
        $oNews_arr = Shop::DB()->query("
            SELECT tnews.kNews, tnews.kSprache, tnews.cKundengruppe, tnews.cBetreff, tnews.cText, 
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
                ORDER BY tnews.dGueltigVon DESC" . $cSQL, 2
        );
        // URLs bauen
        $shopURL = Shop::getURL() . '/';
        foreach ($oNews_arr as $oNews) {
            $oNews->cPreviewImageFull = empty($oNews->cPreviewImage)
                ? ''
                : $shopURL . $oNews->cPreviewImage;
            $oNews->cText             = parseNewsText($oNews->cText);
            $oNews->cURL              = baueURL($oNews, URLART_NEWS);
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
    $suchwolke_objs = Shop::DB()->query("
        SELECT tsuchanfrage.kSuchanfrage, tsuchanfrage.kSprache, tsuchanfrage.cSuche, tseo.cSeo, 
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
            LIMIT " . $limit, 2
    );
    // Priorität berechnen
    $count         = count($suchwolke_objs);
    $Suchwolke_arr = [];
    $prio_step     = $count > 0
        ? (($suchwolke_objs[0]->nAnzahlGesuche - $suchwolke_objs[$count - 1]->nAnzahlGesuche) / 9)
        : 0;
    foreach ($suchwolke_objs as $suchwolke) {
        if ($suchwolke->kSuchanfrage > 0) {
            $suchwolke->Klasse   = ($prio_step < 1) ?
                rand(1, 10) :
                (round(($suchwolke->nAnzahlGesuche - $suchwolke_objs[$count - 1]->nAnzahlGesuche) / $prio_step) + 1);
            $suchwolke->cURL     = baueURL($suchwolke, URLART_LIVESUCHE);
            $suchwolke->cURLFull = baueURL($suchwolke, URLART_LIVESUCHE, 0, false, true);
            $Suchwolke_arr[]     = $suchwolke;
        }
    }

    return $Suchwolke_arr;
}

/**
 * @param object $a
 * @param object $b
 * @return int
 */
function wolkesort($a, $b)
{
    if ($a->nAnzahlGesuche < $b->nAnzahlGesuche) {
        return 1;
    }

    return $a->nAnzahlGesuche > $b->nAnzahlGesuche
        ? -1
        : 0;
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
    $suchwolke_objs = Shop::DB()->query(
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
            ORDER BY tsuchanfrage.dZuletztGesucht DESC
            LIMIT " . $limit, 2
    );
    // Priorität berechnen
    $count         = count($suchwolke_objs);
    $Suchwolke_arr = [];
    $prio_step     = ($count > 0) ?
        (($suchwolke_objs[0]->nAnzahlGesuche - $suchwolke_objs[$count - 1]->nAnzahlGesuche) / 9) :
        0;
    foreach ($suchwolke_objs as $suchwolke) {
        if ($suchwolke->kSuchanfrage > 0) {
            $suchwolke->Klasse   = ($prio_step < 1) ?
                rand(1, 10) :
                round(($suchwolke->nAnzahlGesuche - $suchwolke_objs[$count - 1]->nAnzahlGesuche) / $prio_step) + 1;
            $suchwolke->cURL     = baueURL($suchwolke, URLART_LIVESUCHE);
            $suchwolke->cURLFull = baueURL($suchwolke, URLART_LIVESUCHE, 0, false, true);
            $Suchwolke_arr[]     = $suchwolke;
        }
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
    $tagwolke_objs = Shop::DB()->query(
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
            ORDER BY Anzahl DESC LIMIT " . $limit, 2
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
            $tagwolke->cURL     = baueURL($tagwolke, URLART_TAG);
            $tagwolke->cURLFull = baueURL($tagwolke, URLART_TAG, 0, false, true);
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
    $oNewsletterHistory_arr = Shop::DB()->selectAll(
        'tnewsletterhistory',
        'kSprache',
        Shop::getLanguage(),
        'kNewsletterHistory, cBetreff, DATE_FORMAT(dStart, \'%d.%m.%Y %H:%i\') AS Datum, cHTMLStatic',
        'dStart DESC'
    );
    // URLs bauen
    foreach ($oNewsletterHistory_arr as $oNewsletterHistory) {
        $oNewsletterHistory->cURL     = baueURL($oNewsletterHistory, URLART_NEWS);
        $oNewsletterHistory->cURLFull = baueURL($oNewsletterHistory, URLART_NEWS, 0, false, true);
    }

    return $oNewsletterHistory_arr;
}

/**
 * @return KategorieListe
 */
function gibSitemapKategorien()
{
    $helper                    = KategorieHelper::getInstance();
    $oKategorieliste           = new KategorieListe();
    $oKategorieliste->elemente = $helper->combinedGetAll();

    return $oKategorieliste;
}

/**
 * @return array
 */
function gibSitemapGlobaleMerkmale()
{
    $isDefaultLanguage = standardspracheAktiv();
    $cacheID           = 'gsgm_' . (($isDefaultLanguage === true) ? 'd_' : '') . Shop::getLanguage();
    if (($oMerkmal_arr = Shop::Cache()->get($cacheID)) === false) {
        $oMerkmal_arr    = [];
        $cDatei          = 'index.php';
        $cMerkmalTabelle = 'tmerkmal';
        $cSQL            = " JOIN tmerkmalwert ON tmerkmalwert.kMerkmal = tmerkmal.kMerkmal";
        $cSQL .= " JOIN tmerkmalwertsprache ON tmerkmalwertsprache.kMerkmalWert = tmerkmalwert.kMerkmalWert";
        $cMerkmalWhere = '';
        if ($isDefaultLanguage === false) {
            $cMerkmalTabelle = "tmerkmalsprache";
            $cSQL            = " JOIN tmerkmal ON tmerkmal.kMerkmal = tmerkmalsprache.kMerkmal";
            $cSQL .= " JOIN tmerkmalwert ON tmerkmalwert.kMerkmal = tmerkmal.kMerkmal";
            $cSQL .= " JOIN tmerkmalwertsprache ON tmerkmalwertsprache.kMerkmalWert = tmerkmalwert.kMerkmalWert";
            $cMerkmalWhere = " AND tmerkmalsprache.kSprache = " . Shop::getLanguage();
        }
        $oMerkmalTMP_arr = Shop::DB()->query(
            "SELECT {$cMerkmalTabelle}.*, tmerkmalwertsprache.cWert, tseo.cSeo, tmerkmalwertsprache.kMerkmalWert, 
                tmerkmal.nSort, tmerkmal.nGlobal, tmerkmal.cTyp, tmerkmalwert.cBildPfad AS cBildPfadMW, 
                tmerkmal.cBildpfad
                FROM {$cMerkmalTabelle}
                {$cSQL}
                JOIN tartikelmerkmal 
                    ON tartikelmerkmal.kMerkmalWert = tmerkmalwertsprache.kMerkmalWert
                LEFT JOIN tseo 
                    ON tseo.cKey = 'kMerkmalWert'
                    AND tseo.kKey = tmerkmalwertsprache.kMerkmalWert
                    AND tseo.kSprache = " . Shop::getLanguage() . "
                WHERE tmerkmal.nGlobal = 1
                    AND tmerkmalwertsprache.kSprache = " . Shop::getLanguage() . "
                    {$cMerkmalWhere}
                GROUP BY tmerkmalwertsprache.kMerkmalWert
                ORDER BY tmerkmal.nSort, {$cMerkmalTabelle}.cName, tmerkmalwert.nSort, tmerkmalwertsprache.cWert", 2
        );
        $nPos    = 0;
        $shopURL = Shop::getURL() . '/';
        foreach ($oMerkmalTMP_arr as $i => &$oMerkmalTMP) {
            $oMerkmalWert = new stdClass();
            $oMerkmal     = new stdClass();
            if ($i > 0) {
                // Alle weiteren Durchläufe
                if ($oMerkmal_arr[$nPos]->kMerkmal == $oMerkmalTMP->kMerkmal) {
                    $oMerkmalWert->kMerkmalWert = $oMerkmalTMP->kMerkmalWert;
                    $oMerkmalWert->cWert        = $oMerkmalTMP->cWert;
                    $oMerkmalWert->cSeo         = $oMerkmalTMP->cSeo;
                    $oMerkmalWert->cBildPfadMW  = $oMerkmalTMP->cBildPfadMW;

                    verarbeiteMerkmalWertBild($oMerkmalWert);
                    // cURL bauen
                    $oMerkmalWert->cURL = strlen($oMerkmalWert->cSeo) > 0
                        ? $shopURL . $oMerkmalWert->cSeo
                        : $shopURL . $cDatei . '?m=' . $oMerkmalWert->kMerkmalWert;

                    $oMerkmal_arr[$nPos]->oMerkmalWert_arr[] = $oMerkmalWert;
                } else {
                    $oMerkmal->kMerkmal  = $oMerkmalTMP->kMerkmal;
                    $oMerkmal->cName     = $oMerkmalTMP->cName;
                    $oMerkmal->nSort     = $oMerkmalTMP->nSort;
                    $oMerkmal->nGlobal   = $oMerkmalTMP->nGlobal;
                    $oMerkmal->cBildpfad = $oMerkmalTMP->cBildpfad;
                    $oMerkmal->cTyp      = $oMerkmalTMP->cTyp;

                    verarbeiteMerkmalBild($oMerkmal);
                    $oMerkmalWert->kMerkmalWert = $oMerkmalTMP->kMerkmalWert;
                    $oMerkmalWert->cWert        = $oMerkmalTMP->cWert;
                    $oMerkmalWert->cSeo         = $oMerkmalTMP->cSeo;
                    $oMerkmalWert->cBildPfadMW  = $oMerkmalTMP->cBildPfadMW;

                    verarbeiteMerkmalWertBild($oMerkmalWert);
                    // cURL bauen
                    $oMerkmalWert->cURL = (strlen($oMerkmalWert->cSeo) > 0)
                        ? $shopURL . $oMerkmalWert->cSeo
                        : $shopURL . $cDatei . '?m=' . $oMerkmalWert->kMerkmalWert;

                    $oMerkmal->oMerkmalWert_arr[] = $oMerkmalWert;
                    $oMerkmal_arr[]               = $oMerkmal;

                    ++$nPos;
                }
            } else { // Erster Durchlauf
                $oMerkmal->kMerkmal         = isset($oMerkmalTMP->kMerkmal) ? $oMerkmalTMP->kMerkmal : null;
                $oMerkmal->cName            = isset($oMerkmalTMP->cName) ? $oMerkmalTMP->cName : null;
                $oMerkmal->nSort            = isset($oMerkmalTMP->nSort) ? $oMerkmalTMP->nSort : null;
                $oMerkmal->nGlobal          = isset($oMerkmalTMP->nGlobal) ? $oMerkmalTMP->nGlobal : null;
                $oMerkmal->cBildpfad        = isset($oMerkmalTMP->cBildpfad) ? $oMerkmalTMP->cBildpfad : null;
                $oMerkmal->cTyp             = isset($oMerkmalTMP->cTyp) ? $oMerkmalTMP->cTyp : null;
                $oMerkmal->oMerkmalWert_arr = [];

                verarbeiteMerkmalBild($oMerkmal);
                $oMerkmalWert->kMerkmalWert = $oMerkmalTMP->kMerkmalWert;
                $oMerkmalWert->cWert        = $oMerkmalTMP->cWert;
                $oMerkmalWert->cSeo         = $oMerkmalTMP->cSeo;
                $oMerkmalWert->cBildPfadMW  = $oMerkmalTMP->cBildPfadMW;

                verarbeiteMerkmalWertBild($oMerkmalWert);
                // cURL bauen
                $oMerkmalWert->cURL = (strlen($oMerkmalWert->cSeo) > 0)
                    ? $shopURL . $oMerkmalWert->cSeo
                    : $shopURL . $cDatei . '?m=' . $oMerkmalWert->kMerkmalWert;
                $oMerkmal->oMerkmalWert_arr[] = $oMerkmalWert;
                $oMerkmal_arr[]               = $oMerkmal;
            }
        }
        unset($oMerkmalTMP);
    }
    Shop::Cache()->set($cacheID, $oMerkmal_arr, [CACHING_GROUP_CATEGORY]);

    return $oMerkmal_arr;
}

/**
 * @param object $oMerkmal
 */
function verarbeiteMerkmalBild(&$oMerkmal)
{
    $shopURL = Shop::getURL() . '/';

    $oMerkmal->cBildpfadKlein       = BILD_KEIN_MERKMALBILD_VORHANDEN;
    $oMerkmal->nBildKleinVorhanden  = 0;
    $oMerkmal->cBildpfadNormal      = BILD_KEIN_MERKMALBILD_VORHANDEN;
    $oMerkmal->nBildNormalVorhanden = 0;
    if (strlen($oMerkmal->cBildpfad) > 0) {
        if (file_exists(PFAD_MERKMALBILDER_KLEIN . $oMerkmal->cBildpfad)) {
            $oMerkmal->cBildpfadKlein      = PFAD_MERKMALBILDER_KLEIN . $oMerkmal->cBildpfad;
            $oMerkmal->nBildKleinVorhanden = 1;
        }
        if (file_exists(PFAD_MERKMALBILDER_NORMAL . $oMerkmal->cBildpfad)) {
            $oMerkmal->cBildpfadNormal     = PFAD_MERKMALBILDER_NORMAL . $oMerkmal->cBildpfad;
            $oMerkmal->nBildGrossVorhanden = 1;
        }
    }
    $oMerkmal->cBildURLKlein  = $shopURL . $oMerkmal->cBildpfadKlein;
    $oMerkmal->cBildURLNormal = $shopURL . $oMerkmal->cBildpfadNormal;
}

/**
 * @param object $oMerkmalWert
 */
function verarbeiteMerkmalWertBild(&$oMerkmalWert)
{
    $shopURL = Shop::getURL() . '/';

    $oMerkmalWert->cBildpfadKlein       = BILD_KEIN_MERKMALWERTBILD_VORHANDEN;
    $oMerkmalWert->nBildKleinVorhanden  = 0;
    $oMerkmalWert->cBildpfadNormal      = BILD_KEIN_MERKMALWERTBILD_VORHANDEN;
    $oMerkmalWert->nBildNormalVorhanden = 0;
    if (isset($oMerkmalWert->cBildPfadMW) && strlen($oMerkmalWert->cBildPfadMW) > 0) {
        if (file_exists(PFAD_MERKMALWERTBILDER_KLEIN . $oMerkmalWert->cBildPfadMW)) {
            $oMerkmalWert->cBildpfadKlein      = PFAD_MERKMALWERTBILDER_KLEIN . $oMerkmalWert->cBildPfadMW;
            $oMerkmalWert->nBildKleinVorhanden = 1;
        }
        if (file_exists(PFAD_MERKMALWERTBILDER_NORMAL . $oMerkmalWert->cBildPfadMW)) {
            $oMerkmalWert->cBildpfadNormal      = PFAD_MERKMALWERTBILDER_NORMAL . $oMerkmalWert->cBildPfadMW;
            $oMerkmalWert->nBildNormalVorhanden = 1;
        }
    }
    $oMerkmalWert->cBildURLKlein  = $shopURL . $oMerkmalWert->cBildpfadKlein;
    $oMerkmalWert->cBildURLNormal = $shopURL . $oMerkmalWert->cBildpfadNormal;
}

/**
 * @param array $conf
 * @return mixed
 */
function gibBoxNews($conf)
{
    $nBoxenLimit = (int)$conf['news']['news_anzahl_box'] > 0
        ? (int)$conf['news']['news_anzahl_box']
        : 3;

    return Shop::DB()->query(
        "SELECT DATE_FORMAT(dErstellt, '%M, %Y') AS Datum, count(*) AS nAnzahl, 
            DATE_FORMAT(dErstellt, '%m') AS nMonat
            FROM tnews
            WHERE kSprache = " . Shop::getLanguage() . "
                AND nAktiv = 1
                AND (
                    cKundengruppe LIKE '%;-1;%' 
                    OR FIND_IN_SET('" . Session::CustomerGroup()->getID()
                        . "', REPLACE(cKundengruppe, ';', ',')) > 0
                    )
            GROUP BY DATE_FORMAT(dErstellt, '%M')
            ORDER BY dErstellt DESC
            LIMIT " . $nBoxenLimit, 2
    );
}

/**
 * @return mixed
 */
function gibSitemapNews()
{
    $cacheID = 'sitemap_news';
    if (($overview = Shop::Cache()->get($cacheID)) === false) {
        $overview = Shop::DB()->query(
            "SELECT tseo.cSeo, tnewsmonatsuebersicht.cName, tnewsmonatsuebersicht.kNewsMonatsUebersicht, 
                month(tnews.dGueltigVon) AS nMonat, year(tnews.dGueltigVon) AS nJahr, count(*) AS nAnzahl
                FROM tnews
                JOIN tnewsmonatsuebersicht ON tnewsmonatsuebersicht.nMonat = month(tnews.dGueltigVon)
                    AND tnewsmonatsuebersicht.nJahr = year(tnews.dGueltigVon)
                    AND tnewsmonatsuebersicht.kSprache =1
                LEFT JOIN tseo ON cKey = 'kNewsMonatsUebersicht'
                    AND kKey = tnewsmonatsuebersicht.kNewsMonatsUebersicht
                    AND tseo.kSprache = " . Shop::getLanguage() . "
                WHERE tnews.dGueltigVon < now()
                    AND tnews.nAktiv = 1
                    AND tnews.kSprache = " . Shop::getLanguage() . "
                GROUP BY year(tnews.dGueltigVon) , month(tnews.dGueltigVon)
                ORDER BY tnews.dGueltigVon DESC", 2
        );
        foreach ($overview as $news) {
            $entries = Shop::DB()->query(
                "SELECT tnews.kNews, tnews.kSprache, tnews.cKundengruppe, tnews.cBetreff, tnews.cText, 
                    tnews.cVorschauText, tnews.cMetaTitle, tnews.cMetaDescription, tnews.cMetaKeywords,
                    tnews.nAktiv, tnews.dErstellt, tseo.cSeo,
                    count(tnewskommentar.kNewsKommentar) AS nNewsKommentarAnzahl, 
                    DATE_FORMAT(tnews.dGueltigVon, '%d.%m.%Y  %H:%i') AS dGueltigVon_de
                    FROM tnews
                    LEFT JOIN tnewskommentar 
                        ON tnews.kNews = tnewskommentar.kNews
                    LEFT JOIN tseo 
                        ON tseo.cKey = 'kNews'
                        AND tseo.kKey = tnews.kNews
                        AND tseo.kSprache = " . Shop::getLanguage() . "
                    WHERE tnews.kSprache = " . Shop::getLanguage() . "
                        AND tnews.nAktiv = 1
                        AND (
                            tnews.cKundengruppe LIKE '%;-1;%' 
                            OR FIND_IN_SET('" . Session::CustomerGroup()->getID()
                                . "', REPLACE(tnews.cKundengruppe, ';', ',')) > 0
                            )
                        AND (MONTH(tnews.dGueltigVon) = '" . $news->nMonat . "') 
                        && (tnews.dGueltigVon <= now())
                        AND (YEAR(tnews.dGueltigVon) = '" . $news->nJahr . "') 
                        && (tnews.dGueltigVon <= now())
                    GROUP BY tnews.kNews
                    ORDER BY dGueltigVon DESC", 2
            );
            foreach ($entries as $oNews) {
                $oNews->cURL     = baueURL($oNews, URLART_NEWS);
                $oNews->cURLFull = baueURL($oNews, URLART_NEWS, 0, false, true);
            }
            $news->oNews_arr = $entries;
            $news->cURL      = baueURL($news, URLART_NEWSMONAT);
            $news->cURLFull  = baueURL($news, URLART_NEWSMONAT, 0, false, true);
        }
        Shop::Cache()->set($cacheID, $overview, [CACHING_GROUP_NEWS]);
    }

    return $overview;
}

/**
 * @return mixed
 */
function gibNewsKategorie()
{
    $cacheID = 'news_category_' . Shop::getLanguage() . '_' . Session::CustomerGroup()->getID();
    if (($newsCategories = Shop::Cache()->get($cacheID)) === false) {
        $newsCategories = Shop::DB()->query(
            "SELECT tnewskategorie.kNewsKategorie, tnewskategorie.kSprache, tnewskategorie.cName,
                tnewskategorie.cBeschreibung, tnewskategorie.cMetaTitle, tnewskategorie.cMetaDescription,
                tnewskategorie.nSort, tnewskategorie.nAktiv, tnewskategorie.dLetzteAktualisierung, 
                tnewskategorie.cPreviewImage, tseo.cSeo,
                count(DISTINCT(tnewskategorienews.kNews)) AS nAnzahlNews
                FROM tnewskategorie
                LEFT JOIN tnewskategorienews 
                    ON tnewskategorienews.kNewsKategorie = tnewskategorie.kNewsKategorie
                LEFT JOIN tnews 
                    ON tnews.kNews = tnewskategorienews.kNews
                LEFT JOIN tseo 
                    ON tseo.cKey = 'kNewsKategorie'
                    AND tseo.kKey = tnewskategorie.kNewsKategorie
                    AND tseo.kSprache = " . Shop::getLanguage() . "
                WHERE tnewskategorie.kSprache = " . Shop::getLanguage() . "
                    AND tnewskategorie.nAktiv = 1
                    AND tnews.nAktiv = 1
                    AND tnews.dGueltigVon <= now()
                    AND (
                        tnews.cKundengruppe LIKE '%;-1;%' 
                        OR FIND_IN_SET('" . Session::CustomerGroup()->getID()
                            . "', REPLACE(tnews.cKundengruppe, ';', ',')) > 0
                        )
                GROUP BY tnewskategorienews.kNewsKategorie
                ORDER BY tnewskategorie.nSort DESC", 2
        );
        foreach ($newsCategories as $newsCategory) {
            $newsCategory->cURL      = baueURL($newsCategory, URLART_NEWSKATEGORIE);
            $newsCategory->cURLFull  = baueURL($newsCategory, URLART_NEWSKATEGORIE, 0, false, true);

            $entries = Shop::DB()->query(
                "SELECT tnews.kNews, tnews.kSprache, tnews.cKundengruppe, tnews.cBetreff, tnews.cText, tnews.cVorschauText, 
                    tnews.cMetaTitle, tnews.cMetaDescription, tnews.cMetaKeywords, tnews.nAktiv, tnews.dErstellt, 
                    tseo.cSeo, DATE_FORMAT(tnews.dGueltigVon, '%d.%m.%Y  %H:%i') AS dGueltigVon_de
                    FROM tnews
                    JOIN tnewskategorienews 
                        ON tnewskategorienews.kNews = tnews.kNews
                    LEFT JOIN tseo 
                        ON tseo.cKey = 'kNews'
                        AND tseo.kKey = tnews.kNews
                        AND tseo.kSprache = " . Shop::getLanguage() . "
                    WHERE tnews.kSprache = " . Shop::getLanguage() . "
                        AND tnewskategorienews.kNewsKategorie = " . (int)$newsCategory->kNewsKategorie . "
                        AND tnews.nAktiv = 1
                        AND tnews.dGueltigVon <= now()
                        AND (
                            tnews.cKundengruppe LIKE '%;-1;%' 
                            OR FIND_IN_SET('" . Session::CustomerGroup()->getID()
                                . "', REPLACE(tnews.cKundengruppe, ';', ',')) > 0
                            )
                    GROUP BY tnews.kNews
                    ORDER BY tnews.dGueltigVon DESC", 2
            );
            foreach ($entries as $entry) {
                $entry->cURL     = baueURL($entry, URLART_NEWS);
                $entry->cURLFull = baueURL($entry, URLART_NEWS, 0, false, true);
            }
            $newsCategory->oNews_arr = $entries;
        }
        Shop::Cache()->set($cacheID, $newsCategories, [CACHING_GROUP_NEWS]);
    }

    return $newsCategories;
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
    $oArtikelGeschenkTMP_arr = Shop::DB()->query(
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
            $cSQLLimit, 2
    );

    if (is_array($oArtikelGeschenkTMP_arr) && count($oArtikelGeschenkTMP_arr) > 0) {
        $defaultOptions = Artikel::getDefaultOptions();
        foreach ($oArtikelGeschenkTMP_arr as $oArtikelGeschenkTMP) {
            $oArtikel = new Artikel();
            $oArtikel->fuelleArtikel($oArtikelGeschenkTMP->kArtikel, $defaultOptions);
            $oArtikel->cBestellwert = gibPreisStringLocalized((float)$oArtikelGeschenkTMP->cWert);

            if ($oArtikel->kEigenschaftKombi > 0
                || !is_array($oArtikel->Variationen)
                || count($oArtikel->Variationen) === 0
            ) {
                $oArtikelGeschenk_arr[] = $oArtikel;
            }
        }
    }

    return $oArtikelGeschenk_arr;
}

/**
 * @param int $nLinkart
 * @return null
 */
function pruefeSpezialseite($nLinkart)
{
    if ((int)$nLinkart > 0) {
        $cacheID = 'special_page_n_' . $nLinkart;
        if (($oSeite = Shop::Cache()->get($cacheID)) === false) {
            $oSeite = Shop::DB()->select('tspezialseite', 'nLinkart', (int)$nLinkart);
            Shop::Cache()->set($cacheID, $oSeite, [CACHING_GROUP_CORE]);
        }
        if (isset($oSeite->cDateiname) && strlen($oSeite->cDateiname) > 0) {
            $linkHelper = LinkHelper::getInstance();
            header('Location: ' . $linkHelper->getStaticRoute($oSeite->cDateiname));
            exit();
        }
    }

    return null;
}

/**
 * @param array $conf
 * @param JTLSmarty $smarty
 */
function gibSeiteSitemap($conf, &$smarty)
{
    Shop::setPageType(PAGE_SITEMAP);
    $linkHelper             = LinkHelper::getInstance();
    $linkGroups             = $linkHelper->getLinkGroups();
    $cLinkgruppenMember_arr = [];
    if ($linkGroups !== null && is_object($linkGroups)) {
        $cLinkgruppenMemberTMP_arr = get_object_vars($linkGroups);
        if (is_array($cLinkgruppenMemberTMP_arr) && count($cLinkgruppenMemberTMP_arr) > 0) {
            foreach ($cLinkgruppenMemberTMP_arr as $cLinkgruppe => $cLinkgruppenMemberTMP) {
                $cLinkgruppenMember_arr[] = $cLinkgruppe;
            }
        }
    }
    // Smarty Hilfe um die Linksgruppen dynamisch zu bauen
    $smarty->assign('cLinkgruppenMember_arr', $cLinkgruppenMember_arr);

    if ($conf['sitemap']['sitemap_kategorien_anzeigen'] === 'Y') {
        $smarty->assign('oKategorieliste', gibSitemapKategorien());
    }
    if ($conf['sitemap']['sitemap_globalemerkmale_anzeigen'] === 'Y') {
        $smarty->assign('oGlobaleMerkmale_arr', gibSitemapGlobaleMerkmale());
    }
    if ($conf['sitemap']['sitemap_hersteller_anzeigen'] === 'Y') {
        $smarty->assign('oHersteller_arr', Hersteller::getAll());
    }
    if ($conf['news']['news_benutzen'] === 'Y' && $conf['sitemap']['sitemap_news_anzeigen'] === 'Y') {
        $smarty->assign('oNewsMonatsUebersicht_arr', gibSitemapNews());
    }
    if ($conf['sitemap']['sitemap_newskategorien_anzeigen'] === 'Y') {
        $smarty->assign('oNewsKategorie_arr', gibNewsKategorie());
    }
}

/**
 * @deprecated since 4.0
 * @param array $Einstellungen
 * @return array
 */
function gibSitemapHersteller($Einstellungen)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return Hersteller::getAll();
}

/**
 * @deprecated since 4.0
 * @param int $kLink
 * @return mixed|stdClass
 */
function holeSeitenLink($kLink)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return LinkHelper::getInstance()->getPageLink($kLink);
}

/**
 * @deprecated since 4.0
 * @param int $kLink
 * @return mixed|stdClass
 */
function holeSeitenLinkSprache($kLink)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return LinkHelper::getInstance()->getPageLinkLanguage($kLink);
}

/**
 * @return mixed
 * @deprecated since 4.03
 */
function gibNewsArchiv()
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return Shop::DB()->query(
        "SELECT tnews.kNews, tnews.kSprache, tnews.cKundengruppe, tnews.cBetreff, tnews.cText, tnews.cVorschauText, 
            tnews.cMetaTitle, tnews.cMetaDescription, tnews.cMetaKeywords, tnews.nAktiv, tnews.dErstellt, tseo.cSeo,
            count(tnewskommentar.kNewsKommentar) AS nNewsKommentarAnzahl, 
            DATE_FORMAT(tnews.dErstellt, '%d.%m.%Y  %H:%i') AS dErstellt_de,
            DATE_FORMAT(tnews.dGueltigVon, '%d.%m.%Y  %H:%i') AS dGueltigVon_de, 
            DATE_FORMAT(tnews.dGueltigBis, '%d.%m.%Y  %H:%i') AS dGueltigBis_de
            FROM tnews
            LEFT JOIN tnewskommentar 
                ON tnewskommentar.kNews = tnews.kNews
            LEFT JOIN tseo 
                ON tseo.cKey = 'kNews'
                AND tseo.kKey = tnews.kNews
                AND tseo.kSprache = " . Shop::getLanguage() . "
            WHERE tnews.kSprache = " . Shop::getLanguage() . "
                AND tnews.nAktiv = 1
                AND MONTH(tnews.dErstellt) = '" . date('m') . "'
                AND (
                    tnews.cKundengruppe LIKE '%;-1;%' 
                    OR FIND_IN_SET('" . Session::CustomerGroup()->getID()
                        . "', REPLACE(tnews.cKundengruppe, ';', ',')) > 0
                    )
            GROUP BY tnews.kNews
            ORDER BY tnews.dErstellt DESC", 2
    );
}
