<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use Illuminate\Support\Collection;
use JTL\DB\ReturnType;
use JTL\Link\Link;
use JTL\Link\LinkGroupInterface;
use JTL\Link\LinkInterface;
use JTL\Shop;

/**
 * @param LinkGroupInterface $linkGroup
 * @param int                $parentID
 * @return Collection
 */
function build_navigation_subs_admin($linkGroup, int $parentID = 0)
{
    $news = new Collection();
    $lh   = Shop::Container()->getLinkService();
    foreach ($linkGroup->getLinks() as $link) {
        $link->setLevel(count($lh->getParentIDs($link->getID())));
        /** @var LinkInterface $link */
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
    $baseURL   = Shop::getURL() . '/' . PFAD_BILDER . PFAD_LINKBILDER;
    $images    = [];
    $sort      = [];
    if (is_dir($uploadDir . $linkID)) {
        $dirHandle = opendir($uploadDir . $linkID);
        while (($file = readdir($dirHandle)) !== false) {
            if ($file !== '.' && $file !== '..') {
                $imageNumber          = (int)mb_substr(
                    str_replace('Bild', '', $file),
                    0,
                    mb_strpos(str_replace('Bild', '', $file), '.')
                );
                $images[$imageNumber] = $file;
                $sort[]               = $imageNumber;
            }
        }
    }
    usort($sort, 'cmp');

    foreach ($sort as $no) {
        $text = str_replace(
            '$#Bild' . $no . '#$',
            '<img src="' . $baseURL . $linkID . '/' . $images[$no] . '" />',
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
