<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use Helpers\RequestHelper;

/**
 * This pagination implementation is deprecated. Use the Pagination admin class instead!
 */

/**
 * @param int $currentPage
 * @param int $count
 * @param int $perPage
 * @return stdClass
 * @deprecated since 4.05
 */
function baueBlaetterNavi(int $currentPage, int $count, int $perPage)
{
    $nav         = new stdClass();
    $nav->nAktiv = 0;

    if ($count > $perPage) {
        $nBlaetterAnzahl_arr = [];

        $nSeiten     = ceil($count / $perPage);
        $nMaxAnzeige = 5;
        $nAnfang     = 0;
        $nEnde       = 0;
        $prev        = $currentPage - 1; // Zum zurück blättern in der Navigation
        if ($prev <= 0) {
            $prev = 1;
        }
        $next = $currentPage + 1; // Zum vorwärts blättern in der Navigation
        if ($next >= $nSeiten) {
            $next = $nSeiten;
        }

        if ($nSeiten > $nMaxAnzeige) {
            // Ist die aktuelle Seite nach dem abzug der Begrenzung größer oder gleich 1?
            if (($currentPage - $nMaxAnzeige) >= 1) {
                $nAnfang = 1;
                $nVon    = ($currentPage - $nMaxAnzeige) + 1;
            } else {
                $nAnfang = 0;
                $nVon    = 1;
            }
            // Ist die aktuelle Seite nach dem addieren der Begrenzung kleiner als die maximale Anzahl der Seiten
            if (($currentPage + $nMaxAnzeige) < $nSeiten) {
                $nEnde = $nSeiten;
                $nBis  = ($currentPage + $nMaxAnzeige) - 1;
            } else {
                $nEnde = 0;
                $nBis  = $nSeiten;
            }
            // Baue die Seiten für die Navigation
            for ($i = $nVon; $i <= $nBis; $i++) {
                $nBlaetterAnzahl_arr[] = $i;
            }
        } else {
            // Baue die Seiten für die Navigation
            for ($i = 1; $i <= $nSeiten; $i++) {
                $nBlaetterAnzahl_arr[] = $i;
            }
        }

        // Blaetter Objekt um später in Smarty damit zu arbeiten
        $nav->nSeiten             = $nSeiten;
        $nav->nVoherige           = $prev;
        $nav->nNaechste           = $next;
        $nav->nAnfang             = $nAnfang;
        $nav->nEnde               = $nEnde;
        $nav->nBlaetterAnzahl_arr = $nBlaetterAnzahl_arr;
        $nav->nAktiv              = 1;
        $nav->nAnzahl             = $count;
    }

    $nav->nAktuelleSeite = $currentPage;
    $nav->nVon           = (($nav->nAktuelleSeite - 1) * $perPage) + 1;
    $nav->nBis           = $nav->nAktuelleSeite * $perPage;
    if ($nav->nBis > $count) {
        $nav->nBis = $count;
    }

    return $nav;
}

/**
 * @param int $count
 * @param int $perPage
 * @return bool|stdClass
 * @deprecated since 4.05
 */
function baueBlaetterNaviGetterSetter(int $count, int $perPage)
{
    $conf = new stdClass();
    if ($count <= 0 || $perPage <= 0) {
        return false;
    }
    for ($i = 1; $i <= $count; $i++) {
        $cOffset        = 'nOffset' . $i;
        $cSQL           = 'cSQL' . $i;
        $nAktuelleSeite = 'nAktuelleSeite' . $i;
        $cLimit         = 'cLimit' . $i;

        $conf->$cOffset        = 0;
        $conf->$cSQL           = ' LIMIT ' . $perPage;
        $conf->$nAktuelleSeite = 1;
        $conf->$cLimit         = 0;
        // GET || POST
        if (RequestHelper::verifyGPCDataInt('s' . $i) > 0) {
            $page                  = RequestHelper::verifyGPCDataInt('s' . $i);
            $conf->$cOffset        = (($page - 1) * $perPage);
            $conf->$cSQL           = ' LIMIT ' . (($page - 1) * $perPage) . ', ' . $perPage;
            $conf->$nAktuelleSeite = $page;
            $conf->$cLimit         = (($page - 1) * $perPage);
        }
    }

    return $conf;
}
