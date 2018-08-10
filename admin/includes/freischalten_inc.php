<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

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
            WHERE " . $cond . "tbewertung.nAktiv = 0
                " . $cSuchSQL->cWhere . "
            ORDER BY tbewertung.kArtikel, tbewertung.dDatum DESC" . $cSQL,
        \DB\ReturnType::ARRAY_OF_OBJECTS
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
            WHERE nAktiv = 0 " . $cond . $cSuchSQL->cWhere . "
            ORDER BY " . $cSuchSQL->cOrder . $cSQL,
        \DB\ReturnType::ARRAY_OF_OBJECTS
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
        \DB\ReturnType::ARRAY_OF_OBJECTS
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
    $cond = $checkLanguage === true
        ? ' AND tnews.kSprache = ' . (int)$_SESSION['kSprache'] . ' '
        : '';
    $oNewsKommentar_arr = Shop::Container()->getDB()->query(
        "SELECT tnewskommentar.*, DATE_FORMAT(tnewskommentar.dErstellt, '%d.%m.%Y  %H:%i') AS dErstellt_de, 
            tkunde.kKunde, tkunde.cVorname, tkunde.cNachname, tnews.cBetreff
            FROM tnewskommentar
            JOIN tnews 
                ON tnews.kNews = tnewskommentar.kNews
            LEFT JOIN tkunde 
                ON tkunde.kKunde = tnewskommentar.kKunde
            WHERE tnewskommentar.nAktiv = 0" .
            $cSuchSQL->cWhere . $cond . $cSQL,
        \DB\ReturnType::ARRAY_OF_OBJECTS
    );
    foreach ($oNewsKommentar_arr as $i => $oNewsKommentar) {
        $oKunde = new Kunde($oNewsKommentar->kKunde ?? 0);

        $oNewsKommentar_arr[$i]->cNachname = $oKunde->cNachname;
    }

    return $oNewsKommentar_arr;
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
        " ORDER BY " . $cSuchSQL->cOrder . $cSQL,
        \DB\ReturnType::ARRAY_OF_OBJECTS
    );
}

/**
 * @param array $kBewertung_arr
 * @param array $kArtikel_arr
 * @param array $kBewertungAll_arr
 * @return bool
 */
function schalteBewertungFrei($kBewertung_arr, $kArtikel_arr, $kBewertungAll_arr): bool
{
    global $Einstellungen;

    if (is_array($kBewertung_arr) && count($kBewertung_arr) > 0) {
        $tags = [];
        foreach ($kBewertung_arr as $i => $kBewertung) {
            //$kBewertung_arr and $kArtikel_arr can have different sizes, since $kArtikel_arr is generated by hidden inputs
            //and $kBewertung_arr is generated by actually clicked checkboxes. so the real article ID is taken from the all ratings list
            //which countains ALL the ratings available and not just the ones that were checked
            $idx        = array_search($kBewertung, $kBewertungAll_arr);
            $kArtikel   = ($idx !== false) ? $kArtikel_arr[$idx] : $kArtikel_arr[$i];
            $kArtikel   = (int)$kArtikel;
            $kBewertung = (int)$kBewertung;

            Shop::Container()->getDB()->query(
                'UPDATE tbewertung
                    SET nAktiv = 1
                    WHERE kBewertung = ' . $kBewertung,
                \DB\ReturnType::AFFECTED_ROWS
            );
            // Durchschnitt neu berechnen
            aktualisiereDurchschnitt($kArtikel, $Einstellungen['bewertung']['bewertung_freischalten']);
            // Berechnet BewertungGuthabenBonus
            checkeBewertungGuthabenBonus($kBewertung, $Einstellungen);
            $tags[] = CACHING_GROUP_ARTICLE . '_' . $kArtikel;
        }
        // Clear Cache
        Shop::Cache()->flushTags(array_unique($tags));

        return true;
    }

    return false;
}

/**
 * @param array $kSuchanfrage_arr
 * @return bool
 */
function schalteSuchanfragenFrei($kSuchanfrage_arr): bool
{
    if (!is_array($kSuchanfrage_arr) || count($kSuchanfrage_arr) === 0) {
        return false;
    }
    foreach ($kSuchanfrage_arr as $i => $kSuchanfrage) {
        $kSuchanfrage = (int)$kSuchanfrage;
        $oSuchanfrage = Shop::Container()->getDB()->query(
            'SELECT kSuchanfrage, kSprache, cSuche
                FROM tsuchanfrage
                WHERE kSuchanfrage = ' . $kSuchanfrage,
            \DB\ReturnType::SINGLE_OBJECT
        );

        if ($oSuchanfrage->kSuchanfrage > 0) {
            Shop::Container()->getDB()->delete(
                'tseo',
                ['cKey', 'kKey', 'kSprache'],
                ['kSuchanfrage', $kSuchanfrage, (int)$oSuchanfrage->kSprache]
            );
            // Aktivierte Suchanfragen in tseo eintragen
            $oSeo           = new stdClass();
            $oSeo->cSeo     = checkSeo(getSeo($oSuchanfrage->cSuche));
            $oSeo->cKey     = 'kSuchanfrage';
            $oSeo->kKey     = $kSuchanfrage;
            $oSeo->kSprache = $oSuchanfrage->kSprache;
            Shop::Container()->getDB()->insert('tseo', $oSeo);
            Shop::Container()->getDB()->update(
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
 * @param array $kTag_arr
 * @return bool
 */
function schalteTagsFrei($kTag_arr): bool
{
    if (!is_array($kTag_arr) || count($kTag_arr) === 0) {
        return false;
    }
    $kTag_arr = array_map('intval', $kTag_arr);
    $tags     = [];
    $articles = Shop::Container()->getDB()->query(
        'SELECT DISTINCT kArtikel
            FROM ttagartikel
            WHERE kTag IN (' . implode(',', $kTag_arr) . ')',
        \DB\ReturnType::ARRAY_OF_OBJECTS
    );
    foreach ($articles as $_article) {
        $tags[] = CACHING_GROUP_ARTICLE . '_' . $_article->kArtikel;
    }
    foreach ($kTag_arr as $kTag) {
        $kTag = (int)$kTag;
        $oTag = Shop::Container()->getDB()->select('ttag', 'kTag', $kTag);
        if (isset($oTag->kTag) && $oTag->kTag > 0) {
            // Aktivierte Suchanfragen in tseo eintragen
            Shop::Container()->getDB()->delete(
                'tseo',
                ['cKey', 'kKey', 'kSprache'],
                ['kTag', $kTag, (int)$oTag->kSprache]
            );
            $oSeo           = new stdClass();
            $oSeo->cSeo     = checkSeo(getSeo($oTag->cName));
            $oSeo->cKey     = 'kTag';
            $oSeo->kKey     = $kTag;
            $oSeo->kSprache = (int)$oTag->kSprache;
            Shop::Container()->getDB()->insert('tseo', $oSeo);
            Shop::Container()->getDB()->update(
                'ttag',
                'kTag',
                $kTag,
                (object)['nAktiv' => 1, 'cSeo' => $oSeo->cSeo]
            );
        }
    }
    Shop::Cache()->flushTags($tags);

    return true;
}

/**
 * @param array $kNewsKommentar_arr
 * @return bool
 */
function schalteNewskommentareFrei($kNewsKommentar_arr): bool
{
    if (!is_array($kNewsKommentar_arr) || count($kNewsKommentar_arr) === 0) {
        return false;
    }
    $kNewsKommentar_arr = array_map('intval', $kNewsKommentar_arr);

    Shop::Container()->getDB()->query(
        'UPDATE tnewskommentar
            SET nAktiv = 1
            WHERE kNewsKommentar IN (' . implode(',', $kNewsKommentar_arr) . ')',
        \DB\ReturnType::AFFECTED_ROWS
    );

    return true;
}

/**
 * @param array $kNewsletterEmpfaenger_arr
 * @return bool
 */
function schalteNewsletterempfaengerFrei($kNewsletterEmpfaenger_arr): bool
{
    if (!is_array($kNewsletterEmpfaenger_arr) || count($kNewsletterEmpfaenger_arr) === 0) {
        return false;
    }
    $kNewsletterEmpfaenger_arr = array_map('intval', $kNewsletterEmpfaenger_arr);

    Shop::Container()->getDB()->query(
        'UPDATE tnewsletterempfaenger
            SET nAktiv = 1
            WHERE kNewsletterEmpfaenger IN (' . implode(',', $kNewsletterEmpfaenger_arr) .')',
        \DB\ReturnType::AFFECTED_ROWS
    );

    return true;
}

/**
 * @param array $kBewertung_arr
 * @return bool
 */
function loescheBewertung($kBewertung_arr): bool
{
    if (!is_array($kBewertung_arr) || count($kBewertung_arr) === 0) {
        return false;
    }
    $kBewertung_arr = array_map('intval', $kBewertung_arr);

    Shop::Container()->getDB()->query(
        'DELETE FROM tbewertung
            WHERE kBewertung IN (' . implode(',', $kBewertung_arr) . ')',
        \DB\ReturnType::AFFECTED_ROWS
    );

    return true;
}

/**
 * @param array $kSuchanfrage_arr
 * @return bool
 */
function loescheSuchanfragen($kSuchanfrage_arr): bool
{
    if (!is_array($kSuchanfrage_arr) || count($kSuchanfrage_arr) === 0) {
        return false;
    }
    $kSuchanfrage_arr = array_map('intval', $kSuchanfrage_arr);

    Shop::Container()->getDB()->query(
        'DELETE FROM tsuchanfrage
            WHERE kSuchanfrage IN (' . implode(',', $kSuchanfrage_arr) . ')',
        \DB\ReturnType::AFFECTED_ROWS
    );
    Shop::Container()->getDB()->query(
        "DELETE FROM tseo
            WHERE cKey = 'kSuchanfrage'
                AND kKey IN (" . implode(',', $kSuchanfrage_arr) . ")",
        \DB\ReturnType::AFFECTED_ROWS
    );

    return true;
}

/**
 * @param array $kTag_arr
 * @return bool
 */
function loescheTags($kTag_arr): bool
{
    if (!is_array($kTag_arr) || count($kTag_arr) === 0) {
        return false;
    }
    $kTag_arr = array_map('intval', $kTag_arr);

    Shop::Container()->getDB()->query(
        'DELETE ttag, ttagartikel 
            FROM ttag
            LEFT JOIN ttagartikel 
                ON ttagartikel.kTag = ttag.kTag
            WHERE ttag.kTag IN (' . implode(',', $kTag_arr) . ')',
        \DB\ReturnType::AFFECTED_ROWS
    );

    return true;
}

/**
 * @param array $kNewsKommentar_arr
 * @return bool
 */
function loescheNewskommentare($kNewsKommentar_arr): bool
{
    if (!is_array($kNewsKommentar_arr) || count($kNewsKommentar_arr) === 0) {
        return false;
    }
    $kNewsKommentar_arr = array_map('intval', $kNewsKommentar_arr);

    Shop::Container()->getDB()->query(
        'DELETE FROM tnewskommentar
            WHERE kNewsKommentar IN (' . implode(',', $kNewsKommentar_arr) . ')',
        \DB\ReturnType::AFFECTED_ROWS
    );

    return true;
}

/**
 * @param array $kNewsletterEmpfaenger_arr
 * @return bool
 */
function loescheNewsletterempfaenger($kNewsletterEmpfaenger_arr): bool
{
    if (!is_array($kNewsletterEmpfaenger_arr) || count($kNewsletterEmpfaenger_arr) === 0) {
        return false;
    }
    $kNewsletterEmpfaenger_arr = array_map('intval', $kNewsletterEmpfaenger_arr);

    Shop::Container()->getDB()->query(
        'DELETE FROM tnewsletterempfaenger
            WHERE kNewsletterEmpfaenger IN (' . implode(',', $kNewsletterEmpfaenger_arr) . ')',
        \DB\ReturnType::AFFECTED_ROWS
    );

    return true;
}

/**
 * @param array  $kSuchanfrage_arr
 * @param string $cMapping
 * @return int
 */
function mappeLiveSuche($kSuchanfrage_arr, $cMapping): int
{
    if (!is_array($kSuchanfrage_arr) || count($kSuchanfrage_arr) === 0 || strlen($cMapping) === 0) {
        return 2; // Leere Übergabe
    }
    foreach ($kSuchanfrage_arr as $kSuchanfrage) {
        $oSuchanfrage = Shop::Container()->getDB()->select('tsuchanfrage', 'kSuchanfrage', (int)$kSuchanfrage);
        if ($oSuchanfrage === null || empty($oSuchanfrage->kSuchanfrage)) {
            return 3; // Mindestens eine Suchanfrage wurde nicht in der Datenbank gefunden.
        }
        if (strtolower($oSuchanfrage->cSuche) === strtolower($cMapping)) {
            return 6; // Es kann nicht auf sich selbst gemappt werden
        }
        $oSuchanfrageNeu = Shop::Container()->getDB()->select('tsuchanfrage', 'cSuche', $cMapping);
        if ($oSuchanfrageNeu === null || empty($oSuchanfrageNeu->kSuchanfrage)) {
            return 5; // Sie haben versucht auf eine nicht existierende Suchanfrage zu mappen
        }
        $oSuchanfrageMapping                 = new stdClass();
        $oSuchanfrageMapping->kSprache       = $_SESSION['kSprache'];
        $oSuchanfrageMapping->cSuche         = $oSuchanfrage->cSuche;
        $oSuchanfrageMapping->cSucheNeu      = $cMapping;
        $oSuchanfrageMapping->nAnzahlGesuche = $oSuchanfrage->nAnzahlGesuche;

        $kSuchanfrageMapping = Shop::Container()->getDB()->insert('tsuchanfragemapping', $oSuchanfrageMapping);

        if (empty($kSuchanfrageMapping)) {
            return 4; // Mapping konnte nicht gespeichert werden
        }
        Shop::Container()->getDB()->queryPrepared(
            'UPDATE tsuchanfrage
                SET nAnzahlGesuche = nAnzahlGesuche + :cnt
                WHERE kSprache = :lid
                    AND kSuchanfrage = :sid',
            [
                'cnt' => $oSuchanfrage->nAnzahlGesuche,
                'lid' => (int)$_SESSION['kSprache'],
                'sid' => (int)$oSuchanfrageNeu->kSuchanfrage
            ],
            \DB\ReturnType::DEFAULT
        );
        Shop::Container()->getDB()->delete('tsuchanfrage', 'kSuchanfrage', (int)$oSuchanfrage->kSuchanfrage);
        Shop::Container()->getDB()->query(
            "UPDATE tseo
                SET kKey = " . (int)$oSuchanfrageNeu->kSuchanfrage . "
                WHERE cKey = 'kSuchanfrage'
                    AND kKey = " . (int)$oSuchanfrage->kSuchanfrage,
            \DB\ReturnType::DEFAULT
        );
    }

    return 1; // Alles O.K.
}

/**
 * @return int
 */
function gibMaxBewertungen(): int
{
    return (int)Shop::Container()->getDB()->query(
        'SELECT count(*) AS nAnzahl
            FROM tbewertung
            WHERE nAktiv = 0
                AND kSprache = ' . (int)$_SESSION['kSprache'],
        \DB\ReturnType::SINGLE_OBJECT
    )->nAnzahl;
}

/**
 * @return int
 */
function gibMaxSuchanfragen(): int
{
    return (int)Shop::Container()->getDB()->query(
        'SELECT count(*) AS nAnzahl
            FROM tsuchanfrage
            WHERE nAktiv = 0
                AND kSprache = ' . (int)$_SESSION['kSprache'],
        \DB\ReturnType::SINGLE_OBJECT
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
        \DB\ReturnType::SINGLE_OBJECT
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
            WHERE tnewskommentar.nAktiv = 0
                AND tnews.kSprache = ' . (int)$_SESSION['kSprache'],
        \DB\ReturnType::SINGLE_OBJECT
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
        \DB\ReturnType::SINGLE_OBJECT
    )->nAnzahl;
}
