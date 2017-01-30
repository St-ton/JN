<?php

/**
 * @param array $oLink_arr
 * @param int   $kVaterLink
 * @param int   $nLevel
 * @return array
 */
function build_navigation_subs_admin($oLink_arr, $kVaterLink = 0, $nLevel = 0)
{
    $oNew_arr = [];
    foreach ($oLink_arr as &$oLink) {
        if ($oLink->kVaterLink == $kVaterLink) {
            $oLink->nLevel   = $nLevel;
            $oLink->oSub_arr = build_navigation_subs_admin($oLink_arr, $oLink->kLink, $nLevel + 1);
            $oNew_arr[]      = $oLink;
        }
    }

    return $oNew_arr;
}

/**
 * @param int $kLink
 * @return int|string
 */
function gibLetzteBildNummer($kLink)
{
    $cUploadVerzeichnis = PFAD_ROOT . PFAD_BILDER . PFAD_LINKBILDER;
    $cBild_arr          = [];
    if (is_dir($cUploadVerzeichnis . $kLink)) {
        $DirHandle = opendir($cUploadVerzeichnis . $kLink);
        while (false !== ($Datei = readdir($DirHandle))) {
            if ($Datei !== '.' && $Datei !== '..') {
                $cBild_arr[] = $Datei;
            }
        }
    }
    $nMax       = 0;
    $imageCount = count($cBild_arr);
    if ($imageCount > 0) {
        for ($i = 0; $i < $imageCount; $i++) {
            $cNummer = substr($cBild_arr[$i], 4, (strlen($cBild_arr[$i]) - strpos($cBild_arr[$i], '.')) - 3);
            if ($cNummer > $nMax) {
                $nMax = $cNummer;
            }
        }
    }

    return $nMax;
}

/**
 * @param string $cText
 * @param int    $kLink
 * @return mixed
 */
function parseText($cText, $kLink)
{
    $cUploadVerzeichnis = PFAD_ROOT . PFAD_BILDER . PFAD_LINKBILDER;
    $cBild_arr          = [];
    $nSort_arr          = [];
    if (is_dir($cUploadVerzeichnis . $kLink)) {
        $DirHandle = opendir($cUploadVerzeichnis . $kLink);
        while (false !== ($Datei = readdir($DirHandle))) {
            if ($Datei !== '.' && $Datei !== '..') {
                $nBild             = intval(substr(
                    str_replace('Bild', '', $Datei),
                    0,
                    strpos(str_replace('Bild', '', $Datei), '.'))
                );
                $cBild_arr[$nBild] = $Datei;
                $nSort_arr[]       = $nBild;
            }
        }
    }
    usort($nSort_arr, 'cmp');

    foreach ($nSort_arr as $nSort) {
        $cText = str_replace('$#Bild' . $nSort . '#$', '<img src="' .
            Shop::getURL() . '/' . PFAD_BILDER . PFAD_LINKBILDER . $kLink . '/' . $cBild_arr[$nSort] .
            '" />', $cText);
    }

    return $cText;
}

/**
 * @param int $a
 * @param int $b
 * @return int
 */
function cmp($a, $b)
{
    if ($a == $b) {
        return 0;
    }

    return ($a < $b) ? -1 : 1;
}

/**
 * @param object $a
 * @param object $b
 * @return int
 */
function cmp_obj($a, $b)
{
    if ($a->nBild == $b->nBild) {
        return 0;
    }

    return ($a->nBild < $b->nBild) ? -1 : 1;
}

/**
 * Gibt eine neue Breite und Hoehe als Array zurueck
 *
 * @param string $cDatei
 * @param int    $nMaxBreite
 * @param int    $nMaxHoehe
 * @return array
 */
function calcRatio($cDatei, $nMaxBreite, $nMaxHoehe)
{
    list($ImageBreite, $ImageHoehe) = getimagesize($cDatei);

    return [$ImageBreite, $ImageHoehe];
}

/**
 * @param int $kLink
 * @param int $kLinkgruppe
 */
function removeLink($kLink, $kLinkgruppe)
{
    $oLink              = new Link($kLink, null, true);
    $oLink->kLinkgruppe = $kLinkgruppe;
    $oLink->delete(true, $kLinkgruppe);
    // Bilderverzeichnis loeschen
    if (isset($cUploadVerzeichnis) && is_dir($cUploadVerzeichnis . $kLink)) {
        $DirHandle = opendir($cUploadVerzeichnis . $kLink);
        while (false !== ($Datei = readdir($DirHandle))) {
            if ($Datei !== '.' && $Datei !== '..') {
                unlink($cUploadVerzeichnis . $kLink . '/' . $Datei);
            }
        }

        rmdir($cUploadVerzeichnis . $kLink);
    }
}

/**
 * @param int    $kLink
 * @param string $var
 * @return array
 */
function getLinkVar($kLink, $var)
{
    $namen = [];

    if (!$kLink) {
        return $namen;
    }
    $kLink = (int)$kLink;
    // tseo work around
    if ($var === 'cSeo') {
        $linknamen = Shop::DB()->query(
            "SELECT tlinksprache.cISOSprache, tseo.cSeo
                FROM tlinksprache
                JOIN tsprache 
                    ON tsprache.cISO = tlinksprache.cISOSprache
                LEFT JOIN tseo 
                    ON tseo.cKey = 'kLink'
                    AND tseo.kKey = tlinksprache.kLink
                    AND tseo.kSprache = tsprache.kSprache
                WHERE tlinksprache.kLink = " . $kLink, 2
        );
    } else {
        $linknamen = Shop::DB()->selectAll('tlinksprache', 'kLink', $kLink);
    }
    $linkCount = count($linknamen);
    for ($i = 0; $i < $linkCount; $i++) {
        $namen[$linknamen[$i]->cISOSprache] = $linknamen[$i]->$var;
    }

    return $namen;
}

/**
 * @param object $link
 * @return array
 */
function getGesetzteKundengruppen($link)
{
    $ret = [];
    if (!isset($link->cKundengruppen) || !$link->cKundengruppen || $link->cKundengruppen == 'NULL') {
        $ret[0] = true;

        return $ret;
    }
    $kdgrp = explode(';', $link->cKundengruppen);
    foreach ($kdgrp as $kKundengruppe) {
        if (intval($kKundengruppe) > 0) {
            $ret[$kKundengruppe] = true;
        }
    }

    return $ret;
}

/**
 * @param int $kLinkgruppe
 * @return array
 */
function getLinkgruppeNames($kLinkgruppe)
{
    $namen = [];
    if (!$kLinkgruppe) {
        return $namen;
    }
    $linknamen = Shop::DB()->selectAll('tlinkgruppesprache', 'kLinkgruppe', (int)$kLinkgruppe);
    $linkCount = count($linknamen);
    for ($i = 0; $i < $linkCount; $i++) {
        $namen[$linknamen[$i]->cISOSprache] = $linknamen[$i]->cName;
    }

    return $namen;
}

/**
 * @param int $kLinkgruppe
 * @return mixed
 */
function holeLinkgruppe($kLinkgruppe)
{
    return Shop::DB()->select('tlinkgruppe', 'kLinkgruppe', (int)$kLinkgruppe);
}

/**
 * @return mixed
 */
function holeSpezialseiten()
{
    return Shop::DB()->query(
        "SELECT *
            FROM tspezialseite
            ORDER BY nSort", 2
    );
}

/**
 * @param array $oSub_arr
 * @param int   $kLinkgruppe
 */
function aenderLinkgruppeRek($oSub_arr, $kLinkgruppe, $kLinkgruppeAlt)
{
    if (is_array($oSub_arr) && count($oSub_arr) > 0) {
        foreach ($oSub_arr as $oSub) {
            /*$oSub->setLinkgruppe($kLinkgruppe);
            $oSub->update();
            aenderLinkgruppeRek($oSub->oSub_arr, $kLinkgruppe);*/
            Shop::dbg($oSub, false, 'oSub');
            $oSub = new Link(null, $oSub, true, $kLinkgruppeAlt);
            Shop::dbg($oSub, false, 'oSub mit sub');
            //$oSub->getSub((int)$oSub->kLink, (int)($_POST['kLinkgruppeAlt']));
            $exists = Shop::DB()->select('tlink', ['kLink', 'kLinkgruppe'],[(int)$oSub->kLink,  (int)$kLinkgruppe]);
            Shop::dbg($exists, false, 'exists');
            if (empty($exists)) {
                Shop::dbg($oSub, false, 'oSub1');
                $oSub->setLinkgruppe($kLinkgruppe)
                    ->save();
                aenderLinkgruppeRek($oSub->oSub_arr, $kLinkgruppe, $kLinkgruppeAlt);
                $oSub->setLinkgruppe($kLinkgruppeAlt)
                    ->delete(false, $kLinkgruppeAlt);
            } else {
                //$oSub = new Link((int)$oSub->kLink, null, true, (int)($kLinkgruppeAlt));
                Shop::dbg($oSub, false, 'oSub2');
                Shop::dbg($kLinkgruppeAlt, false, 'linkgruppealt');
                $oSub->setVaterLink(0)
                     ->update();
            }
        }
    }
}
