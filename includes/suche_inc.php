<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * @return array
 */
function gibSuchSpalten()
{
    $cSuchspalten_arr = [];
    for ($i = 0; $i < 10; ++$i) {
        $cSuchspalten_arr[] = gibMaxPrioSpalte($cSuchspalten_arr);
    }
    // Leere Spalten entfernen
    if (is_array($cSuchspalten_arr) && count($cSuchspalten_arr) > 0) {
        foreach ($cSuchspalten_arr as $i => $cSuchspalten) {
            if (strlen($cSuchspalten) === 0) {
                unset($cSuchspalten_arr[$i]);
            }
        }
        $cSuchspalten_arr = array_merge($cSuchspalten_arr);
    }

    return $cSuchspalten_arr;
}

/**
 * @param array $exclude
 * @return string
 */
function gibMaxPrioSpalte($exclude)
{
    $max             = 0;
    $aktEle          = '';
    $cTabellenPrefix = 'tartikel.';
    $conf            = Shop::getSettings([CONF_ARTIKELUEBERSICHT]);

    if (!standardspracheAktiv()) {
        $cTabellenPrefix = 'tartikelsprache.';
    }
    if (!in_array($cTabellenPrefix . 'cName', $exclude, true) && $conf['artikeluebersicht']['suche_prio_name'] > $max) {
        $max    = $conf['artikeluebersicht']['suche_prio_name'];
        $aktEle = $cTabellenPrefix . 'cName';
    }
    if (!in_array($cTabellenPrefix . 'cSeo', $exclude, true) && $conf['artikeluebersicht']['suche_prio_name'] > $max) {
        $max    = $conf['artikeluebersicht']['suche_prio_name'];
        $aktEle = $cTabellenPrefix . 'cSeo';
    }
    if (!in_array('tartikel.cSuchbegriffe', $exclude, true) && $conf['artikeluebersicht']['suche_prio_suchbegriffe'] > $max) {
        $max    = $conf['artikeluebersicht']['suche_prio_suchbegriffe'];
        $aktEle = 'tartikel.cSuchbegriffe';
    }
    if (!in_array('tartikel.cArtNr', $exclude, true) && $conf['artikeluebersicht']['suche_prio_artikelnummer'] > $max) {
        $max    = $conf['artikeluebersicht']['suche_prio_artikelnummer'];
        $aktEle = 'tartikel.cArtNr';
    }
    if (!in_array($cTabellenPrefix . 'cKurzBeschreibung', $exclude, true) && $conf['artikeluebersicht']['suche_prio_kurzbeschreibung'] > $max) {
        $max    = $conf['artikeluebersicht']['suche_prio_kurzbeschreibung'];
        $aktEle = $cTabellenPrefix . 'cKurzBeschreibung';
    }
    if (!in_array($cTabellenPrefix . 'cBeschreibung', $exclude, true) && $conf['artikeluebersicht']['suche_prio_beschreibung'] > $max) {
        $max    = $conf['artikeluebersicht']['suche_prio_beschreibung'];
        $aktEle = $cTabellenPrefix . 'cBeschreibung';
    }
    if (!in_array('tartikel.cBarcode', $exclude, true) && $conf['artikeluebersicht']['suche_prio_ean'] > $max) {
        $max    = $conf['artikeluebersicht']['suche_prio_ean'];
        $aktEle = 'tartikel.cBarcode';
    }
    if (!in_array('tartikel.cISBN', $exclude, true) && $conf['artikeluebersicht']['suche_prio_isbn'] > $max) {
        $max    = $conf['artikeluebersicht']['suche_prio_isbn'];
        $aktEle = 'tartikel.cISBN';
    }
    if (!in_array('tartikel.cHAN', $exclude, true) && $conf['artikeluebersicht']['suche_prio_han'] > $max) {
        $max    = $conf['artikeluebersicht']['suche_prio_han'];
        $aktEle = 'tartikel.cHAN';
    }
    if (!in_array('tartikel.cAnmerkung', $exclude, true) && $conf['artikeluebersicht']['suche_prio_anmerkung'] > $max) {
        $aktEle = 'tartikel.cAnmerkung';
    }

    return $aktEle;
}

/**
 * @param array $searchColumns
 * @return array
 * @deprecated since 4.06
 */
function gibSuchspaltenKlassen($searchColumns)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return Shop::getNaviFilter()->Suchanfrage->getSearchColumnClasses($searchColumns);
}

/**
 * @param array  $searchColumns
 * @param string $searchColumn
 * @param array  $nonAllowed
 * @return bool
 * @deprecated since 4.06
 */
function pruefeSuchspaltenKlassen($searchColumns, $searchColumn, $nonAllowed)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return Shop::getNaviFilter()->Suchanfrage->checkColumnClasses($searchColumns, $searchColumn, $nonAllowed);
}

/**
 * @param string $cSuche
 * @param int    $nAnzahlTreffer
 * @param bool   $bEchteSuche
 * @param int    $kSpracheExt
 * @param bool   $bSpamFilter
 * @return bool
 * @deprecated since 4.06
 */
function suchanfragenSpeichern($cSuche, $nAnzahlTreffer, $bEchteSuche = false, $kSpracheExt = 0, $bSpamFilter = true)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return Shop::getNaviFilter()->Suche->saveQuery($nAnzahlTreffer, $cSuche, $bEchteSuche, $kSpracheExt, $bSpamFilter);
}

/**
 * @param string $Suchausdruck
 * @param int    $kSpracheExt
 * @return mixed
 * @deprecated since 4.05
 */
function mappingBeachten($Suchausdruck, $kSpracheExt = 0)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $kSprache = ((int)$kSpracheExt > 0) ? (int)$kSpracheExt : getDefaultLanguageID();
    if (strlen($Suchausdruck) > 0) {
        $SuchausdruckmappingTMP = Shop::DB()->select(
            'tsuchanfragemapping',
            'kSprache',
            $kSprache,
            'cSuche',
            $Suchausdruck,
            null,
            null,
            false,
            'cSucheNeu'
        );
        $Suchausdruckmapping    = $SuchausdruckmappingTMP;
        while ($SuchausdruckmappingTMP !== null &&
            isset($SuchausdruckmappingTMP->cSucheNeu) &&
            strlen($SuchausdruckmappingTMP->cSucheNeu) > 0
        ) {
            $SuchausdruckmappingTMP = Shop::DB()->select(
                'tsuchanfragemapping',
                'kSprache',
                $kSprache,
                'cSuche',
                $SuchausdruckmappingTMP->cSucheNeu,
                null,
                null,
                false,
                'cSucheNeu'
            );
            if (isset($SuchausdruckmappingTMP->cSucheNeu) && strlen($SuchausdruckmappingTMP->cSucheNeu) > 0) {
                $Suchausdruckmapping = $SuchausdruckmappingTMP;
            }
        }
        if (isset($Suchausdruckmapping->cSucheNeu) && strlen($Suchausdruckmapping->cSucheNeu) > 0) {
            $Suchausdruck = $Suchausdruckmapping->cSucheNeu;
        }
    }

    return $Suchausdruck;
}

/**
 * @param string $query
 * @return array
 * @deprecated since 4.06
 */
function suchausdruckVorbereiten($query)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return Shop::getNaviFilter()->Suchanfrage->prepareSearchQuery($query);
}

/**
 * @param array $cSuch_arr
 * @return array
 * @deprecated since 4.06 - it's never used anyways
 */
function suchausdruckAlleKombis($cSuch_arr)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $cSuchTMP_arr = [];
    $cnt          = count($cSuch_arr);
    if ($cnt > 3 || $cnt === 1) {
        return [];
    }

    switch ($cnt) {
        case 2:
            $cSuchTMP_arr[] = $cSuch_arr[0] . ' ' . $cSuch_arr[1];
            $cSuchTMP_arr[] = $cSuch_arr[1] . ' ' . $cSuch_arr[0];
            break;
        case 3:
            $cSuchTMP_arr[] = $cSuch_arr[0] . ' ' . $cSuch_arr[1] . ' ' . $cSuch_arr[2];
            $cSuchTMP_arr[] = $cSuch_arr[0] . ' ' . $cSuch_arr[2] . ' ' . $cSuch_arr[1];
            $cSuchTMP_arr[] = $cSuch_arr[2] . ' ' . $cSuch_arr[1] . ' ' . $cSuch_arr[0];
            $cSuchTMP_arr[] = $cSuch_arr[2] . ' ' . $cSuch_arr[0] . ' ' . $cSuch_arr[1];
            $cSuchTMP_arr[] = $cSuch_arr[1] . ' ' . $cSuch_arr[0] . ' ' . $cSuch_arr[2];
            $cSuchTMP_arr[] = $cSuch_arr[1] . ' ' . $cSuch_arr[2] . ' ' . $cSuch_arr[0];
            break;
        default:
            break;
    }

    return $cSuchTMP_arr;
}
