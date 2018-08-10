<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Fügt für einen bestimmten Artikel, in einer bestimmten Sprache eine Bewertung hinzu.
 *
 * @param int    $productID
 * @param int    $customerID
 * @param int    $langID
 * @param string $title
 * @param string $text
 * @param int    $stars
 * @return bool
 */
function speicherBewertung(int $productID, int $customerID, int $langID, $title, $text, int $stars)
{
    $conf = Shop::getSettings([CONF_BEWERTUNG]);
    if ($customerID <= 0 || $conf['bewertung']['bewertung_anzeigen'] !== 'Y') {
        return false;
    }
    $title   = StringHandler::htmlentities(StringHandler::filterXSS($title));
    $text    = StringHandler::htmlentities(StringHandler::filterXSS($text));
    $article = new Artikel();
    $article->fuelleArtikel($productID, Artikel::getDefaultOptions());
    $url = !empty($article->cURLFull)
        ? ($article->cURLFull . '?')
        : (Shop::getURL() . '/?a=' . $productID . '&');

    if ($productID <= 0 || $langID <= 0 || $title === '' || $text === '' || $stars <= 0) {
        header('Location: ' . $url . 'bewertung_anzeigen=1&cFehler=f01', true, 303);
        exit;
    }
    unset($oBewertungBereitsVorhanden);
    // Prüfe ob die Einstellung (Bewertung nur bei bereits gekauftem Artikel) gesetzt ist
    // und der Kunde den Artikel bereits gekauft hat
    if (pruefeKundeArtikelGekauft($productID, $_SESSION['Kunde']->kKunde)) {
        header('Location: ' . $url . 'bewertung_anzeigen=1&cFehler=f03');
        exit;
    }
    $reward                  = 0.0;
    $rating                  = new stdClass();
    $rating->kArtikel        = $productID;
    $rating->kKunde          = $customerID;
    $rating->kSprache        = $langID;
    $rating->cName           = $_SESSION['Kunde']->cVorname . ' ' . substr($_SESSION['Kunde']->cNachname, 0, 1);
    $rating->cTitel          = $title;
    $rating->cText           = strip_tags($text);
    $rating->nHilfreich      = 0;
    $rating->nNichtHilfreich = 0;
    $rating->nSterne         = $stars;
    $rating->nAktiv          = ($conf['bewertung']['bewertung_freischalten'] === 'N') ? 1 : 0;
    $rating->dDatum          = date('Y-m-d H:i:s');

    executeHook(HOOK_BEWERTUNG_INC_SPEICHERBEWERTUNG, ['rating' => &$rating]);

    $ratingID      = Shop::Container()->getDB()->select(
        'tbewertung',
        ['kArtikel', 'kKunde'],
        [$productID, $customerID]
    ) !== null
        ? Shop::Container()->getDB()->update('tbewertung', ['kArtikel', 'kKunde'], [$productID, $customerID], $rating)
        : Shop::Container()->getDB()->insert('tbewertung', $rating);
    $nFreischalten = 1;

    if ($conf['bewertung']['bewertung_freischalten'] === 'N') {
        $nFreischalten = 0;
        aktualisiereDurchschnitt($productID, $conf['bewertung']['bewertung_freischalten']);
        $reward = checkeBewertungGuthabenBonus($ratingID, $conf);
        // Clear Cache
        Shop::Container()->getCache()->flushTags([CACHING_GROUP_ARTICLE . '_' . $productID]);
    }
    unset($oBewertungBereitsVorhanden);
    if ($nFreischalten === 0) {
        if ($reward > 0) {
            header('Location: ' . $url . 'bewertung_anzeigen=1&fB=' .
                $reward . '&cHinweis=h04',
                true,
                301
            );
            exit;
        }
        header('Location: ' . $url . 'bewertung_anzeigen=1&cHinweis=h01', true, 303);
        exit;
    }
    header('Location: ' . $url . 'bewertung_anzeigen=1&cHinweis=h05', true, 303);
    exit;
}


/**
 * Speichert für eine bestimmte Bewertung und bestimmten Kunden ab, ob sie hilfreich oder nicht hilfreich war.
 *
 * @param int $productID
 * @param int $customerID
 * @param int $langID
 * @param int $page
 * @param int $stars
 */
function speicherHilfreich(int $productID, int $customerID, int $langID, int $page = 1, int $stars = 0)
{
    $helpful = 0;
    $conf    = Shop::getSettings([CONF_BEWERTUNG]);
    // Prüfe ob Kunde eingeloggt
    if ($customerID <= 0
        || $productID <= 0
        || $langID <= 0
        || $conf['bewertung']['bewertung_anzeigen'] !== 'Y'
        || $conf['bewertung']['bewertung_hilfreich_anzeigen'] !== 'Y'
    ) {
        return;
    }
    $ratings = Shop::Container()->getDB()->selectAll(
        'tbewertung',
        ['kArtikel', 'kSprache'],
        [$productID, $langID],
        'kBewertung'
    );
    if (count($ratings) === 0) {
        return;
    }
    $ratingID = 0;
    foreach ($ratings as $rating) {
        $idx = 'hilfreich_' . $rating->kBewertung;
        if (isset($_POST[$idx])) {
            $ratingID = (int)$rating->kBewertung;
            $helpful  = 1;
        }
        $idx = 'nichthilfreich_' . $rating->kBewertung;
        if (isset($_POST[$idx])) {
            $ratingID = (int)$rating->kBewertung;
            $helpful  = 0;
        }
    }
    $redir         = '&btgseite=' . $page . '&btgsterne=' . $stars;
    $helpfulRating = Shop::Container()->getDB()->select(
        'tbewertunghilfreich',
        ['kBewertung', 'kKunde'],
        [$ratingID, $customerID]
    );
    // Hat der Kunde für diese Bewertung noch keine hilfreich flag gesetzt?
    if ((int)$helpfulRating->kKunde === 0) {
        unset($helpfulRating);
        $rating = Shop::Container()->getDB()->select('tbewertung', 'kBewertung', $ratingID);
        if ($rating !== null && (int)$rating->kKunde !== (int)$_SESSION['Kunde']->kKunde) {
            $helpfulRating             = new stdClass();
            $helpfulRating->kBewertung = $ratingID;
            $helpfulRating->kKunde     = $customerID;
            $helpfulRating->nBewertung = 0;
            // Wenn Hilfreich neu für eine Bewertung eingetragen wird und diese positiv ist
            if ($helpful === 1) {
                $helpfulRating->nBewertung = 1;
                Shop::Container()->getDB()->queryPrepared(
                    'UPDATE tbewertung
                        SET nHilfreich = nHilfreich + 1
                        WHERE kBewertung = :rid',
                    ['rid' => $ratingID],
                    \DB\ReturnType::AFFECTED_ROWS
                );
            } else {
                // Wenn Hilfreich neu für eine Bewertung eingetragen wird und diese negativ ist
                $helpfulRating->nBewertung = 0;
                Shop::Container()->getDB()->queryPrepared(
                    'UPDATE tbewertung
                        SET nNichtHilfreich = nNichtHilfreich + 1
                        WHERE kBewertung = :rid',
                    ['rid' => $ratingID],
                    \DB\ReturnType::AFFECTED_ROWS
                );
            }

            executeHook(HOOK_BEWERTUNG_INC_SPEICHERBEWERTUNGHILFREICH, ['rating' => &$helpfulRating]);

            Shop::Container()->getDB()->insert('tbewertunghilfreich', $helpfulRating);
            header('Location: ' . Shop::getURL() . '/?a=' . $productID .
                '&bewertung_anzeigen=1&cHinweis=h02' . $redir,
                true,
                303
            );
            exit;
        }
    } elseif ((int)$helpfulRating->kKunde > 0) {
        // Wenn Hilfreich nicht neu (wechsel) für eine Bewertung eingetragen wird und diese positiv ist
        if ($helpful === 1 && $helpfulRating->nBewertung !== $helpful) {
            Shop::Container()->getDB()->queryPrepared(
                'UPDATE tbewertung
                    SET nHilfreich = nHilfreich + 1, nNichtHilfreich = nNichtHilfreich - 1
                    WHERE kBewertung = :rid',
                ['rid' => $ratingID],
                \DB\ReturnType::AFFECTED_ROWS
            );
        } elseif ($helpful === 0 && $helpfulRating->nBewertung !== $helpful) {
            // Wenn Hilfreich neu für (wechsel) eine Bewertung eingetragen wird und diese negativ ist
            Shop::Container()->getDB()->queryPrepared(
                'UPDATE tbewertung
                    SET nHilfreich = nHilfreich-1, nNichtHilfreich = nNichtHilfreich+1
                    WHERE kBewertung = :rid',
                ['rid' => $ratingID],
                \DB\ReturnType::AFFECTED_ROWS
            );
        }

        Shop::Container()->getDB()->queryPrepared(
            'UPDATE tbewertunghilfreich
                SET nBewertung = :rnb
                WHERE kBewertung = :rid
                    AND kKunde = :cid',
            [
                'rid' => $ratingID,
                'rnb' => $helpful,
                'cid' => $customerID
            ],
            \DB\ReturnType::AFFECTED_ROWS
        );
        header('Location: ' . Shop::getURL() . '/?a=' . $productID .
            '&bewertung_anzeigen=1&cHinweis=h03' . $redir,
            true,
            303
        );
        exit;
    }
}

/**
 * @param int    $productID
 * @param string $activate
 * @return bool
 */
function aktualisiereDurchschnitt(int $productID, string $activate): bool
{
    $sql       = $activate === 'Y' ? ' AND nAktiv = 1' : '';
    $countData = Shop::Container()->getDB()->query(
        'SELECT COUNT(*) AS nAnzahl
            FROM tbewertung
            WHERE kArtikel = ' . $productID . $sql,
        \DB\ReturnType::SINGLE_OBJECT
    );

    if ((int)$countData->nAnzahl === 1) {
        $sql = '';
    } elseif ((int)$countData->nAnzahl === 0) {
        Shop::Container()->getDB()->delete('tartikelext', 'kArtikel', $productID);

        return false;
    }

    $avg = Shop::Container()->getDB()->query(
        'SELECT (SUM(nSterne) / COUNT(*)) AS fDurchschnitt
            FROM tbewertung
            WHERE kArtikel = ' . $productID . $sql,
        \DB\ReturnType::SINGLE_OBJECT
    );

    if (isset($avg->fDurchschnitt) && $avg->fDurchschnitt > 0) {
        Shop::Container()->getDB()->delete('tartikelext', 'kArtikel', $productID);
        $oArtikelExt                          = new stdClass();
        $oArtikelExt->kArtikel                = $productID;
        $oArtikelExt->fDurchschnittsBewertung = (float)$avg->fDurchschnitt;

        Shop::Container()->getDB()->insert('tartikelext', $oArtikelExt);
    }

    return true;
}

/**
 * @param int $productID
 * @param int $customerID
 * @return int
 */
function pruefeKundeArtikelBewertet(int $productID, int $customerID)
{
    if ($customerID > 0) {
        $oBewertung = Shop::Container()->getDB()->select(
            'tbewertung',
            ['kKunde', 'kArtikel', 'kSprache'],
            [$customerID, $productID, Shop::getLanguageID()]
        );
        if (isset($oBewertung->kKunde) && $oBewertung->kKunde > 0) {
            return 1;
        }
    }

    return 0;
}

/**
 * @param int $productID
 * @param int $customerID
 * @return int
 */
function pruefeKundeArtikelGekauft(int $productID, int $customerID)
{
    // Prüfen ob der Bewerter diesen Artikel bereits gekauft hat
    if ($customerID > 0 && $productID > 0 && Shop::getSettingValue(CONF_BEWERTUNG, 'bewertung_artikel_gekauft')) {
        $order = Shop::Container()->getDB()->queryPrepared(
            'SELECT tbestellung.kBestellung
                FROM tbestellung
                LEFT JOIN tartikel 
                    ON tartikel.kVaterArtikel = :aid
                JOIN twarenkorb 
                    ON twarenkorb.kWarenkorb = tbestellung.kWarenkorb
                JOIN twarenkorbpos 
                    ON twarenkorbpos.kWarenkorb = twarenkorb.kWarenkorb
                WHERE tbestellung.kKunde = :cid
                    AND (twarenkorbpos.kArtikel = :aid 
                    OR twarenkorbpos.kArtikel = tartikel.kArtikel)',
            [
                'aid' => $productID,
                'cid' => $customerID
            ],
            \DB\ReturnType::SINGLE_OBJECT
        );

        if (!isset($order->kBestellung) || !$order->kBestellung) {
            // Kunde hat diesen Artikel noch nicht gekauft und darf somit laut Einstellung keine Bewertung abgeben
            return 1;
        }
    }

    return 0;
}

/**
 * @param int   $ratingID
 * @param array $conf
 * @return float
 */
function checkeBewertungGuthabenBonus(int $ratingID, array $conf)
{
    $reward = 0.0;
    // Ist Guthaben freigeschaltet? Wenn ja, schreibe dem Kunden den richtigen Betrag gut
    if ($conf['bewertung']['bewertung_guthaben_nutzen'] !== 'Y') {
        return $reward;
    }
    $rating      = Shop::Container()->getDB()->queryPrepared(
        'SELECT kBewertung, kKunde, cText
            FROM tbewertung
            WHERE kBewertung = :rid',
        ['rid' => $ratingID],
        \DB\ReturnType::SINGLE_OBJECT
    );
    $customerID  = (int)$rating->kKunde;
    $ratingBonus = Shop::Container()->getDB()->queryPrepared(
        'SELECT sum(fGuthabenBonus) AS fGuthabenProMonat
            FROM tbewertungguthabenbonus
            WHERE kKunde = :cid
                AND kBewertung != :rid
                AND YEAR(dDatum) = :dyear
                AND MONTH(dDatum) = :dmonth',
        [
            'cid'    => $customerID,
            'rid'    => $ratingID,
            'dyear'  => date('Y'),
            'dmonth' => date('m')
        ],
        \DB\ReturnType::SINGLE_OBJECT
    );
    if ((float)$ratingBonus->fGuthabenProMonat >
        (float)$conf['bewertung']['bewertung_max_guthaben']
    ) {
        return $reward;
    }
    if ((int)$conf['bewertung']['bewertung_stufe2_anzahlzeichen'] <= strlen($rating->cText)) {
        // Prüfen ob die max. Belohnung + das aktuelle Guthaben, das Max des Monats überscchreitet
        // Falls ja, nur die Differenz von Kundenguthaben zu Max im Monat auszahlen
        if (((float)$ratingBonus->fGuthabenProMonat +
                (float)$conf['bewertung']['bewertung_stufe2_guthaben']) >
            (float)$conf['bewertung']['bewertung_max_guthaben']
        ) {
            $reward = (float)$conf['bewertung']['bewertung_max_guthaben'] -
                (float)$ratingBonus->fGuthabenProMonat;
        } else {
            $reward = (float)$conf['bewertung']['bewertung_stufe2_guthaben'];
        }

        // tkunde Guthaben updaten
        Shop::Container()->getDB()->queryPrepared(
            'UPDATE tkunde
                SET fGuthaben = fGuthaben + :rew
                    WHERE kKunde = :cid',
            [
                'cid' => $customerID,
                'rew' => $reward
            ],
            \DB\ReturnType::AFFECTED_ROWS
        );
        $ratingBonus                 = new stdClass();
        $ratingBonus->kBewertung     = $ratingID;
        $ratingBonus->kKunde         = $customerID;
        $ratingBonus->fGuthabenBonus = $reward;
        $ratingBonus->dDatum         = 'now()';

        if (Shop::Container()->getDB()->select(
                'tbewertungguthabenbonus',
                ['kBewertung', 'kKunde'],
                [$ratingID, $customerID]) !== null
        ) {
            Shop::Container()->getDB()->queryPrepared(
                'UPDATE tbewertungguthabenbonus 
                    SET fGuthabenBonus = :reward 
                    WHERE kBewertung = :feedback',
                [
                    'reward'   => $reward,
                    'feedback' => $ratingID
                ],
                \DB\ReturnType::SINGLE_OBJECT
            );

        } else {
            Shop::Container()->getDB()->insert('tbewertungguthabenbonus', $ratingBonus);
        }
    } else {
        // Prüfen ob die max. Belohnung + das aktuelle Guthaben, das Max des Monats überschreitet
        // Falls ja, nur die Differenz von Kundenguthaben zu Max im Monat auszahlen
        if (((float)$ratingBonus->fGuthabenProMonat +
                (float)$conf['bewertung']['bewertung_stufe1_guthaben']) >
            (float)$conf['bewertung']['bewertung_max_guthaben']) {
            $reward = (float)$conf['bewertung']['bewertung_max_guthaben'] -
                (float)$ratingBonus->fGuthabenProMonat;
        } else {
            $reward = (float)$conf['bewertung']['bewertung_stufe1_guthaben'];
        }
        Shop::Container()->getDB()->queryPrepared(
            'UPDATE tkunde
                SET fGuthaben = fGuthaben + :rew
                WHERE kKunde = :cid',
            [
                'cid' => $customerID,
                'rew' => $reward
            ],
            \DB\ReturnType::AFFECTED_ROWS
        );
        $ratingBonus                 = new stdClass();
        $ratingBonus->kBewertung     = $ratingID;
        $ratingBonus->kKunde         = $customerID;
        $ratingBonus->fGuthabenBonus = $reward;
        $ratingBonus->dDatum         = 'now()';
        if (Shop::Container()->getDB()->select(
                'tbewertungguthabenbonus',
                ['kBewertung', 'kKunde'],
                [$ratingID, $customerID]) !== null
        ) {
            Shop::Container()->getDB()->queryPrepared(
                'UPDATE tbewertungguthabenbonus 
                    SET fGuthabenBonus = :reward 
                    WHERE kBewertung = :feedback',
                [
                    'reward'   => $reward,
                    'feedback' => $ratingID
                ],
                \DB\ReturnType::SINGLE_OBJECT
            );
        } else {
            Shop::Container()->getDB()->insert('tbewertungguthabenbonus', $ratingBonus);
        }
    }
    require_once PFAD_ROOT . PFAD_INCLUDES . 'mailTools.php';
    $obj                          = new stdClass();
    $obj->tkunde                  = new Kunde($ratingBonus->kKunde);
    $obj->oBewertungGuthabenBonus = $ratingBonus;
    sendeMail(MAILTEMPLATE_BEWERTUNG_GUTHABEN, $obj);

    return $reward;
}

/**
 * @param int $ratingID
 * @return bool
 */
function BewertungsGuthabenBonusLoeschen(int $ratingID)
{
    $rating = Shop::Container()->getDB()->select('tbewertung', 'kBewertung', $ratingID);
    if ($rating === null || $rating->kBewertung <= 0) {
        return false;
    }
    $bonus = Shop::Container()->getDB()->select(
        'tbewertungguthabenbonus',
        'kBewertung',
        (int)$rating->kBewertung,
        'kKunde',
        (int)$rating->kKunde
    );
    if ($bonus !== null && $bonus->kBewertungGuthabenBonus > 0) {
        $oKunde = Shop::Container()->getDB()->select('tkunde', 'kKunde', (int)$rating->kKunde);
        if ($oKunde !== null && $oKunde->kKunde > 0) {
            Shop::Container()->getDB()->delete(
                'tbewertungguthabenbonus',
                'kBewertungGuthabenBonus',
                $bonus->kBewertungGuthabenBonus
            );
            $balance        = $oKunde->fGuthaben - (float)$bonus->fGuthabenBonus;
            $upd            = new stdClass();
            $upd->fGuthaben = (($balance > 0) ? $balance : 0);
            Shop::Container()->getDB()->update('tkunde', 'kKunde', (int)$rating->kKunde, $upd);

            return true;
        }
    }

    return false;
}
