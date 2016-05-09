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
    $kKundengruppe = $_SESSION['Kundengruppe']->kKundengruppe;
    if (!$kKundengruppe || !$_SESSION['Kundengruppe']->darfArtikelKategorienSehen) {
        return array();
    }
    $Boxliste      = array();
    $schon_drin    = array();
    $Einstellungen = Shop::getSettings(array(CONF_STARTSEITE));
    while ($obj = gibNextBoxPrio($schon_drin, $Einstellungen)) {
        $schon_drin[] = $obj->name;
        $Boxliste[]   = $obj;
    }
    $Boxliste = array_reverse($Boxliste);
    $boxCount = count($Boxliste);
    for ($i = 0; $i < $boxCount; $i++) {
        $kArtikel_arr = array();
        $limit_nr     = $Boxliste[$i]->anzahl;
        $menge        = null;
        switch ($Boxliste[$i]->name) {
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
            } elseif ($rndkeys == 0) {
                $kArtikel_arr[] = $menge[0]->kArtikel;
            }
        }
        if (count($kArtikel_arr) > 0) {
            $Boxliste[$i]->cURL    = baueSuchSpecialURL($cURL);
            //hole anzuzeigende Artikel
            $Boxliste[$i]->Artikel = new ArtikelListe();
            $Boxliste[$i]->Artikel->getArtikelByKeys($kArtikel_arr, 0, count($kArtikel_arr));
        }
    }

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
            $oSpracheStd            = gibStandardsprache(true);
            $oAuswahlAssistentFrage = gibAAFrage($_SESSION['AuswahlAssistent']['nFrage'], $_SESSION['kSprache'], $oSpracheStd->kSprache);

            return $oAuswahlAssistentFrage;
        }
    } else {
        unset($_SESSION['AuswahlAssistent']);
    }
}

/**
 * @param array $Einstellungen
 * @return array|mixed
 */
function gibNews($Einstellungen)
{
    $cSQL      = '';
    $oNews_arr = array();
    // Sollen keine News auf der Startseite angezeigt werden?
    if (isset($Einstellungen['news']['news_anzahl_content']) && strlen($Einstellungen['news']['news_anzahl_content']) > 0 && intval($Einstellungen['news']['news_anzahl_content']) === 0) {
        return $oNews_arr;
    }
    $cacheID = 'news_' . md5(json_encode($Einstellungen['news']) . '_' . $_SESSION['kSprache']);

    if (($oNews_arr = Shop::Cache()->get($cacheID)) === false) {
        if (intval($Einstellungen['news']['news_anzahl_content']) > 0) {
            $cSQL = ' LIMIT ' . intval($Einstellungen['news']['news_anzahl_content']);
        }
        $oNews_arr = Shop::DB()->query(
            "SELECT tnews.kNews, tnews.kSprache, tnews.cKundengruppe, tnews.cBetreff, tnews.cText, tnews.cVorschauText, tnews.cMetaTitle,
                tnews.cMetaDescription, tnews.cMetaKeywords, tnews.nAktiv, tnews.dErstellt, tnews.cPreviewImage, tseo.cSeo,
                count(tnewskommentar.kNewsKommentar) AS nNewsKommentarAnzahl, DATE_FORMAT(tnews.dGueltigVon, '%d.%m.%Y  %H:%i') AS dErstellt_de,
                DATE_FORMAT(tnews.dGueltigVon, '%d.%m.%Y  %H:%i') AS dGueltigVon_de
                FROM tnews
                LEFT JOIN tnewskommentar ON tnewskommentar.kNews = tnews.kNews
                    AND tnewskommentar.nAktiv = 1
                LEFT JOIN tseo ON tseo.cKey = 'kNews'
                    AND tseo.kKey = tnews.kNews
                    AND tseo.kSprache = " . intval($_SESSION['kSprache']) . "
                WHERE tnews.kSprache = " . intval($_SESSION['kSprache']) . "
                    AND tnews.nAktiv = 1
                    AND tnews.dGueltigVon <= now()
                    AND (tnews.cKundengruppe LIKE '%;-1;%' OR tnews.cKundengruppe LIKE '%;" . $_SESSION['Kundengruppe']->kKundengruppe . ";%')
                GROUP BY tnews.kNews
                ORDER BY tnews.dGueltigVon DESC" . $cSQL, 2
        );
        // URLs bauen
        if (count($oNews_arr) > 0) {
            if (is_array($oNews_arr) && count($oNews_arr) > 0) {
                foreach ($oNews_arr as $i => $oNews) {
                    $oNews_arr[$i]->cText    = parseNewsText($oNews_arr[$i]->cText);
                    $oNews_arr[$i]->cURL     = baueURL($oNews, URLART_NEWS);
                    $oNews_arr[$i]->cMehrURL = '<a href="' . $oNews_arr[$i]->cURL . '">' . Shop::Lang()->get('moreLink', 'news') . '</a>';
                }
            }
        }
        Shop::Cache()->set($cacheID, $oNews_arr, array(CACHING_GROUP_NEWS, CACHING_GROUP_OPTION));
    }

    return $oNews_arr;
}

/**
 * @param array $search
 * @param array $Einstellungen
 * @return null|stdClass
 */
function gibNextBoxPrio($search, $Einstellungen)
{
    $max       = -1;
    $obj       = new stdClass();
    $obj->name = '';
    if ($max < intval($Einstellungen['startseite']['startseite_bestseller_sortnr']) &&
        intval($Einstellungen['startseite']['startseite_bestseller_anzahl']) > 0 &&
        !in_array('Bestseller', $search)
    ) {
        $obj->name   = 'Bestseller';
        $obj->anzahl = intval($Einstellungen['startseite']['startseite_bestseller_anzahl']);
        $obj->sort   = intval($Einstellungen['startseite']['startseite_bestseller_sortnr']);
        $max         = intval($Einstellungen['startseite']['startseite_bestseller_sortnr']);
    }
    if ($max < intval($Einstellungen['startseite']['startseite_sonderangebote_sortnr']) &&
        intval($Einstellungen['startseite']['startseite_sonderangebote_anzahl']) > 0 &&
        !in_array('Sonderangebote', $search)
    ) {
        $obj->name   = 'Sonderangebote';
        $obj->anzahl = intval($Einstellungen['startseite']['startseite_sonderangebote_anzahl']);
        $obj->sort   = intval($Einstellungen['startseite']['startseite_sonderangebote_sortnr']);
        $max         = intval($Einstellungen['startseite']['startseite_sonderangebote_sortnr']);
    }
    if ($max < intval($Einstellungen['startseite']['startseite_topangebote_sortnr']) &&
        intval($Einstellungen['startseite']['startseite_topangebote_anzahl']) > 0 &&
        !in_array('TopAngebot', $search)
    ) {
        $obj->name   = 'TopAngebot';
        $obj->anzahl = intval($Einstellungen['startseite']['startseite_topangebote_anzahl']);
        $obj->sort   = intval($Einstellungen['startseite']['startseite_topangebote_sortnr']);
        $max         = intval($Einstellungen['startseite']['startseite_topangebote_sortnr']);
    }
    if ($max < intval($Einstellungen['startseite']['startseite_neuimsortiment_sortnr']) &&
        intval($Einstellungen['startseite']['startseite_neuimsortiment_anzahl']) > 0 &&
        !in_array('NeuImSortiment', $search)
    ) {
        $obj->name   = 'NeuImSortiment';
        $obj->anzahl = intval($Einstellungen['startseite']['startseite_neuimsortiment_anzahl']);
        $obj->sort   = intval($Einstellungen['startseite']['startseite_neuimsortiment_sortnr']);
    }

    return (strlen($obj->name) > 0) ? $obj : null;
}

/**
 * @param array $Einstellungen
 * @return array
 */
function gibLivesucheTop($Einstellungen)
{
    $limit = (isset($Einstellungen['sonstiges']['sonstiges_livesuche_all_top_count']) && intval($Einstellungen['sonstiges']['sonstiges_livesuche_all_top_count']) > 0) ?
        intval($Einstellungen['sonstiges']['sonstiges_livesuche_all_top_count']) :
        100;
    $suchwolke_objs = Shop::DB()->query(
        "SELECT tsuchanfrage.kSuchanfrage, tsuchanfrage.kSprache, tsuchanfrage.cSuche, tsuchanfrage.nAktiv, tsuchanfrage.nAnzahlTreffer,
            tsuchanfrage.nAnzahlGesuche, DATE_FORMAT(tsuchanfrage.dZuletztGesucht, '%d.%m.%Y  %H:%i') AS dZuletztGesucht_de, tseo.cSeo
            FROM tsuchanfrage
            LEFT JOIN tseo ON tseo.cKey = 'kSuchanfrage' AND tseo.kKey = tsuchanfrage.kSuchanfrage AND tseo.kSprache = " . intval($_SESSION['kSprache']) . "
            WHERE tsuchanfrage.kSprache = " . intval($_SESSION['kSprache']) . "
                AND tsuchanfrage.nAktiv = 1
            ORDER BY tsuchanfrage.nAnzahlGesuche DESC
            LIMIT " . $limit, 2
    );
    // Priorität berechnen
    $count         = count($suchwolke_objs);
    $Suchwolke_arr = array();
    $prio_step     = ($count > 0) ?
        (($suchwolke_objs[0]->nAnzahlGesuche - $suchwolke_objs[$count - 1]->nAnzahlGesuche) / 9) :
        0;
    if (is_array($suchwolke_objs) && count($suchwolke_objs) > 0) {
        foreach ($suchwolke_objs as $suchwolke) {
            if ($suchwolke->kSuchanfrage > 0) {
                $suchwolke->Klasse = ($prio_step < 1) ?
                    rand(1, 10) :
                    (round(($suchwolke->nAnzahlGesuche - $suchwolke_objs[$count - 1]->nAnzahlGesuche) / $prio_step) + 1);
                $suchwolke->cURL = baueURL($suchwolke, URLART_LIVESUCHE);
                $Suchwolke_arr[] = $suchwolke;
            }
        }
    }

    return (count($Suchwolke_arr) > 0) ? $Suchwolke_arr : array();
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
    if ($a->nAnzahlGesuche > $b->nAnzahlGesuche) {
        return -1;
    }

    return 0;
}

/**
 * @param array $Einstellungen
 * @return array
 */
function gibLivesucheLast($Einstellungen)
{
    $limit = (isset($Einstellungen['sonstiges']['sonstiges_livesuche_all_last_count']) && intval($Einstellungen['sonstiges']['sonstiges_livesuche_all_last_count']) > 0) ?
        intval($Einstellungen['sonstiges']['sonstiges_livesuche_all_last_count']) :
        100;
    $suchwolke_objs = Shop::DB()->query(
        "SELECT tsuchanfrage.kSuchanfrage, tsuchanfrage.kSprache, tsuchanfrage.cSuche, tsuchanfrage.nAktiv, tsuchanfrage.nAnzahlTreffer,
            tsuchanfrage.nAnzahlGesuche, DATE_FORMAT(tsuchanfrage.dZuletztGesucht, '%d.%m.%Y  %H:%i') AS dZuletztGesucht_de, tseo.cSeo
            FROM tsuchanfrage
            LEFT JOIN tseo ON tseo.cKey = 'kSuchanfrage' AND tseo.kKey = tsuchanfrage.kSuchanfrage AND tseo.kSprache = " . intval($_SESSION['kSprache']) . "
            WHERE tsuchanfrage.kSprache = " . intval($_SESSION['kSprache']) . "
                AND tsuchanfrage.nAktiv = 1
            ORDER BY tsuchanfrage.dZuletztGesucht DESC
            LIMIT " . $limit, 2
    );
    // Priorität berechnen
    $count         = count($suchwolke_objs);
    $Suchwolke_arr = array();
    $prio_step     = ($count > 0) ?
        (($suchwolke_objs[0]->nAnzahlGesuche - $suchwolke_objs[$count - 1]->nAnzahlGesuche) / 9) :
        0;
    if (is_array($suchwolke_objs) && count($suchwolke_objs) > 0) {
        foreach ($suchwolke_objs as $suchwolke) {
            if ($suchwolke->kSuchanfrage > 0) {
                $suchwolke->Klasse = ($prio_step < 1) ?
                    rand(1, 10) :
                    round(($suchwolke->nAnzahlGesuche - $suchwolke_objs[$count - 1]->nAnzahlGesuche) / $prio_step) + 1;
                $suchwolke->cURL = baueURL($suchwolke, URLART_LIVESUCHE);
                $Suchwolke_arr[] = $suchwolke;
            }
        }
    }

    return (count($Suchwolke_arr) > 0) ? $Suchwolke_arr : array();
}

/**
 * @param array $Einstellungen
 * @return array
 */
function gibTagging($Einstellungen)
{
    $limit = (isset($Einstellungen['sonstiges']['sonstiges_tagging_all_count']) && intval($Einstellungen['sonstiges']['sonstiges_tagging_all_count']) > 0) ?
        intval($Einstellungen['sonstiges']['sonstiges_tagging_all_count']) :
        100;
    $limit         = ' limit ' . $limit;
    $tagwolke_objs = Shop::DB()->query(
        "SELECT ttag.kTag, ttag.cName, tseo.cSeo, sum(ttagartikel.nAnzahlTagging) AS Anzahl
            FROM ttag
            JOIN ttagartikel ON ttagartikel.kTag = ttag.kTag
            LEFT JOIN tseo ON tseo.cKey = 'kTag' AND tseo.kKey = ttag.kTag AND tseo.kSprache = " . intval($_SESSION['kSprache']) . "
            WHERE ttag.nAktiv = 1
                AND ttag.kSprache = " . intval($_SESSION['kSprache']) . "
            GROUP BY ttag.cName
            ORDER BY Anzahl DESC" . $limit, 2
    );
    // Priorität berechnen
    $count        = count($tagwolke_objs);
    $Tagwolke_arr = array();
    $prio_step    = ($count > 0) ?
        (($tagwolke_objs[0]->Anzahl - $tagwolke_objs[$count - 1]->Anzahl) / 9) :
        0;
    if (is_array($tagwolke_objs) && count($tagwolke_objs) > 0) {
        foreach ($tagwolke_objs as $tagwolke) {
            if ($tagwolke->kTag > 0) {
                $tagwolke->Klasse = ($prio_step < 1) ?
                    rand(1, 10) :
                    (round(($tagwolke->Anzahl - $tagwolke_objs[$count - 1]->Anzahl) / $prio_step) + 1);
                $tagwolke->cURL = baueURL($tagwolke, URLART_TAG);
                $Tagwolke_arr[] = $tagwolke;
            }
        }
    }
    if (count($Tagwolke_arr) > 0) {
        shuffle($Tagwolke_arr);

        return $Tagwolke_arr;
    }

    return array();
}

/**
 * @return mixed
 */
function gibNewsletterHistory()
{
    $oNewsletterHistory_arr = Shop::DB()->query(
        "SELECT kNewsletterHistory, cBetreff, DATE_FORMAT(dStart, '%d.%m.%Y %H:%i') AS Datum, cHTMLStatic
            FROM tnewsletterhistory
            WHERE kSprache = " . intval($_SESSION['kSprache']) . "
            ORDER BY dStart DESC", 2
    );
    // URLs bauen
    if (is_array($oNewsletterHistory_arr) && count($oNewsletterHistory_arr) > 0) {
        foreach ($oNewsletterHistory_arr as $i => $oNewsletterHistory) {
            $oNewsletterHistory_arr[$i]->cURL = baueURL($oNewsletterHistory, URLART_NEWS);
        }
    }

    return $oNewsletterHistory_arr;
}

/**
 * @return KategorieListe
 */
function gibSitemapKategorien()
{
    $oKategorieliste = new KategorieListe();
    $oKategorieliste->holKategorienAufEinenBlick(3, $_SESSION['Kundengruppe']->kKundengruppe, $_SESSION['kSprache']);

    return $oKategorieliste;
}

/**
 * @return array
 */
function gibSitemapGlobaleMerkmale()
{
    $isDefaultLanguage = standardspracheAktiv();
    $cacheID           = 'gsgm_' . (($isDefaultLanguage === true) ? 'd_' : '') . intval($_SESSION['kSprache']);
    if (($oMerkmal_arr = Shop::Cache()->get($cacheID)) === false) {
        $oMerkmal_arr    = array();
        $cDatei          = 'navi.php';
        $cMerkmalTabelle = 'tmerkmal';
        $cSQL            = " JOIN tmerkmalwert ON tmerkmalwert.kMerkmal = tmerkmal.kMerkmal";
        $cSQL .= " JOIN tmerkmalwertsprache ON tmerkmalwertsprache.kMerkmalWert = tmerkmalwert.kMerkmalWert";
        $cMerkmalWhere = '';
        if ($isDefaultLanguage === false) {
            $cMerkmalTabelle = "tmerkmalsprache";
            $cSQL            = " JOIN tmerkmal ON tmerkmal.kMerkmal = tmerkmalsprache.kMerkmal";
            $cSQL .= " JOIN tmerkmalwert ON tmerkmalwert.kMerkmal = tmerkmal.kMerkmal";
            $cSQL .= " JOIN tmerkmalwertsprache ON tmerkmalwertsprache.kMerkmalWert = tmerkmalwert.kMerkmalWert";
            $cMerkmalWhere = " AND tmerkmalsprache.kSprache = " . intval($_SESSION['kSprache']);
        }
        $oMerkmalTMP_arr = Shop::DB()->query(
            "SELECT {$cMerkmalTabelle}.*, tmerkmalwertsprache.cWert, tseo.cSeo, tmerkmalwertsprache.kMerkmalWert, tmerkmal.nSort, tmerkmal.nGlobal, tmerkmal.cTyp, tmerkmalwert.cBildPfad AS cBildPfadMW, tmerkmal.cBildpfad
                FROM {$cMerkmalTabelle}
                {$cSQL}
                JOIN tartikelmerkmal ON tartikelmerkmal.kMerkmalWert = tmerkmalwertsprache.kMerkmalWert
                LEFT JOIN tseo ON tseo.cKey = 'kMerkmalWert'
                    AND tseo.kKey = tmerkmalwertsprache.kMerkmalWert
                    AND tseo.kSprache = {$_SESSION['kSprache']}
                WHERE tmerkmal.nGlobal = 1
                    AND tmerkmalwertsprache.kSprache = {$_SESSION['kSprache']}
                    {$cMerkmalWhere}
                GROUP BY tmerkmalwertsprache.kMerkmalWert
                ORDER BY tmerkmal.nSort, {$cMerkmalTabelle}.cName, tmerkmalwert.nSort, tmerkmalwertsprache.cWert", 2
        );
        $nPos = 0;
        if (is_array($oMerkmalTMP_arr) && count($oMerkmalTMP_arr) > 0) {
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
                        $oMerkmalWert->cURL = (strlen($oMerkmalWert->cSeo) > 0) ?
                            Shop::getURL() . '/' . $oMerkmalWert->cSeo :
                            Shop::getURL() . '/' . $cDatei . '?m=' . $oMerkmalWert->kMerkmalWert;

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
                        $oMerkmalWert->cURL = (strlen($oMerkmalWert->cSeo) > 0) ?
                            Shop::getURL() . '/' . $oMerkmalWert->cSeo :
                            Shop::getURL() . '/' . $cDatei . '?m=' . $oMerkmalWert->kMerkmalWert;

                        $oMerkmal->oMerkmalWert_arr[] = $oMerkmalWert;
                        $oMerkmal_arr[]               = $oMerkmal;

                        $nPos++;
                    }
                } else { // Erster Durchlauf
                    $oMerkmal->kMerkmal         = (isset($oMerkmalTMP->kMerkmal)) ? $oMerkmalTMP->kMerkmal : null;
                    $oMerkmal->cName            = (isset($oMerkmalTMP->cName)) ? $oMerkmalTMP->cName : null;
                    $oMerkmal->nSort            = (isset($oMerkmalTMP->nSort)) ? $oMerkmalTMP->nSort : null;
                    $oMerkmal->nGlobal          = (isset($oMerkmalTMP->nGlobal)) ? $oMerkmalTMP->nGlobal : null;
                    $oMerkmal->cBildpfad        = (isset($oMerkmalTMP->cBildpfad)) ? $oMerkmalTMP->cBildpfad : null;
                    $oMerkmal->cTyp             = (isset($oMerkmalTMP->cTyp)) ? $oMerkmalTMP->cTyp : null;
                    $oMerkmal->oMerkmalWert_arr = array();

                    verarbeiteMerkmalBild($oMerkmal);
                    $oMerkmalWert->kMerkmalWert = $oMerkmalTMP->kMerkmalWert;
                    $oMerkmalWert->cWert        = $oMerkmalTMP->cWert;
                    $oMerkmalWert->cSeo         = $oMerkmalTMP->cSeo;
                    $oMerkmalWert->cBildPfadMW  = $oMerkmalTMP->cBildPfadMW;

                    verarbeiteMerkmalWertBild($oMerkmalWert);
                    // cURL bauen
                    $oMerkmalWert->cURL = (strlen($oMerkmalWert->cSeo) > 0) ?
                        Shop::getURL() . '/' . $oMerkmalWert->cSeo :
                        Shop::getURL() . '/' . $cDatei . '?m=' . $oMerkmalWert->kMerkmalWert;
                    $oMerkmal->oMerkmalWert_arr[] = $oMerkmalWert;
                    $oMerkmal_arr[]               = $oMerkmal;
                }
            }
        }
        Shop::Cache()->set($cacheID, $oMerkmal_arr, array(CACHING_GROUP_CATEGORY));
    }

    return $oMerkmal_arr;
}

/**
 * @param object $oMerkmal
 */
function verarbeiteMerkmalBild(&$oMerkmal)
{
    $oMerkmal->cBildpfadKlein      = BILD_KEIN_MERKMALBILD_VORHANDEN;
    $oMerkmal->nBildKleinVorhanden = 0;
    $oMerkmal->cBildpfadGross      = BILD_KEIN_MERKMALBILD_VORHANDEN;
    $oMerkmal->nBildGrossVorhanden = 0;
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
}

/**
 * @param object $oMerkmalWert
 */
function verarbeiteMerkmalWertBild(&$oMerkmalWert)
{
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
}

/**
 * @param array $BoxenEinstellungen
 * @return mixed
 */
function gibBoxNews($BoxenEinstellungen)
{
    $nBoxenLimit = (intval($BoxenEinstellungen['news']['news_anzahl_box']) > 0) ?
        intval($BoxenEinstellungen['news']['news_anzahl_box']) :
        3;

    return Shop::DB()->query(
        "SELECT DATE_FORMAT(dErstellt, '%M, %Y') AS Datum, count(*) AS nAnzahl, DATE_FORMAT(dErstellt, '%m') AS nMonat
            FROM tnews
            WHERE kSprache=" . intval($_SESSION['kSprache']) . "
                AND nAktiv=1
                AND (cKundengruppe LIKE '%;-1;%' OR cKundengruppe LIKE '%;" . intval($_SESSION['Kundengruppe']->kKundengruppe) . ";%')
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
    if (($oNewsMonatsUebersicht_arr = Shop::Cache()->get($cacheID)) === false) {
        $oNewsMonatsUebersicht_arr = Shop::DB()->query(
            "SELECT tseo.cSeo, tnewsmonatsuebersicht.cName, tnewsmonatsuebersicht.kNewsMonatsUebersicht, month(tnews.dGueltigVon) AS nMonat, year( tnews.dGueltigVon ) AS nJahr, count(*) AS nAnzahl
                FROM tnews
                JOIN tnewsmonatsuebersicht ON tnewsmonatsuebersicht.nMonat = month(tnews.dGueltigVon)
                    AND tnewsmonatsuebersicht.nJahr = year(tnews.dGueltigVon)
                    AND tnewsmonatsuebersicht.kSprache =1
                LEFT JOIN tseo ON cKey = 'kNewsMonatsUebersicht'
                    AND kKey = tnewsmonatsuebersicht.kNewsMonatsUebersicht
                    AND tseo.kSprache = " . intval($_SESSION['kSprache']) . "
                WHERE tnews.dGueltigVon < now()
                    AND tnews.nAktiv = 1
                    AND tnews.kSprache = " . intval($_SESSION['kSprache']) . "
                GROUP BY year(tnews.dGueltigVon) , month(tnews.dGueltigVon)
                ORDER BY tnews.dGueltigVon DESC", 2
        );
        if (is_array($oNewsMonatsUebersicht_arr) && count($oNewsMonatsUebersicht_arr) > 0) {
            foreach ($oNewsMonatsUebersicht_arr as $i => $oNewsMonatsUebersicht) {
                $oNews_arr = Shop::DB()->query(
                    "SELECT tnews.kNews, tnews.kSprache, tnews.cKundengruppe, tnews.cBetreff, tnews.cText, tnews.cVorschauText, tnews.cMetaTitle,
                        tnews.cMetaDescription, tnews.cMetaKeywords, tnews.nAktiv, tnews.dErstellt, tseo.cSeo,
                        count(tnewskommentar.kNewsKommentar) AS nNewsKommentarAnzahl, DATE_FORMAT(tnews.dGueltigVon, '%d.%m.%Y  %H:%i') AS dGueltigVon_de
                        FROM tnews
                        LEFT JOIN tnewskommentar ON tnews.kNews = tnewskommentar.kNews
                        LEFT JOIN tseo ON tseo.cKey = 'kNews'
                            AND tseo.kKey = tnews.kNews
                            AND tseo.kSprache = " . intval($_SESSION['kSprache']) . "
                        WHERE tnews.kSprache = " . intval($_SESSION['kSprache']) . "
                            AND tnews.nAktiv = 1
                            AND (tnews.cKundengruppe LIKE '%;-1;%' OR tnews.cKundengruppe LIKE '%;" . intval($_SESSION['Kundengruppe']->kKundengruppe) . ";%')
                            AND (MONTH(tnews.dGueltigVon) = '" . $oNewsMonatsUebersicht->nMonat . "') && (tnews.dGueltigVon <= now())
                            AND (YEAR(tnews.dGueltigVon) = '" . $oNewsMonatsUebersicht->nJahr . "') && (tnews.dGueltigVon <= now())
                        GROUP BY tnews.kNews
                        ORDER BY dGueltigVon DESC", 2
                );
                // cURL bauen
                if (is_array($oNews_arr) && count($oNews_arr) > 0) {
                    foreach ($oNews_arr as $j => $oNews) {
                        $oNews_arr[$j]->cURL = baueURL($oNews, URLART_NEWS);
                    }
                }
                $oNewsMonatsUebersicht_arr[$i]->oNews_arr = $oNews_arr;
                $oNewsMonatsUebersicht_arr[$i]->cURL      = baueURL($oNewsMonatsUebersicht, URLART_NEWSMONAT);
            }
        }
        Shop::Cache()->set($cacheID, $oNewsMonatsUebersicht_arr, array(CACHING_GROUP_NEWS));
    }

    return $oNewsMonatsUebersicht_arr;
}

/**
 * @return mixed
 */
function gibNewsKategorie()
{
    $cacheID = 'news_category_' . $_SESSION['kSprache'] . '_' . $_SESSION['Kundengruppe']->kKundengruppe;
    if (($oNewsKategorie_arr = Shop::Cache()->get($cacheID)) === false) {
        $oNewsKategorie_arr = Shop::DB()->query(
            "SELECT tnewskategorie.kNewsKategorie, tnewskategorie.kSprache, tnewskategorie.cName,
                tnewskategorie.cBeschreibung, tnewskategorie.cMetaTitle, tnewskategorie.cMetaDescription,
                tnewskategorie.nSort, tnewskategorie.nAktiv, tnewskategorie.dLetzteAktualisierung, tseo.cSeo,
                count(DISTINCT(tnewskategorienews.kNews)) AS nAnzahlNews
                FROM tnewskategorie
                LEFT JOIN tnewskategorienews ON tnewskategorienews.kNewsKategorie = tnewskategorie.kNewsKategorie
                LEFT JOIN tnews ON tnews.kNews = tnewskategorienews.kNews
                LEFT JOIN tseo ON tseo.cKey = 'kNewsKategorie'
                    AND tseo.kKey = tnewskategorie.kNewsKategorie
                    AND tseo.kSprache = " . intval($_SESSION['kSprache']) . "
                WHERE tnewskategorie.kSprache = " . intval($_SESSION['kSprache']) . "
                    AND tnewskategorie.nAktiv = 1
                    AND tnews.nAktiv = 1
                    AND tnews.dGueltigVon <= now()
                    AND (tnews.cKundengruppe LIKE '%;-1;%' OR tnews.cKundengruppe LIKE '%;" . intval($_SESSION['Kundengruppe']->kKundengruppe) . ";%')
                GROUP BY tnewskategorienews.kNewsKategorie
                ORDER BY tnewskategorie.nSort DESC", 2
        );
        if (is_array($oNewsKategorie_arr) && count($oNewsKategorie_arr) > 0) {
            foreach ($oNewsKategorie_arr as $i => $oNewsKategorie) {
                $oNewsKategorie_arr[$i]->oNews_arr = array();
                $oNewsKategorie_arr[$i]->cURL      = baueURL($oNewsKategorie, URLART_NEWSKATEGORIE);

                $oNews_arr = Shop::DB()->query(
                    "SELECT tnews.kNews, tnews.kSprache, tnews.cKundengruppe, tnews.cBetreff, tnews.cText, tnews.cVorschauText, tnews.cMetaTitle,
                        tnews.cMetaDescription, tnews.cMetaKeywords, tnews.nAktiv, tnews.dErstellt, tseo.cSeo,
                        DATE_FORMAT(tnews.dGueltigVon, '%d.%m.%Y  %H:%i') AS dGueltigVon_de
                        FROM tnews
                        JOIN tnewskategorienews ON tnewskategorienews.kNews = tnews.kNews
                        LEFT JOIN tseo ON tseo.cKey = 'kNews'
                            AND tseo.kKey = tnews.kNews
                            AND tseo.kSprache = " . intval($_SESSION['kSprache']) . "
                        WHERE tnews.kSprache = " . intval($_SESSION['kSprache']) . "
                            AND tnewskategorienews.kNewsKategorie = " . $oNewsKategorie->kNewsKategorie . "
                            AND tnews.nAktiv = 1
                            AND tnews.dGueltigVon <= now()
                            AND (tnews.cKundengruppe LIKE '%;-1;%' OR tnews.cKundengruppe LIKE '%;" . intval($_SESSION['Kundengruppe']->kKundengruppe) . ";%')
                        GROUP BY tnews.kNews
                        ORDER BY tnews.dGueltigVon DESC", 2
                );
                // Baue cURL
                if (is_array($oNews_arr) && count($oNews_arr) > 0) {
                    foreach ($oNews_arr as $j => $oNews) {
                        $oNews_arr[$j]->cURL = baueURL($oNews, URLART_NEWS);
                    }
                }

                $oNewsKategorie_arr[$i]->oNews_arr = $oNews_arr;
            }
        }
        Shop::Cache()->set($cacheID, $oNewsKategorie_arr, array(CACHING_GROUP_NEWS));
    }

    return $oNewsKategorie_arr;
}

/**
 * @param array $Einstellungen
 * @return array
 */
function gibGratisGeschenkArtikel($Einstellungen)
{
    $oArtikelGeschenk_arr = array();
    $cSQLSort             = " ORDER BY CAST(tartikelattribut.cWert AS DECIMAL) DESC";
    if ($Einstellungen['sonstiges']['sonstiges_gratisgeschenk_sortierung'] === 'N') {
        $cSQLSort = " ORDER BY tartikel.cName";
    } elseif ($Einstellungen['sonstiges']['sonstiges_gratisgeschenk_sortierung'] === 'L') {
        $cSQLSort = " ORDER BY tartikel.fLagerbestand DESC";
    }
    $cSQLLimit = (intval($Einstellungen['sonstiges']['sonstiges_gratisgeschenk_anzahl']) > 0) ?
        " LIMIT " . intval($Einstellungen['sonstiges']['sonstiges_gratisgeschenk_anzahl']) :
        '';
    $oArtikelGeschenkTMP_arr = Shop::DB()->query(
        "SELECT tartikel.kArtikel, tartikelattribut.cWert
            FROM tartikel
            JOIN tartikelattribut ON tartikelattribut.kArtikel = tartikel.kArtikel
            LEFT JOIN tartikelsichtbarkeit ON tartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                    AND tartikelsichtbarkeit.kKundengruppe = {$_SESSION['Kundengruppe']->kKundengruppe}
            WHERE tartikelsichtbarkeit.kArtikel IS NULL
            AND tartikelattribut.cName = '" . FKT_ATTRIBUT_GRATISGESCHENK . "' " . gibLagerfilter() . $cSQLSort . $cSQLLimit, 2
    );

    if (is_array($oArtikelGeschenkTMP_arr) && count($oArtikelGeschenkTMP_arr) > 0) {
        foreach ($oArtikelGeschenkTMP_arr as $i => $oArtikelGeschenkTMP) {
            $oArtikel = new Artikel();
            $oArtikel->fuelleArtikel($oArtikelGeschenkTMP->kArtikel, Artikel::getDefaultOptions());
            $oArtikel->cBestellwert = gibPreisStringLocalized(doubleval($oArtikelGeschenkTMP->cWert));

            if ($oArtikel->kEigenschaftKombi > 0 || !is_array($oArtikel->Variationen) || count($oArtikel->Variationen) === 0) {
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
    if (intval($nLinkart) > 0) {
        $cacheID = 'special_page_n_' . $nLinkart;
        if (($oSeite = Shop::Cache()->get($cacheID)) === false) {
            $oSeite = Shop::DB()->query("SELECT * FROM tspezialseite WHERE nLinkart = " . intval($nLinkart), 1);
            Shop::Cache()->set($cacheID, $oSeite, array(CACHING_GROUP_CORE));
        }
        if (isset($oSeite->cDateiname) && strlen($oSeite->cDateiname) > 0) {
            header('Location: ' . Shop::getURL() . '/' . $oSeite->cDateiname);
            exit();
        }
    }

    return;
}

/**
 * @param array $Einstellungen
 * @param JTLSmarty $smarty
 */
function gibSeiteSitemap($Einstellungen, &$smarty)
{
    Shop::setPageType(PAGE_SITEMAP);
    $linkHelper             = LinkHelper::getInstance();
    $linkGroups             = $linkHelper->getLinkGroups();
    $cLinkgruppenMember_arr = array();
    if (isset($linkGroups) && is_object($linkGroups)) {
        $cLinkgruppenMemberTMP_arr = get_object_vars($linkGroups);
        if (is_array($cLinkgruppenMemberTMP_arr) && count($cLinkgruppenMemberTMP_arr) > 0) {
            foreach ($cLinkgruppenMemberTMP_arr as $cLinkgruppe => $cLinkgruppenMemberTMP) {
                $cLinkgruppenMember_arr[] = $cLinkgruppe;
            }
        }
    }
    // Smarty Hilfe um die Linksgruppen dynamisch zu bauen
    $smarty->assign('cLinkgruppenMember_arr', $cLinkgruppenMember_arr);

    if ($Einstellungen['sitemap']['sitemap_kategorien_anzeigen'] === 'Y') {
        $smarty->assign('oKategorieliste', gibSitemapKategorien());
    }
    if ($Einstellungen['sitemap']['sitemap_globalemerkmale_anzeigen'] === 'Y') {
        $smarty->assign('oGlobaleMerkmale_arr', gibSitemapGlobaleMerkmale());
    }
    if ($Einstellungen['sitemap']['sitemap_hersteller_anzeigen'] === 'Y') {
        $smarty->assign('oHersteller_arr', Hersteller::getAll());
    }
    if ($Einstellungen['news']['news_benutzen'] === 'Y' && $Einstellungen['sitemap']['sitemap_news_anzeigen'] === 'Y') {
        $smarty->assign('oNewsMonatsUebersicht_arr', gibSitemapNews());
    }
    if ($Einstellungen['sitemap']['sitemap_newskategorien_anzeigen'] === 'Y') {
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
    return Hersteller::getAll();
}

/**
 * @deprecated since 4.0
 * @param int $kLink
 * @return mixed|stdClass
 */
function holeSeitenLink($kLink)
{
    $linkHelper = LinkHelper::getInstance();

    return $linkHelper->getPageLink($kLink);
}

/**
 * @deprecated since 4.0
 * @param int $kLink
 * @return mixed|stdClass
 */
function holeSeitenLinkSprache($kLink)
{
    $linkHelper = LinkHelper::getInstance();

    return $linkHelper->getPageLinkLanguage($kLink);
}

/**
 * @return mixed
 * @deprecated since 4.03
 */
function gibNewsArchiv()
{
    return Shop::DB()->query(
        "SELECT tnews.kNews, tnews.kSprache, tnews.cKundengruppe, tnews.cBetreff, tnews.cText, tnews.cVorschauText, tnews.cMetaTitle,
            tnews.cMetaDescription, tnews.cMetaKeywords, tnews.nAktiv, tnews.dErstellt, tseo.cSeo,
            count(tnewskommentar.kNewsKommentar) AS nNewsKommentarAnzahl, DATE_FORMAT(tnews.dErstellt, '%d.%m.%Y  %H:%i') AS dErstellt_de,
            DATE_FORMAT(tnews.dGueltigVon, '%d.%m.%Y  %H:%i') AS dGueltigVon_de, DATE_FORMAT(tnews.dGueltigBis, '%d.%m.%Y  %H:%i') AS dGueltigBis_de
            FROM tnews
            LEFT JOIN tnewskommentar ON tnewskommentar.kNews = tnews.kNews
            LEFT JOIN tseo ON tseo.cKey = 'kNews'
                AND tseo.kKey = tnews.kNews
                AND tseo.kSprache = " . intval($_SESSION['kSprache']) . "
            WHERE tnews.kSprache = " . intval($_SESSION['kSprache']) . "
                AND tnews.nAktiv = 1
                AND MONTH(tnews.dErstellt)='" . date('m') . "'
                AND (tnews.cKundengruppe LIKE '%;-1;%' OR tnews.cKundengruppe LIKE '%;" . intval($_SESSION['Kundengruppe']->kKundengruppe) . ";%')
            GROUP BY tnews.kNews
            ORDER BY tnews.dErstellt DESC", 2
    );
}
