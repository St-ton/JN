<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use Helpers\URL;

/**
 * @param int $kTag
 * @param int $kSprache
 * @return int
 */
function holeTagDetailAnzahl($kTag, $kSprache)
{
    if ((int)$kTag > 0 && (int)$kSprache > 0) {
        return (int)Shop::Container()->getDB()->query(
            'SELECT COUNT(*) AS nAnzahl
                FROM ttagartikel
                JOIN ttag 
                    ON ttag.kTag = ttagartikel.kTag
                    AND ttag.kSprache = ' . (int)$kSprache . '
                WHERE ttagartikel.kTag = ' . (int)$kTag,
            \DB\ReturnType::SINGLE_OBJECT
        )->nAnzahl;
    }

    return 0;
}

/**
 * @param int    $kTag
 * @param int    $kSprache
 * @param string $cLimit
 * @return bool
 */
function holeTagDetail(int $kTag, int $kSprache, $cLimit)
{
    if (!$kSprache) {
        $kSprache = $_SESSION['kSprache'];
    }
    if ($kTag > 0 && $kSprache > 0) {
        $oTagArtikel_arr = Shop::Container()->getDB()->queryPrepared(
            "SELECT ttagartikel.kTag, ttag.cName, tartikel.cName AS acName, 
                tartikel.kArtikel AS kArtikel, tseo.cSeo
                FROM ttagartikel
                JOIN ttag 
                    ON ttag.kTag = ttagartikel.kTag
                    AND ttag.kSprache = :lid
                JOIN tartikel 
                        ON tartikel.kArtikel = ttagartikel.kArtikel
                LEFT JOIN tseo 
                    ON tseo.cKey = 'kArtikel'
                    AND tseo.kKey = tartikel.kArtikel
                    AND tseo.kSprache = :lid
                WHERE ttagartikel.kTag = :tid
                    AND ttag.kSprache = :lid
                GROUP BY tartikel.kArtikel
                ORDER BY tartikel.cName" . $cLimit,
            ['lid' => $kSprache, 'tid' => $kTag],
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
        foreach ($oTagArtikel_arr as $i => $oTagArtikel) {
            $oTagArtikel_arr[$i]->cURL = URL::buildURL($oTagArtikel, URLART_ARTIKEL, true);
        }

        return $oTagArtikel_arr;
    }

    return false;
}

/**
 * @param array $kArtikel_arr
 * @param int   $kTag
 * @return bool
 */
function loescheTagsVomArtikel($kArtikel_arr, int $kTag)
{
    if ($kTag > 0 && is_array($kArtikel_arr) && count($kArtikel_arr) > 0) {
        foreach ($kArtikel_arr as $kArtikel) {
            $kArtikel = (int)$kArtikel;
            Shop::Container()->getDB()->delete('ttagartikel', ['kArtikel', 'kTag'], [$kArtikel, $kTag]);
            $oTagArtikel_arr = Shop::Container()->getDB()->selectAll('ttagartikel', 'kTag', $kTag);
            // Es gibt keine Artikel mehr zu dem Tag => Tag aus ttag / tseo löschen
            if (count($oTagArtikel_arr) === 0) {
                Shop::Container()->getDB()->query(
                    "DELETE ttag, tseo
                        FROM ttag
                        LEFT JOIN tseo 
                            ON tseo.cKey = 'kTag'
                            AND tseo.kKey = ttag.kTag
                        WHERE ttag.kTag = " . $kTag,
                    \DB\ReturnType::DEFAULT
                );
            }
            Shop::Container()->getCache()->flushTags(['CACHING_GROUP_ARTICLE_' . $kArtikel]);
        }

        return true;
    }

    return false;
}

/**
 * @param array $tagIDs
 * @return int
 */
function flushAffectedArticleCache(array $tagIDs)
{
    // get tagged article IDs to invalidate their cache
    $_affectedArticles = Shop::Container()->getDB()->query(
        'SELECT DISTINCT kArtikel
            FROM ttagartikel
            WHERE kTag IN (' . implode(', ', $tagIDs) . ')',
        \DB\ReturnType::ARRAY_OF_OBJECTS
    );
    if (count($_affectedArticles) > 0) {
        $articleCacheIDs = [];
        foreach ($_affectedArticles as $_article) {
            $articleCacheIDs[] = CACHING_GROUP_ARTICLE . '_' . $_article->kArtikel;
        }

        return Shop::Container()->getCache()->flushTags($articleCacheIDs);
    }

    return 0;
}
