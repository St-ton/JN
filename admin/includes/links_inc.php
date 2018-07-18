<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

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
    $nMax = 0;
    foreach ($cBild_arr as $image) {
        $cNummer = substr($image, 4, (strlen($image) - strpos($image, '.')) - 3);
        if ($cNummer > $nMax) {
            $nMax = $cNummer;
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
            LEFT JOIN tlinkgroupassociations
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
        $links = Shop::Container()->getDB()->query(
            "SELECT tlinksprache.cISOSprache, tseo.cSeo
                FROM tlinksprache
                JOIN tsprache 
                    ON tsprache.cISO = tlinksprache.cISOSprache
                LEFT JOIN tseo 
                    ON tseo.cKey = 'kLink'
                    AND tseo.kKey = tlinksprache.kLink
                    AND tseo.kSprache = tsprache.kSprache
                WHERE tlinksprache.kLink = " . $kLink,
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
    } else {
        $links = Shop::Container()->getDB()->selectAll('tlinksprache', 'kLink', $kLink);
    }
    foreach ($links as $link) {
        $namen[$link->cISOSprache] = $link->$var;
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
    if ($link instanceof \Link\LinkInterface) {
        $cGroups = $link->getCustomerGroups();
        if (count($cGroups) === 0) {
            $ret[0] = true;
        }
        foreach ($cGroups as $customerGroup) {
            $ret[$customerGroup] = true;
        }

        return $ret;
    }
    if (!isset($link->cKundengruppen) || !$link->cKundengruppen || strtolower($link->cKundengruppen) === 'null') {
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
    $links = Shop::Container()->getDB()->selectAll('tlinkgruppesprache', 'kLinkgruppe', (int)$kLinkgruppe);
    foreach ($links as $link) {
        $namen[$link->cISOSprache] = $link->cName;
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
            ORDER BY nSort",
        \DB\ReturnType::ARRAY_OF_OBJECTS
    );
}

/**
 * @param int $nSpecialSite
 * @param int $kLink
 * @return false|object
 */
function checkSpecialSite ($nSpecialSite, $kLink)
{
    return Shop::Container()->getDB()->query(
        'SELECT kLink, cName
                            FROM tlink
                            WHERE nLinkart = ' . $nSpecialSite . '
                                AND kLink != ' . (!empty($kLink) ? $kLink : 0),
        \DB\ReturnType::SINGLE_OBJECT
    );
}
