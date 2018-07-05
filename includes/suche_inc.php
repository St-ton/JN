<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * @return array
 * @deprecated since 5.0.0
 */
function gibSuchSpalten()
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return \Filter\States\BaseSearchQuery::getSearchRows(Shop::getSettings([CONF_ARTIKELUEBERSICHT]));
}

/**
 * @param array $exclude
 * @param array $conf
 * @return string
 * @deprecated since 5.0.0
 */
function gibMaxPrioSpalte($exclude, $conf = null)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return \Filter\States\BaseSearchQuery::getPrioritizedRows($exclude, $conf);
}

/**
 * @param array $searchColumns
 * @return array
 * @deprecated since 4.06
 */
function gibSuchspaltenKlassen($searchColumns)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return Shop::getProductFilter()->Suchanfrage->getSearchColumnClasses($searchColumns);
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
    return Shop::getProductFilter()->Suchanfrage->checkColumnClasses($searchColumns, $searchColumn, $nonAllowed);
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
    return Shop::getProductFilter()->Suche->saveQuery($nAnzahlTreffer, $cSuche, $bEchteSuche, $kSpracheExt, $bSpamFilter);
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
    $kSprache = ((int)$kSpracheExt > 0) ? (int)$kSpracheExt : Sprache::getDefaultLanguage(true)->kSprache;
    if (strlen($Suchausdruck) > 0) {
        $SuchausdruckmappingTMP = Shop::Container()->getDB()->select(
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
            $SuchausdruckmappingTMP = Shop::Container()->getDB()->select(
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
    return Shop::getProductFilter()->Suchanfrage->prepareSearchQuery($query);
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
