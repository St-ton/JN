<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Fügt für einen bestimmten Artikel, in einer bestimmten Sprache eine Bewertung hinzu.
 *
 * @param int    $kArtikel
 * @param int    $kKunde
 * @param int    $kSprache
 * @param string $cTitel
 * @param string $cText
 * @param int    $nSterne
 * @return bool
 */
function speicherBewertung(int $kArtikel, int $kKunde, int $kSprache, $cTitel, $cText, int $nSterne)
{
    $conf     = Shop::getSettings([CONF_BEWERTUNG]);
    if ($kKunde <= 0 || $conf['bewertung']['bewertung_anzeigen'] !== 'Y') {
        return false;
    }
    $cTitel  = StringHandler::htmlentities(StringHandler::filterXSS($cTitel));
    $cText   = StringHandler::htmlentities(StringHandler::filterXSS($cText));
    $article = new Artikel();
    $article->fuelleArtikel($kArtikel, Artikel::getDefaultOptions());
    $url = !empty($article->cURLFull)
        ? ($article->cURLFull . '?')
        : (Shop::getURL() . '/?a=' . $kArtikel . '&');

    if ($kArtikel <= 0 || $kSprache <= 0 || $cTitel === '' || $cText === '' || $nSterne <= 0) {
        header('Location: ' . $url . 'bewertung_anzeigen=1&cFehler=f01', true, 303);
        exit;
    }
    unset($oBewertungBereitsVorhanden);
    // Prüfe ob die Einstellung (Bewertung nur bei bereits gekauftem Artikel) gesetzt ist
    // und der Kunde den Artikel bereits gekauft hat
    if (pruefeKundeArtikelGekauft($kArtikel, $_SESSION['Kunde']->kKunde)) {
        header('Location: ' . $url . 'bewertung_anzeigen=1&cFehler=f03');
        exit;
    }
    $fBelohnung                  = 0.0;
    $oBewertung                  = new stdClass();
    $oBewertung->kArtikel        = $kArtikel;
    $oBewertung->kKunde          = $kKunde;
    $oBewertung->kSprache        = $kSprache;
    $oBewertung->cName           = $_SESSION['Kunde']->cVorname . ' ' .
        substr($_SESSION['Kunde']->cNachname, 0, 1);
    $oBewertung->cTitel          = $cTitel;
    $oBewertung->cText           = strip_tags($cText);
    $oBewertung->nHilfreich      = 0;
    $oBewertung->nNichtHilfreich = 0;
    $oBewertung->nSterne         = $nSterne;
    $oBewertung->nAktiv          = ($conf['bewertung']['bewertung_freischalten'] === 'N') ? 1 : 0;
    $oBewertung->dDatum          = date('Y-m-d H:i:s', time());

    executeHook(HOOK_BEWERTUNG_INC_SPEICHERBEWERTUNG, ['rating' => &$oBewertung]);
    // Speicher Bewertung

    $kBewertung    = Shop::Container()->getDB()->select('tbewertung', ['kArtikel', 'kKunde'], [$kArtikel, $kKunde]) !== null
        ? Shop::Container()->getDB()->update('tbewertung',['kArtikel', 'kKunde'], [$kArtikel, $kKunde], $oBewertung)
        : Shop::Container()->getDB()->insert('tbewertung', $oBewertung);
    $nFreischalten = 1;

    if ($conf['bewertung']['bewertung_freischalten'] === 'N') {
        $nFreischalten = 0;
        aktualisiereDurchschnitt($kArtikel, $conf['bewertung']['bewertung_freischalten']);
        $fBelohnung = checkeBewertungGuthabenBonus($kBewertung, $conf);
        // Clear Cache
        Shop::Container()->getCache()->flushTags([CACHING_GROUP_ARTICLE . '_' . $kArtikel]);
    }
    unset($oBewertungBereitsVorhanden);
    if ($nFreischalten === 0) {
        if ($fBelohnung > 0) {
            header('Location: ' . $url . 'bewertung_anzeigen=1&fB=' .
                $fBelohnung . '&cHinweis=h04', true, 301);
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
 * @param int $kArtikel
 * @param int $kKunde
 * @param int $kSprache
 * @param int $bewertung_seite
 * @param int $bewertung_sterne
 */
function speicherHilfreich(int $kArtikel, int $kKunde, int $kSprache, int $bewertung_seite = 1, int  $bewertung_sterne = 0)
{
    $bHilfreich = 0;
    $conf       = Shop::getSettings([CONF_BEWERTUNG]);
    // Prüfe ob Kunde eingeloggt
    if ($kKunde <= 0
        || $kArtikel <= 0
        || $kSprache <= 0
        || $conf['bewertung']['bewertung_anzeigen'] !== 'Y'
        || $conf['bewertung']['bewertung_hilfreich_anzeigen'] !== 'Y'
    ) {
        return;
    }
    // Hole alle Bewertungen für den auktuellen Artikel und Sprache
    $oBewertung_arr = Shop::Container()->getDB()->selectAll(
        'tbewertung',
        ['kArtikel', 'kSprache'],
        [$kArtikel, $kSprache],
        'kBewertung'
    );
    if (count($oBewertung_arr) === 0) {
        return;
    }
    $kBewertung = 0;
    foreach ($oBewertung_arr as $oBewertung) {
        // Prüf ob die Bewertung als Hilfreich gemarkt ist
        if (isset($_POST['hilfreich_' . $oBewertung->kBewertung])) {
            $kBewertung = (int)$oBewertung->kBewertung;
            $bHilfreich = 1;
        }
        // Prüf ob die Bewertung als nicht Hilfreich gemarkt ist
        if (isset($_POST['nichthilfreich_' . $oBewertung->kBewertung])) {
            $kBewertung = (int)$oBewertung->kBewertung;
            $bHilfreich = 0;
        }
    }
    // Weiterleitungsstring bauen
    $cWeiterleitung = '&btgseite=' . $bewertung_seite . '&btgsterne=' . $bewertung_sterne;
    // Hole alle Einträge aus tbewertunghilfreich für eine bestimmte Bewertung und einen bestimmten Kunde
    $oBewertungHilfreich = Shop::Container()->getDB()->select(
        'tbewertunghilfreich',
        ['kBewertung', 'kKunde'],
        [$kBewertung,  $kKunde]
    );
    // Hat der Kunde für diese Bewertung noch keine hilfreich flag gesetzt?
    if ((int)$oBewertungHilfreich->kKunde === 0) {
        unset($oBewertungHilfreich);
        $oBewertung = Shop::Container()->getDB()->select('tbewertung', 'kBewertung', $kBewertung);
        if ($oBewertung !== null && (int)$oBewertung->kKunde !== (int)$_SESSION['Kunde']->kKunde) {
            $oBewertungHilfreich             = new stdClass();
            $oBewertungHilfreich->kBewertung = $kBewertung;
            $oBewertungHilfreich->kKunde     = $kKunde;
            $oBewertungHilfreich->nBewertung = 0;
            // Wenn Hilfreich neu für eine Bewertung eingetragen wird und diese positiv ist
            if ($bHilfreich === 1) {
                $oBewertungHilfreich->nBewertung = 1;
                Shop::Container()->getDB()->queryPrepared(
                    'UPDATE tbewertung
                        SET nHilfreich = nHilfreich + 1
                        WHERE kBewertung = :rid',
                    ['rid' => $kBewertung],
                    \DB\ReturnType::AFFECTED_ROWS
                );
            } else {
                // Wenn Hilfreich neu für eine Bewertung eingetragen wird und diese negativ ist
                $oBewertungHilfreich->nBewertung = 0;
                Shop::Container()->getDB()->queryPrepared(
                    'UPDATE tbewertung
                        SET nNichtHilfreich = nNichtHilfreich + 1
                        WHERE kBewertung = :rid',
                    ['rid' => $kBewertung],
                    \DB\ReturnType::AFFECTED_ROWS
                );
            }

            executeHook(HOOK_BEWERTUNG_INC_SPEICHERBEWERTUNGHILFREICH, ['rating' => &$oBewertungHilfreich]);

            Shop::Container()->getDB()->insert('tbewertunghilfreich', $oBewertungHilfreich);
            header('Location: ' . Shop::getURL() . '/?a=' . $kArtikel .
                '&bewertung_anzeigen=1&cHinweis=h02' . $cWeiterleitung, true, 303);
            exit;
        }
    } elseif ((int)$oBewertungHilfreich->kKunde > 0) {
        // Wenn Hilfreich nicht neu (wechsel) für eine Bewertung eingetragen wird und diese positiv ist
        if ($bHilfreich === 1 && $oBewertungHilfreich->nBewertung != $bHilfreich) {
            Shop::Container()->getDB()->queryPrepared(
                'UPDATE tbewertung
                    SET nHilfreich = nHilfreich+1, nNichtHilfreich = nNichtHilfreich-1
                    WHERE kBewertung = :rid',
                ['rid' => $kBewertung],
                \DB\ReturnType::AFFECTED_ROWS
            );
        } elseif ($bHilfreich === 0 && $oBewertungHilfreich->nBewertung != $bHilfreich) {
            // Wenn Hilfreich neu für (wechsel) eine Bewertung eingetragen wird und diese negativ ist
            Shop::Container()->getDB()->queryPrepared(
                'UPDATE tbewertung
                    SET nHilfreich = nHilfreich-1, nNichtHilfreich = nNichtHilfreich+1
                    WHERE kBewertung = :rid',
                ['rid' => $kBewertung],
                \DB\ReturnType::AFFECTED_ROWS
            );
        }

        Shop::Container()->getDB()->queryPrepared(
            'UPDATE tbewertunghilfreich
                SET nBewertung = :rnb
                WHERE kBewertung = :rid
                    AND kKunde = :cid',
            [
                'rid' => $kBewertung,
                'rnb' => $bHilfreich,
                'cid' => $kKunde
            ],
            \DB\ReturnType::AFFECTED_ROWS
        );
        header('Location: ' . Shop::getURL() . '/?a=' . $kArtikel .
            '&bewertung_anzeigen=1&cHinweis=h03' . $cWeiterleitung, true, 303);
        exit;
    }
}

/**
 * @param int    $kArtikel
 * @param string $cFreischalten
 * @return bool
 */
function aktualisiereDurchschnitt(int $kArtikel, $cFreischalten)
{
    $cFreiSQL         = $cFreischalten === 'Y' ? ' AND nAktiv = 1' : '';
    $oAnzahlBewertung = Shop::Container()->getDB()->query(
        "SELECT count(*) AS nAnzahl
            FROM tbewertung
            WHERE kArtikel = " . $kArtikel . $cFreiSQL,
        \DB\ReturnType::SINGLE_OBJECT
    );

    if ((int)$oAnzahlBewertung->nAnzahl === 1) {
        $cFreiSQL = '';
    } elseif ((int)$oAnzahlBewertung->nAnzahl === 0) {
        Shop::Container()->getDB()->delete('tartikelext', 'kArtikel', $kArtikel);

        return false;
    }

    $oBewDurchschnitt = Shop::Container()->getDB()->query(
        "SELECT (sum(nSterne) / count(*)) AS fDurchschnitt
            FROM tbewertung
            WHERE kArtikel = " . $kArtikel . $cFreiSQL,
        \DB\ReturnType::SINGLE_OBJECT
    );

    if (isset($oBewDurchschnitt->fDurchschnitt) && $oBewDurchschnitt->fDurchschnitt > 0) {
        Shop::Container()->getDB()->delete('tartikelext', 'kArtikel', $kArtikel);
        $oArtikelExt                          = new stdClass();
        $oArtikelExt->kArtikel                = $kArtikel;
        $oArtikelExt->fDurchschnittsBewertung = (float)$oBewDurchschnitt->fDurchschnitt;

        Shop::Container()->getDB()->insert('tartikelext', $oArtikelExt);
    }

    return true;
}

/**
 * @param int $kArtikel
 * @param int $kKunde
 * @return int
 */
function pruefeKundeArtikelBewertet(int $kArtikel, int $kKunde)
{
    // Pürfen ob der Bewerter schon diesen Artikel bewertet hat
    if ($kKunde > 0) {
        $oBewertung = Shop::Container()->getDB()->select(
            'tbewertung',
            ['kKunde', 'kArtikel', 'kSprache'],
            [$kKunde, $kArtikel, Shop::getLanguageID()]
        );
        // Kunde hat den Artikel schon bewertet
        if (isset($oBewertung->kKunde) && $oBewertung->kKunde > 0) {
            return 1;
        }
    }

    return 0;
}

/**
 * @param int $kArtikel
 * @param int $kKunde
 * @return int
 */
function pruefeKundeArtikelGekauft(int $kArtikel, int $kKunde)
{
    // Prüfen ob der Bewerter diesen Artikel bereits gekauft hat
    if ($kKunde > 0 && $kArtikel > 0 && Shop::getSettingValue(CONF_BEWERTUNG, 'bewertung_artikel_gekauft')) {
        $oBestellung = Shop::Container()->getDB()->queryPrepared(
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
                'aid' => $kArtikel,
                'cid' => $kKunde
            ],
            \DB\ReturnType::SINGLE_OBJECT
        );

        if (!isset($oBestellung->kBestellung) || !$oBestellung->kBestellung) {
            // Kunde hat diesen Artikel noch nicht gekauft und darf somit laut Einstellung keine Bewertung abgeben
            return 1;
        }
    }

    return 0;
}

/**
 * @param int   $kBewertung
 * @param array $Einstellungen
 * @return float
 */
function checkeBewertungGuthabenBonus(int $kBewertung, array $Einstellungen)
{
    $fBelohnung = 0.0;
    // Ist Guthaben freigeschaltet? Wenn ja, schreibe dem Kunden den richtigen Betrag gut
    if ($Einstellungen['bewertung']['bewertung_guthaben_nutzen'] !== 'Y') {
        return $fBelohnung;
    }
    // Hole Kunden und cText der Bewertung
    $oBewertung              = Shop::Container()->getDB()->queryPrepared(
        'SELECT kBewertung, kKunde, cText
            FROM tbewertung
            WHERE kBewertung = :rid',
        ['rid' => $kBewertung],
        \DB\ReturnType::SINGLE_OBJECT
    );
    $kKunde                  = (int)$oBewertung->kKunde;
    $oBewertungGuthabenBonus = Shop::Container()->getDB()->queryPrepared(
        'SELECT sum(fGuthabenBonus) AS fGuthabenProMonat
            FROM tbewertungguthabenbonus
            WHERE kKunde = :ci
                AND kBewertung != :rID
                AND YEAR(dDatum) = :dYear
                AND MONTH(dDatum) = :dMonth',
        [
            'cID'    => $kKunde,
            'rID'    => $kBewertung,
            'dYear'  => date('Y'),
            'dMonth' => date('m')
        ],
        \DB\ReturnType::SINGLE_OBJECT
    );
    if ((float)$oBewertungGuthabenBonus->fGuthabenProMonat >
        (float)$Einstellungen['bewertung']['bewertung_max_guthaben']
    ) {
        return $fBelohnung;
    }
    // Reichen die Zeichen in der Bewertung, um das Stufe 2 Guthaben zu erhalten?
    if ((int)$Einstellungen['bewertung']['bewertung_stufe2_anzahlzeichen'] <= strlen($oBewertung->cText)) {
        // Prüfen ob die max. Belohnung + das aktuelle Guthaben, das Max des Monats überscchreitet
        // Falls ja, nur die Differenz von Kundenguthaben zu Max im Monat auszahlen
        if (((float)$oBewertungGuthabenBonus->fGuthabenProMonat +
                (float)$Einstellungen['bewertung']['bewertung_stufe2_guthaben']) >
            (float)$Einstellungen['bewertung']['bewertung_max_guthaben']
        ) {
            $fBelohnung = (float)$Einstellungen['bewertung']['bewertung_max_guthaben'] -
                (float)$oBewertungGuthabenBonus->fGuthabenProMonat;
        } else {
            $fBelohnung = (float)$Einstellungen['bewertung']['bewertung_stufe2_guthaben'];
        }

        // tkunde Guthaben updaten
        Shop::Container()->getDB()->queryPrepared(
            'UPDATE tkunde
                SET fGuthaben = fGuthaben + :rew
                    WHERE kKunde = :cid',
            [
                'cid' => $kKunde,
                'rew' => $fBelohnung
            ],
            \DB\ReturnType::AFFECTED_ROWS
        );
        // tbewertungguthabenbonus eintragen
        $oBewertungGuthabenBonus                 = new stdClass();
        $oBewertungGuthabenBonus->kBewertung     = $kBewertung;
        $oBewertungGuthabenBonus->kKunde         = $kKunde;
        $oBewertungGuthabenBonus->fGuthabenBonus = $fBelohnung;
        $oBewertungGuthabenBonus->dDatum         = 'now()';

        if (Shop::Container()->getDB()->select(
                'tbewertungguthabenbonus',
                ['kBewertung', 'kKunde'],
                [$kBewertung, $kKunde]) !== null
        ) {
            Shop::Container()->getDB()->queryPrepared(
                'UPDATE tbewertungguthabenbonus 
                    SET fGuthabenBonus = :reward 
                    WHERE kBewertung = :feedback',
                [
                    'reward'   => $fBelohnung,
                    'feedback' => $kBewertung
                ],
                \DB\ReturnType::SINGLE_OBJECT
            );

        } else {
            Shop::Container()->getDB()->insert('tbewertungguthabenbonus', $oBewertungGuthabenBonus);
        }
    } else {
        // Prüfen ob die max. Belohnung + das aktuelle Guthaben, das Max des Monats überschreitet
        // Falls ja, nur die Differenz von Kundenguthaben zu Max im Monat auszahlen
        if (((float)$oBewertungGuthabenBonus->fGuthabenProMonat +
                (float)$Einstellungen['bewertung']['bewertung_stufe1_guthaben']) >
            (float)$Einstellungen['bewertung']['bewertung_max_guthaben']) {
            $fBelohnung = (float)$Einstellungen['bewertung']['bewertung_max_guthaben'] -
                (float)$oBewertungGuthabenBonus->fGuthabenProMonat;
        } else {
            $fBelohnung = (float)$Einstellungen['bewertung']['bewertung_stufe1_guthaben'];
        }

        // tkunde Guthaben updaten
        Shop::Container()->getDB()->queryPrepared(
            'UPDATE tkunde
                SET fGuthaben = fGuthaben + :rew
                WHERE kKunde = :cid',
            [
                'cid' => $kKunde,
                'rew' => $fBelohnung
            ],
            \DB\ReturnType::AFFECTED_ROWS
        );
        // tbewertungguthabenbonus eintragen
        $oBewertungGuthabenBonus                 = new stdClass();
        $oBewertungGuthabenBonus->kBewertung     = $kBewertung;
        $oBewertungGuthabenBonus->kKunde         = $kKunde;
        $oBewertungGuthabenBonus->fGuthabenBonus = $fBelohnung;
        $oBewertungGuthabenBonus->dDatum         = 'now()';
        if (Shop::Container()->getDB()->select(
                'tbewertungguthabenbonus',
                ['kBewertung', 'kKunde'],
                [$kBewertung, $kKunde]) !== null
        ) {
            Shop::Container()->getDB()->queryPrepared(
                'UPDATE tbewertungguthabenbonus 
                    SET fGuthabenBonus = :reward 
                    WHERE kBewertung = :feedback',
                [
                    'reward'   => $fBelohnung,
                    'feedback' => $kBewertung
                ],
                \DB\ReturnType::SINGLE_OBJECT
            );
        } else {
            Shop::Container()->getDB()->insert('tbewertungguthabenbonus', $oBewertungGuthabenBonus);
        }
    }
    require_once PFAD_ROOT . PFAD_INCLUDES . 'mailTools.php';
    $oKunde                       = new Kunde($oBewertungGuthabenBonus->kKunde);
    $obj                          = new stdClass();
    $obj->tkunde                  = $oKunde;
    $obj->oBewertungGuthabenBonus = $oBewertungGuthabenBonus;
    sendeMail(MAILTEMPLATE_BEWERTUNG_GUTHABEN, $obj);

    return $fBelohnung;
}

/**
 * @param int $kBewertung
 * @return bool
 */
function BewertungsGuthabenBonusLoeschen(int $kBewertung)
{
    if ($kBewertung <= 0) {
        return false;
    }
    $oBewertung = Shop::Container()->getDB()->select('tbewertung', 'kBewertung', $kBewertung);
    if ($oBewertung !== null && $oBewertung->kBewertung > 0) {
        $oBewertungGuthabenBonus = Shop::Container()->getDB()->select(
            'tbewertungguthabenbonus',
            'kBewertung',
            (int)$oBewertung->kBewertung,
            'kKunde',
            (int)$oBewertung->kKunde
        );
        if ($oBewertungGuthabenBonus !== null && $oBewertungGuthabenBonus->kBewertungGuthabenBonus > 0) {
            $oKunde = Shop::Container()->getDB()->select('tkunde', 'kKunde', (int)$oBewertung->kKunde);
            if ($oKunde !== null && $oKunde->kKunde > 0) {
                Shop::Container()->getDB()->delete(
                    'tbewertungguthabenbonus',
                    'kBewertungGuthabenBonus',
                    $oBewertungGuthabenBonus->kBewertungGuthabenBonus
                );
                $fGuthaben      = $oKunde->fGuthaben - (float)$oBewertungGuthabenBonus->fGuthabenBonus;
                $upd            = new stdClass();
                $upd->fGuthaben = (($fGuthaben > 0) ? $fGuthaben : 0);
                Shop::Container()->getDB()->update('tkunde', 'kKunde', (int)$oBewertung->kKunde, $upd);

                return true;
            }
        }
    }

    return false;
}
