<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTL\DB\ReturnType;
use JTL\Link\Link;
use JTL\Link\LinkInterface;
use JTL\Shop;

/**
 * @param \JTL\Link\LinkGroupInterface $linkGroup
 * @param int                          $parentID
 * @return \Illuminate\Support\Collection
 */
function build_navigation_subs_admin($linkGroup, int $parentID = 0)
{
    $news = new \Illuminate\Support\Collection();
    $lh   = Shop::Container()->getLinkService();
    foreach ($linkGroup->getLinks() as $link) {
        $link->setLevel(count($lh->getParentIDs($link->getID())));
        /** @var \JTL\Link\Link $link */
        if ($link->getParent() !== $parentID) {
            continue;
        }
        $link->setChildLinks(build_navigation_subs_admin($linkGroup, $link->getID()));
        $news->push($link);
    }

    return $news;
}

/**
 * @param int $linkID
 * @return int|string
 */
function gibLetzteBildNummer($linkID)
{
    $uploadDir = PFAD_ROOT . PFAD_BILDER . PFAD_LINKBILDER;
    $images    = [];
    if (is_dir($uploadDir . $linkID)) {
        $handle = opendir($uploadDir . $linkID);
        while (($file = readdir($handle)) !== false) {
            if ($file !== '.' && $file !== '..') {
                $images[] = $file;
            }
        }
    }
    $max = 0;
    foreach ($images as $image) {
        $num = mb_substr($image, 4, (mb_strlen($image) - mb_strpos($image, '.')) - 3);
        if ($num > $max) {
            $max = $num;
        }
    }

    return $max;
}

/**
 * @param string $text
 * @param int    $linkID
 * @return mixed
 */
function parseText($text, int $linkID)
{
    $uploadDir = PFAD_ROOT . PFAD_BILDER . PFAD_LINKBILDER;
    $images    = [];
    $sort      = [];
    if (is_dir($uploadDir . $linkID)) {
        $handle = opendir($uploadDir . $linkID);
        while (($file = readdir($handle)) !== false) {
            if ($file !== '.' && $file !== '..') {
                $imageID          = (int)mb_substr(
                    str_replace('Bild', '', $file),
                    0,
                    mb_strpos(str_replace('Bild', '', $file), '.')
                );
                $images[$imageID] = $file;
                $sort[]           = $imageID;
            }
        }
    }
    usort($sort, 'cmp');
    $basePath = Shop::getURL() . '/' . PFAD_BILDER . PFAD_LINKBILDER;
    foreach ($sort as $sortID) {
        $text = str_replace(
            '$#Bild' . $sortID . '#$',
            '<img src="' . $basePath . $linkID . '/' . $images[$sortID] . '" />',
            $text
        );
    }

    return $text;
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
 * @param string $file
 * @param int    $nMaxBreite
 * @param int    $nMaxHoehe
 * @return array
 */
function calcRatio($file, $nMaxBreite = 0, $nMaxHoehe = 0)
{
    [$width, $height] = getimagesize($file);

    return [$width, $height];
}

/**
 * @param int $kLink
 * @param int $linkGroupID
 * @return int
 */
function removeLink($kLink, $linkGroupID = 0)
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
 * @param int    $linkID
 * @param string $var
 * @return array
 */
function getLinkVar(int $linkID, $var)
{
    $namen = [];
    if (!$linkID) {
        return $namen;
    }
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
                WHERE tlinksprache.kLink = " . $linkID,
            ReturnType::ARRAY_OF_OBJECTS
        );
    } else {
        $links = Shop::Container()->getDB()->selectAll('tlinksprache', 'kLink', $linkID);
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
 * @param int $linkGroupID
 * @return array
 */
function getLinkgruppeNames(int $linkGroupID)
{
    $namen = [];
    if (!$linkGroupID) {
        return $namen;
    }
    $links = Shop::Container()->getDB()->selectAll('tlinkgruppesprache', 'kLinkgruppe', $linkGroupID);
    foreach ($links as $link) {
        $namen[$link->cISOSprache] = $link->cName;
    }

    return $namen;
}

/**
 * @param int $linkGroupID
 * @return mixed
 */
function holeLinkgruppe(int $linkGroupID)
{
    return Shop::Container()->getDB()->select('tlinkgruppe', 'kLinkgruppe', $linkGroupID);
}

/**
 * @return array
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

/**
 * @param int $linkType
 * @param int $linkID
 * @param array $customerGroups
 * @return bool
 */
function isDuplicateSpecialLink(int $linkType, int $linkID, array $customerGroups): bool
{
    $link = new Link(Shop::Container()->getDB());
    $link->setCustomerGroups($customerGroups);
    $link->setLinkType($linkType);
    $link->setID($linkID);

    return $link->hasDuplicateSpecialLink();
}
