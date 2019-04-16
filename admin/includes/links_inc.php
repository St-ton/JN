<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use Illuminate\Support\Collection;
use JTL\DB\ReturnType;
use JTL\Link\LinkGroupInterface;
use JTL\Link\LinkInterface;
use JTL\Shop;

/**
 * @param LinkGroupInterface $linkGroup
 * @param int                $parentLinkID
 * @return Collection
 */
function build_navigation_subs_admin($linkGroup, $parentLinkID = 0)
{
    $parentLinkID = (int)$parentLinkID;
    $collection   = new Collection();
    $service      = Shop::Container()->getLinkService();
    foreach ($linkGroup->getLinks() as $link) {
        $link->setLevel(count($service->getParentIDs($link->getID())));
        /** @var \JTL\Link\Link $link */
        if ($link->getParent() !== $parentLinkID) {
            continue;
        }
        $link->setChildLinks(build_navigation_subs_admin($linkGroup, $link->getID()));
        $collection->push($link);
    }

    return $collection;
}

/**
 * @param int $kLink
 * @return int|string
 */
function gibLetzteBildNummer($kLink)
{
    $uploadDir = PFAD_ROOT . PFAD_BILDER . PFAD_LINKBILDER;
    $images    = [];
    if (is_dir($uploadDir . $kLink)) {
        $handle = opendir($uploadDir . $kLink);
        while (($file = readdir($handle)) !== false) {
            if ($file !== '.' && $file !== '..') {
                $images[] = $file;
            }
        }
    }
    $nMax = 0;
    foreach ($images as $image) {
        $cNummer = mb_substr($image, 4, (mb_strlen($image) - mb_strpos($image, '.')) - 3);
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
        while (($Datei = readdir($DirHandle)) !== false) {
            if ($Datei !== '.' && $Datei !== '..') {
                $nBild             = (int)mb_substr(
                    str_replace('Bild', '', $Datei),
                    0,
                    mb_strpos(str_replace('Bild', '', $Datei), '.')
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
        ReturnType::AFFECTED_ROWS
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
            ReturnType::ARRAY_OF_OBJECTS
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
    if ($link instanceof LinkInterface) {
        $cGroups = $link->getCustomerGroups();
        if (count($cGroups) === 0) {
            $ret[0] = true;
        }
        foreach ($cGroups as $customerGroup) {
            $ret[$customerGroup] = true;
        }

        return $ret;
    }
    if (!isset($link->cKundengruppen)
        || !$link->cKundengruppen
        || mb_convert_case($link->cKundengruppen, MB_CASE_LOWER) === 'null'
    ) {
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
        'SELECT *
            FROM tspezialseite
            ORDER BY nSort',
        ReturnType::ARRAY_OF_OBJECTS
    );
}
