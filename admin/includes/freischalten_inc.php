<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTL\Customer\Kunde;
use JTL\DB\ReturnType;
use JTL\Helpers\Seo;
use JTL\Rating\RatingAdminController;
use JTL\Shop;

/**
 * @param string $cSQL
 * @param object $cSuchSQL
 * @param bool   $checkLanguage
 * @return array
 */
function gibBewertungFreischalten($cSQL, $cSuchSQL, bool $checkLanguage = true): array
{
    $cond = $checkLanguage === true
        ? 'tbewertung.kSprache = ' . (int)$_SESSION['kSprache'] . ' AND '
        : '';

    return Shop::Container()->getDB()->query(
        "SELECT tbewertung.*, DATE_FORMAT(tbewertung.dDatum, '%d.%m.%Y') AS Datum, tartikel.cName AS ArtikelName
            FROM tbewertung
            LEFT JOIN tartikel 
                ON tbewertung.kArtikel = tartikel.kArtikel
            WHERE " . $cond . 'tbewertung.nAktiv = 0
                ' . $cSuchSQL->cWhere . '
            ORDER BY tbewertung.kArtikel, tbewertung.dDatum DESC' . $cSQL,
        ReturnType::ARRAY_OF_OBJECTS
    );
}

/**
 * @param string $cSQL
 * @param object $cSuchSQL
 * @param bool   $checkLanguage
 * @return array
 */
function gibSuchanfrageFreischalten($cSQL, $cSuchSQL, bool $checkLanguage = true): array
{
    $cond = $checkLanguage === true
        ? 'AND kSprache = ' . (int)$_SESSION['kSprache'] . ' '
        : '';

    return Shop::Container()->getDB()->query(
        "SELECT *, DATE_FORMAT(dZuletztGesucht, '%d.%m.%Y %H:%i') AS dZuletztGesucht_de
            FROM tsuchanfrage
            WHERE nAktiv = 0 " . $cond . $cSuchSQL->cWhere . '
            ORDER BY ' . $cSuchSQL->cOrder . $cSQL,
        ReturnType::ARRAY_OF_OBJECTS
    );
}

/**
 * @param string $cSQL
 * @param object $cSuchSQL
 * @param bool   $checkLanguage
 * @return array
 */
function gibTagFreischalten($cSQL, $cSuchSQL, bool $checkLanguage = true): array
{
    $cond = $checkLanguage === true
        ? 'AND ttag.kSprache = ' . (int)$_SESSION['kSprache'] . ' '
        : '';

    return Shop::Container()->getDB()->query(
        'SELECT ttag.*, sum(ttagartikel.nAnzahlTagging) AS Anzahl, ttagartikel.kArtikel, 
            tartikel.cName AS cArtikelName, tartikel.cSeo AS cArtikelSeo
            FROM ttag
            LEFT JOIN ttagartikel 
                ON ttagartikel.kTag = ttag.kTag
            LEFT JOIN tartikel 
                ON tartikel.kArtikel = ttagartikel.kArtikel
            WHERE ttag.nAktiv = 0 ' . $cond . $cSuchSQL->cWhere . '
            GROUP BY ttag.kTag
            ORDER BY Anzahl DESC' . $cSQL,
        ReturnType::ARRAY_OF_OBJECTS
    );
}

/**
 * @param string $cSQL
 * @param object $cSuchSQL
 * @param bool   $checkLanguage
 * @return array
 */
function gibNewskommentarFreischalten($cSQL, $cSuchSQL, bool $checkLanguage = true): array
{
    $cond         = $checkLanguage === true
        ? ' AND t.languageID = ' . (int)$_SESSION['kSprache'] . ' '
        : '';
    $newsComments = Shop::Container()->getDB()->query(
        "SELECT tnewskommentar.*, DATE_FORMAT(tnewskommentar.dErstellt, '%d.%m.%Y  %H:%i') AS dErstellt_de, 
            tkunde.kKunde, tkunde.cVorname, tkunde.cNachname, t.title AS cBetreff
            FROM tnewskommentar
            JOIN tnews 
                ON tnews.kNews = tnewskommentar.kNews
            JOIN tnewssprache t 
                ON tnews.kNews = t.kNews
            LEFT JOIN tkunde 
                ON tkunde.kKunde = tnewskommentar.kKunde
            WHERE tnewskommentar.nAktiv = 0" .
            $cSuchSQL->cWhere . $cond . $cSQL,
        ReturnType::ARRAY_OF_OBJECTS
    );
    foreach ($newsComments as $comment) {
        $oKunde = new Kunde($comment->kKunde ?? 0);

        $comment->cNachname = $oKunde->cNachname;
    }

    return $newsComments;
}

/**
 * @param string $cSQL
 * @param object $cSuchSQL
 * @param bool   $checkLanguage
 * @return array
 */
function gibNewsletterEmpfaengerFreischalten($cSQL, $cSuchSQL, bool $checkLanguage = true): array
{
    $cond = $checkLanguage === true
        ? ' AND kSprache = ' . (int)$_SESSION['kSprache']
        : '';

    return Shop::Container()->getDB()->query(
        "SELECT *, DATE_FORMAT(dEingetragen, '%d.%m.%Y  %H:%i') AS dEingetragen_de, 
            DATE_FORMAT(dLetzterNewsletter, '%d.%m.%Y  %H:%i') AS dLetzterNewsletter_de
            FROM tnewsletterempfaenger
            WHERE nAktiv = 0
                " . $cSuchSQL->cWhere . $cond .
        ' ORDER BY ' . $cSuchSQL->cOrder . $cSQL,
        ReturnType::ARRAY_OF_OBJECTS
    );
}

/**
 * @param array $ratingIDs
 * @return bool
 */
function schalteBewertungFrei($ratingIDs): bool
{
    if (!is_array($ratingIDs) || count($ratingIDs) === 0) {
        return false;
    }
    $controller = new RatingAdminController(Shop::Container()->getDB(), Shop::Container()->getCache());
    $controller->activate($ratingIDs);

    return true;
}

/**
 * @param array $searchQueries
 * @return bool
 */
function schalteSuchanfragenFrei($searchQueries): bool
{
    if (!is_array($searchQueries) || count($searchQueries) === 0) {
        return false;
    }
    $db = Shop::Container()->getDB();
    foreach ($searchQueries as $i => $kSuchanfrage) {
        $kSuchanfrage = (int)$kSuchanfrage;
        $oSuchanfrage = $db->query(
            'SELECT kSuchanfrage, kSprache, cSuche
                FROM tsuchanfrage
                WHERE kSuchanfrage = ' . $kSuchanfrage,
            ReturnType::SINGLE_OBJECT
        );

        if ($oSuchanfrage->kSuchanfrage > 0) {
            $db->delete(
                'tseo',
                ['cKey', 'kKey', 'kSprache'],
                ['kSuchanfrage', $kSuchanfrage, (int)$oSuchanfrage->kSprache]
            );
            $oSeo           = new stdClass();
            $oSeo->cSeo     = Seo::checkSeo(Seo::getSeo($oSuchanfrage->cSuche));
            $oSeo->cKey     = 'kSuchanfrage';
            $oSeo->kKey     = $kSuchanfrage;
            $oSeo->kSprache = $oSuchanfrage->kSprache;
            $db->insert('tseo', $oSeo);
            $db->update(
                'tsuchanfrage',
                'kSuchanfrage',
                $kSuchanfrage,
                (object)['nAktiv' => 1, 'cSeo' => $oSeo->cSeo]
            );
        }
    }

    return true;
}

/**
 * @param array $tags
 * @return bool
 */
function schalteTagsFrei($tags): bool
{
    if (!is_array($tags) || count($tags) === 0) {
        return false;
    }
    $db        = Shop::Container()->getDB();
    $tags      = array_map('\intval', $tags);
    $cacheTags = [];
    $products  = $db->query(
        'SELECT DISTINCT kArtikel
            FROM ttagartikel
            WHERE kTag IN (' . implode(',', $tags) . ')',
        ReturnType::ARRAY_OF_OBJECTS
    );
    foreach ($products as $_article) {
        $cacheTags[] = CACHING_GROUP_ARTICLE . '_' . $_article->kArtikel;
    }
    foreach ($tags as $kTag) {
        $kTag = (int)$kTag;
        $oTag = $db->select('ttag', 'kTag', $kTag);
        if (isset($oTag->kTag) && $oTag->kTag > 0) {
            // Aktivierte Suchanfragen in tseo eintragen
            $db->delete(
                'tseo',
                ['cKey', 'kKey', 'kSprache'],
                ['kTag', $kTag, (int)$oTag->kSprache]
            );
            $oSeo           = new stdClass();
            $oSeo->cSeo     = Seo::checkSeo(Seo::getSeo($oTag->cName));
            $oSeo->cKey     = 'kTag';
            $oSeo->kKey     = $kTag;
            $oSeo->kSprache = (int)$oTag->kSprache;
            $db->insert('tseo', $oSeo);
            $db->update(
                'ttag',
                'kTag',
                $kTag,
                (object)['nAktiv' => 1, 'cSeo' => $oSeo->cSeo]
            );
        }
    }
    Shop::Container()->getCache()->flushTags($cacheTags);

    return true;
}

/**
 * @param array $newsComments
 * @return bool
 */
function schalteNewskommentareFrei($newsComments): bool
{
    if (!is_array($newsComments) || count($newsComments) === 0) {
        return false;
    }
    $newsComments = array_map('\intval', $newsComments);

    Shop::Container()->getDB()->query(
        'UPDATE tnewskommentar
            SET nAktiv = 1
            WHERE kNewsKommentar IN (' . implode(',', $newsComments) . ')',
        ReturnType::AFFECTED_ROWS
    );

    return true;
}

/**
 * @param array $recipients
 * @return bool
 */
function schalteNewsletterempfaengerFrei($recipients): bool
{
    if (!is_array($recipients) || count($recipients) === 0) {
        return false;
    }
    $recipients = array_map('\intval', $recipients);

    Shop::Container()->getDB()->query(
        'UPDATE tnewsletterempfaenger
            SET nAktiv = 1
            WHERE kNewsletterEmpfaenger IN (' . implode(',', $recipients) .')',
        ReturnType::AFFECTED_ROWS
    );

    return true;
}

/**
 * @param array $ratings
 * @return bool
 */
function loescheBewertung($ratings): bool
{
    if (!is_array($ratings) || count($ratings) === 0) {
        return false;
    }
    $ratings = array_map('\intval', $ratings);

    Shop::Container()->getDB()->query(
        'DELETE FROM tbewertung
            WHERE kBewertung IN (' . implode(',', $ratings) . ')',
        ReturnType::AFFECTED_ROWS
    );

    return true;
}

/**
 * @param array $queries
 * @return bool
 */
function loescheSuchanfragen($queries): bool
{
    if (!is_array($queries) || count($queries) === 0) {
        return false;
    }
    $queries = array_map('\intval', $queries);

    Shop::Container()->getDB()->query(
        'DELETE FROM tsuchanfrage
            WHERE kSuchanfrage IN (' . implode(',', $queries) . ')',
        ReturnType::AFFECTED_ROWS
    );
    Shop::Container()->getDB()->query(
        "DELETE FROM tseo
            WHERE cKey = 'kSuchanfrage'
                AND kKey IN (" . implode(',', $queries) . ')',
        ReturnType::AFFECTED_ROWS
    );

    return true;
}

/**
 * @param array $tags
 * @return bool
 */
function loescheTags($tags): bool
{
    if (!is_array($tags) || count($tags) === 0) {
        return false;
    }
    $tags = array_map('\intval', $tags);

    Shop::Container()->getDB()->query(
        'DELETE ttag, ttagartikel 
            FROM ttag
            LEFT JOIN ttagartikel 
                ON ttagartikel.kTag = ttag.kTag
            WHERE ttag.kTag IN (' . implode(',', $tags) . ')',
        ReturnType::AFFECTED_ROWS
    );

    return true;
}

/**
 * @param array $comments
 * @return bool
 */
function loescheNewskommentare($comments): bool
{
    if (!is_array($comments) || count($comments) === 0) {
        return false;
    }
    $comments = array_map('\intval', $comments);

    Shop::Container()->getDB()->query(
        'DELETE FROM tnewskommentar
            WHERE kNewsKommentar IN (' . implode(',', $comments) . ')',
        ReturnType::AFFECTED_ROWS
    );

    return true;
}

/**
 * @param array $recipients
 * @return bool
 */
function loescheNewsletterempfaenger($recipients): bool
{
    if (!is_array($recipients) || count($recipients) === 0) {
        return false;
    }
    $recipients = array_map('\intval', $recipients);

    Shop::Container()->getDB()->query(
        'DELETE FROM tnewsletterempfaenger
            WHERE kNewsletterEmpfaenger IN (' . implode(',', $recipients) . ')',
        ReturnType::AFFECTED_ROWS
    );

    return true;
}

/**
 * @param array  $queryIDs
 * @param string $cMapping
 * @return int
 */
function mappeLiveSuche($queryIDs, $cMapping): int
{
    if (!is_array($queryIDs) || count($queryIDs) === 0 || mb_strlen($cMapping) === 0) {
        return 2; // Leere Übergabe
    }
    $db = Shop::Container()->getDB();
    foreach ($queryIDs as $kSuchanfrage) {
        $oSuchanfrage = $db->select('tsuchanfrage', 'kSuchanfrage', (int)$kSuchanfrage);
        if ($oSuchanfrage === null || empty($oSuchanfrage->kSuchanfrage)) {
            return 3; // Mindestens eine Suchanfrage wurde nicht in der Datenbank gefunden.
        }
        if (mb_convert_case($oSuchanfrage->cSuche, MB_CASE_LOWER) === mb_convert_case($cMapping, MB_CASE_LOWER)) {
            return 6; // Es kann nicht auf sich selbst gemappt werden
        }
        $oSuchanfrageNeu = $db->select('tsuchanfrage', 'cSuche', $cMapping);
        if ($oSuchanfrageNeu === null || empty($oSuchanfrageNeu->kSuchanfrage)) {
            return 5; // Sie haben versucht auf eine nicht existierende Suchanfrage zu mappen
        }
        $mapping                 = new stdClass();
        $mapping->kSprache       = $_SESSION['kSprache'];
        $mapping->cSuche         = $oSuchanfrage->cSuche;
        $mapping->cSucheNeu      = $cMapping;
        $mapping->nAnzahlGesuche = $oSuchanfrage->nAnzahlGesuche;

        $kSuchanfrageMapping = $db->insert('tsuchanfragemapping', $mapping);

        if (empty($kSuchanfrageMapping)) {
            return 4; // Mapping konnte nicht gespeichert werden
        }
        $db->queryPrepared(
            'UPDATE tsuchanfrage
                SET nAnzahlGesuche = nAnzahlGesuche + :cnt
                WHERE kSprache = :lid
                    AND kSuchanfrage = :sid',
            [
                'cnt' => $oSuchanfrage->nAnzahlGesuche,
                'lid' => (int)$_SESSION['kSprache'],
                'sid' => (int)$oSuchanfrageNeu->kSuchanfrage
            ],
            ReturnType::DEFAULT
        );
        $db->delete('tsuchanfrage', 'kSuchanfrage', (int)$oSuchanfrage->kSuchanfrage);
        $db->queryPrepared(
            "UPDATE tseo
                SET kKey = :sqid
                WHERE cKey = 'kSuchanfrage'
                    AND kKey = :sqid",
            ['sqid' => (int)$oSuchanfrage->kSuchanfrage],
            ReturnType::DEFAULT
        );
    }

    return 1;
}

/**
 * @return int
 */
function gibMaxBewertungen(): int
{
    return (int)Shop::Container()->getDB()->query(
        'SELECT COUNT(*) AS nAnzahl
            FROM tbewertung
            WHERE nAktiv = 0
                AND kSprache = ' . (int)$_SESSION['kSprache'],
        ReturnType::SINGLE_OBJECT
    )->nAnzahl;
}

/**
 * @return int
 */
function gibMaxSuchanfragen(): int
{
    return (int)Shop::Container()->getDB()->query(
        'SELECT COUNT(*) AS nAnzahl
            FROM tsuchanfrage
            WHERE nAktiv = 0
                AND kSprache = ' . (int)$_SESSION['kSprache'],
        ReturnType::SINGLE_OBJECT
    )->nAnzahl;
}

/**
 * @return int
 */
function gibMaxTags(): int
{
    return (int)Shop::Container()->getDB()->query(
        'SELECT COUNT(*) AS nAnzahl
            FROM ttag
            WHERE nAktiv = 0
                AND kSprache = ' . (int)$_SESSION['kSprache'],
        ReturnType::SINGLE_OBJECT
    )->nAnzahl;
}

/**
 * @return int
 */
function gibMaxNewskommentare(): int
{
    return (int)Shop::Container()->getDB()->query(
        'SELECT COUNT(tnewskommentar.kNewsKommentar) AS nAnzahl
            FROM tnewskommentar
            JOIN tnews 
                ON tnews.kNews = tnewskommentar.kNews
            JOIN tnewssprache t 
                ON tnews.kNews = t.kNews
            WHERE tnewskommentar.nAktiv = 0
                AND t.languageID = ' . (int)$_SESSION['kSprache'],
        ReturnType::SINGLE_OBJECT
    )->nAnzahl;
}

/**
 * @return int
 */
function gibMaxNewsletterEmpfaenger(): int
{
    return (int)Shop::Container()->getDB()->query(
        'SELECT COUNT(*) AS nAnzahl
            FROM tnewsletterempfaenger
            WHERE nAktiv = 0
                AND kSprache = ' . (int)$_SESSION['kSprache'],
        ReturnType::SINGLE_OBJECT
    )->nAnzahl;
}
