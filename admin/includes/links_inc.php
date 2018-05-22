<?php

/**
 * @param \Link\LinkGroupInterface $linkGroup
 * @param int                      $kVaterLink
 * @return \Tightenco\Collect\Support\Collection
 */
function build_navigation_subs_admin($linkGroup, $kVaterLink = 0)
{
    $kVaterLink = (int)$kVaterLink;
    $oNew_arr   = new \Tightenco\Collect\Support\Collection();
    $lh         = Shop::Container()->getLinkService();
    $i          = 0;
    foreach ($linkGroup->getLinks() as $link) {
        $link->setLevel(count($lh->getParentIDs($link->getID())));
        /** @var \Link\Link $link */
        if ($link->getParent() !== $kVaterLink) {
            continue;
        }
        $link->setChildLinks(build_navigation_subs_admin($linkGroup, $link->getID()));
        $oNew_arr->push($link);
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
                $nBild             = (int)substr(
                    str_replace('Bild', '', $Datei),
                    0,
                    strpos(str_replace('Bild', '', $Datei), '.')
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
 * @return int
 */
function removeLink($kLink, $kLinkgruppe = 0)
{
    return Shop::Container()->getDB()->executeQueryPrepared(
        "DELETE tlink, tlinksprache, tseo, tlinkgroupassociations
            FROM tlink
            JOIN tlinkgroupassociations
                ON tlinkgroupassociations.linkID = tlink.kLink
            LEFT JOIN tlinksprache
                ON tlink.kLink = tlinksprache.kLink
            LEFT JOIN tseo
                ON tseo.cKey = 'kLink'
                AND tseo.kKey = :lid
            WHERE tlink.kLink = :lid",
        ['lid' => $kLink],
        \DB\ReturnType::AFFECTED_ROWS
    );
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
        $linknamen = Shop::Container()->getDB()->query(
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
        $linknamen = Shop::Container()->getDB()->selectAll('tlinksprache', 'kLink', $kLink);
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
        if ((int)$kKundengruppe > 0) {
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
    $linknamen = Shop::Container()->getDB()->selectAll('tlinkgruppesprache', 'kLinkgruppe', (int)$kLinkgruppe);
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
    return Shop::Container()->getDB()->select('tlinkgruppe', 'kLinkgruppe', (int)$kLinkgruppe);
}

/**
 * @return mixed
 */
function holeSpezialseiten()
{
    return Shop::Container()->getDB()->query(
        "SELECT *
            FROM tspezialseite
            ORDER BY nSort", 2
    );
}
