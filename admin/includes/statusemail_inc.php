<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * @param array $cPost_arr
 * @return bool
 */
function speicherStatusemailEinstellungen($cPost_arr)
{
    if (intval($cPost_arr['nAktiv']) === 0 || (valid_email($cPost_arr['cEmail']) && (is_array($cPost_arr['cIntervall_arr']) &&
                count($cPost_arr['cIntervall_arr']) > 0) && (is_array($cPost_arr['cInhalt_arr']) && count($cPost_arr['cInhalt_arr']) > 0))
    ) {
        if (erstelleStatusemailCron(24)) {
            // Erstellt den Cron Eintrag
            $oStatusemail             = new stdClass();
            $oStatusemail->cEmail     = $cPost_arr['cEmail'];
            $oStatusemail->cIntervall = (is_array($cPost_arr['cIntervall_arr']) && count($cPost_arr['cIntervall_arr']) > 0) ?
                ';' . implode(';', $cPost_arr['cIntervall_arr']) . ';' :
                '';
            $oStatusemail->cInhalt = (is_array($cPost_arr['cInhalt_arr']) && count($cPost_arr['cInhalt_arr']) > 0) ?
                ';' . implode(';', $cPost_arr['cInhalt_arr']) . ';' :
                '';
            $oStatusemail->nAktiv                = (int)$cPost_arr['nAktiv'];
            $oStatusemail->dLetzterTagesVersand  = 'now()';
            $oStatusemail->dLetzterWochenVersand = 'now()';
            $oStatusemail->dLetzterMonatsVersand = 'now()';

            Shop::DB()->query("DELETE FROM tstatusemail", 4);
            Shop::DB()->insert('tstatusemail', $oStatusemail);
        }

        return true;
    }

    return false;
}

/**
 * @param int $nAlleXStunden
 * @return bool
 */
function erstelleStatusemailCron($nAlleXStunden)
{
    if ($nAlleXStunden > 0) {
        Shop::DB()->query(
            "DELETE tcron, tjobqueue
                FROM tcron
                LEFT JOIN tjobqueue ON tjobqueue.kCron = tcron.kCron
                WHERE tcron.kKey = 1
                    AND tcron.cKey = 'nAktiv'
                    AND tcron.cJobArt = 'statusemail'", 4
        );

        $oCron = new Cron(0, 1, $nAlleXStunden, 'statusemail', 'statusemail', 'tstatusemail', 'nAktiv', date('Y-m-d', time() + 3600 * 24) . ' 00:00:00', '00:00:00', '0000-00-00 00:00:00');
        $oCron->speicherInDB();

        return true;
    }

    return false;
}

/**
 * @return mixed
 */
function ladeStatusemailEinstellungen()
{
    $oStatusemailEinstellungen = Shop::DB()->query("SELECT * FROM tstatusemail", 1);
    if (!is_object($oStatusemailEinstellungen)) {
        $oStatusemailEinstellungen = new stdClass();
    }
    $oStatusemailEinstellungen->cIntervallMoeglich_arr = gibIntervallMoeglichkeiten();
    $oStatusemailEinstellungen->cInhaltMoeglich_arr    = gibInhaltMoeglichkeiten();
    $oStatusemailEinstellungen->nIntervall_arr         = (isset($oStatusemailEinstellungen->cIntervall)) ? gibKeyArrayFuerKeyString($oStatusemailEinstellungen->cIntervall, ';') : array();
    $oStatusemailEinstellungen->nInhalt_arr            = (isset($oStatusemailEinstellungen->cInhalt)) ? gibKeyArrayFuerKeyString($oStatusemailEinstellungen->cInhalt, ';') : array();

    return $oStatusemailEinstellungen;
}

/**
 * @return array
 */
function gibIntervallMoeglichkeiten()
{
    return array(
        'Tagesbericht'  => 1,
        'Wochenbericht' => 7,
        'Monatsbericht' => 30
    );
}

/**
 * @return array
 */
function gibInhaltMoeglichkeiten()
{
    return array(
        'Anzahl Produkte pro Kundengruppe'             => 1,
        'Anzahl Neukunden'                             => 2,
        'Anzahl Neukunden, die gekauft haben'          => 3,
        'Anzahl Bestellungen'                          => 4,
        'Anzahl Bestellungen von Neukunden'            => 5,
        'Anzahl Zahlungseing&auml;nge zu Bestellungen' => 23,
        'Anzahl versendeter Bestellungen'              => 24,
        'Anzahl Besucher'                              => 6,
        'Anzahl Besucher von Suchmaschinen'            => 7,
        'Anzahl Bewertungen'                           => 8,
        'Anzahl Bewertungen nicht freigeschaltet'      => 9,
        'Anzahl Bewertungsguthaben gezahlt'            => 10,
        'Anzahl Tags'                                  => 11,
        'Anzahl Tags nicht freigeschaltet'             => 12,
        'Anzahl geworbener Kunden'                     => 13,
        'Anzahl geworbener Kunden, die gekauft haben'  => 14,
        'Anzahl versendeter Wunschlisten'              => 15,
        'Anzahl durchgef&uuml;hrter Umfragen'          => 16,
        'Anzahl neuer Newskommentare'                  => 17,
        'Anzahl Newskommentare nicht freigeschaltet'   => 18,
        'Anzahl neuer Produktanfragen'                 => 19,
        'Anzahl neuer Verf&uuml;gbarkeitsanfragen'     => 20,
        'Anzahl Produktvergleiche'                     => 21,
        'Anzahl genutzter Kupons'                      => 22
    );
}

/**
 * @return array
 */
function gibAnzahlArtikelProKundengruppe()
{
    $oArtikelProKundengruppe_arr = array();
    // Hole alle Kundengruppen im Shop
    $oKundengruppe_arr = Shop::DB()->query(
        "SELECT kKundengruppe, cName
            FROM tkundengruppe", 2
    );

    if (is_array($oKundengruppe_arr) && count($oKundengruppe_arr) > 0) {
        foreach ($oKundengruppe_arr as $oKundengruppe) {
            $oArtikel = Shop::DB()->query(
                "SELECT count(*) AS nAnzahl
                    FROM tartikel
                    LEFT JOIN tartikelsichtbarkeit ON tartikelsichtbarkeit.kArtikel = tartikel.kArtikel
                        AND tartikelsichtbarkeit.kKundengruppe = " . (int)$oKundengruppe->kKundengruppe . "
                    WHERE tartikelsichtbarkeit.kArtikel IS NULL", 1
            );
            $oTMP                = new stdClass();
            $oTMP->nAnzahl       = $oArtikel->nAnzahl;
            $oTMP->kKundengruppe = $oKundengruppe->kKundengruppe;
            $oTMP->cName         = $oKundengruppe->cName;

            $oArtikelProKundengruppe_arr[] = $oTMP;
        }
    }

    return $oArtikelProKundengruppe_arr;
}

/**
 * @param string $dVon
 * @param string $dBis
 * @return int
 */
function gibAnzahlNeukunden($dVon, $dBis)
{
    $dVon = Shop::DB()->escape($dVon);
    $dBis = Shop::DB()->escape($dBis);

    if (strlen($dVon) > 0 && strlen($dBis) > 0) {
        $oKunde = Shop::DB()->query(
            "SELECT count(*) AS nAnzahl
                FROM tkunde
                WHERE dErstellt >= '" . $dVon . "'
                    AND dErstellt < '" . $dBis . "'
                    AND nRegistriert = 1", 1
        );

        if (isset($oKunde->nAnzahl) && $oKunde->nAnzahl > 0) {
            return $oKunde->nAnzahl;
        }
    }

    return 0;
}

/**
 * @param string $dVon
 * @param string $dBis
 * @return int
 */
function gibAnzahlNeukundenGekauft($dVon, $dBis)
{
    $dVon = Shop::DB()->escape($dVon);
    $dBis = Shop::DB()->escape($dBis);

    if (strlen($dVon) > 0 && strlen($dBis) > 0) {
        $oKunde = Shop::DB()->query(
            "SELECT count(DISTINCT(tkunde.kKunde)) AS nAnzahl
                FROM tkunde
                JOIN tbestellung ON tbestellung.kKunde = tkunde.kKunde
                WHERE tbestellung.dErstellt >= '" . $dVon . "'
                    AND tbestellung.dErstellt < '" . $dBis . "'
                    AND tkunde.dErstellt >= '" . $dVon . "'
                    AND tkunde.dErstellt < '" . $dBis . "'
                    AND tkunde.nRegistriert = 1", 1
        );

        return (isset($oKunde->nAnzahl) && $oKunde->nAnzahl > 0) ?
            (int)$oKunde->nAnzahl :
            0;
    }

    return 0;
}

/**
 * Holt die Anzahl an Bestellungen für einen bestimmten Zeitraum
 *
 * @param string $dVon
 * @param string $dBis
 * @return int
 */
function gibAnzahlBestellungen($dVon, $dBis)
{
    $dVon = Shop::DB()->escape($dVon);
    $dBis = Shop::DB()->escape($dBis);

    if (strlen($dVon) > 0 && strlen($dBis) > 0) {
        $oBestellung = Shop::DB()->query(
            "SELECT count(*) AS nAnzahl
                FROM tbestellung
                WHERE dErstellt >= '" . $dVon . "'
                    AND dErstellt < '" . $dBis . "'", 1
        );

        return (isset($oBestellung->nAnzahl) && $oBestellung->nAnzahl > 0) ?
            (int)$oBestellung->nAnzahl :
            0;
    }

    return 0;
}

/**
 * Holt die Anzahl an Bestellungen für einen bestimmten Zeitraum von Neukunden
 *
 * @param string $dVon
 * @param string $dBis
 * @return int
 */
function gibAnzahlBestellungenNeukunden($dVon, $dBis)
{
    $dVon = Shop::DB()->escape($dVon);
    $dBis = Shop::DB()->escape($dBis);

    if (strlen($dVon) > 0 && strlen($dBis) > 0) {
        $oBestellung = Shop::DB()->query(
            "SELECT count(*) AS nAnzahl
                FROM tbestellung
                JOIN tkunde ON tkunde.kKunde = tbestellung.kKunde
                WHERE tbestellung.dErstellt >= '" . $dVon . "'
                    AND tbestellung.dErstellt < '" . $dBis . "'
                    AND tkunde.nRegistriert = 1", 1
        );

        return (isset($oBestellung->nAnzahl) && $oBestellung->nAnzahl > 0) ?
            (int)$oBestellung->nAnzahl :
            0;
    }

    return 0;
}

/**
 * Anzahl Zahlungseingänge zu Bestellungen
 *
 * @param string $dVon
 * @param string $dBis
 * @return int
 */
function gibAnzahlZahlungseingaengeVonBestellungen($dVon, $dBis)
{
    $dVon = Shop::DB()->escape($dVon);
    $dBis = Shop::DB()->escape($dBis);

    if (strlen($dVon) > 0 && strlen($dBis) > 0) {
        $oBestellung = Shop::DB()->query(
            "SELECT count(*) AS nAnzahl
                FROM tbestellung
                WHERE tbestellung.dErstellt >= '" . $dVon . "'
                    AND tbestellung.dErstellt < '" . $dBis . "'
                    AND tbestellung.dBezahltDatum != '0000-00-00'", 1
        );

        return (isset($oBestellung->nAnzahl) && $oBestellung->nAnzahl > 0) ?
            (int)$oBestellung->nAnzahl :
            0;
    }

    return 0;
}

/**
 * Anzahl versendeter Bestellungen
 *
 * @param string $dVon
 * @param string $dBis
 * @return int
 */
function gibAnzahlVersendeterBestellungen($dVon, $dBis)
{
    $dVon = Shop::DB()->escape($dVon);
    $dBis = Shop::DB()->escape($dBis);

    if (strlen($dVon) > 0 && strlen($dBis) > 0) {
        $oBestellung = Shop::DB()->query(
            "SELECT count(*) AS nAnzahl
                FROM tbestellung
                WHERE tbestellung.dErstellt >= '" . $dVon . "'
                    AND tbestellung.dErstellt < '" . $dBis . "'
                    AND tbestellung.dVersandDatum != '0000-00-00'", 1
        );

        return (isset($oBestellung->nAnzahl) && $oBestellung->nAnzahl > 0) ?
            (int)$oBestellung->nAnzahl :
            0;
    }

    return 0;
}

/**
 * Holt die Anzahl von Besucher für einen bestimmten Zeitraum
 *
 * @param string $dVon
 * @param string $dBis
 * @return int
 */
function gibAnzahlBesucher($dVon, $dBis)
{
    $dVon = Shop::DB()->escape($dVon);
    $dBis = Shop::DB()->escape($dBis);

    if (strlen($dVon) > 0 && strlen($dBis) > 0) {
        $oBesucher = Shop::DB()->query(
            "SELECT count(*) AS nAnzahl
                FROM tbesucherarchiv
                WHERE dZeit >= '" . $dVon . "'
                    AND dZeit < '" . $dBis . "' AND kBesucherBot = 0", 1
        );

        return (isset($oBesucher->nAnzahl) && $oBesucher->nAnzahl > 0) ?
            (int)$oBesucher->nAnzahl :
            0;
    }

    return 0;
}

/**
 * Holt die Anzahl von Besucher für einen bestimmten Zeitraum die von Suchmaschinen kamen
 *
 * @param string $dVon
 * @param string $dBis
 * @return int
 */
function gibAnzahlBesucherSuchmaschine($dVon, $dBis)
{
    $dVon = Shop::DB()->escape($dVon);
    $dBis = Shop::DB()->escape($dBis);

    if (strlen($dVon) > 0 && strlen($dBis) > 0) {
        $oBesucher = Shop::DB()->query(
            "SELECT count(*) AS nAnzahl
                FROM tbesucherarchiv
                WHERE dZeit >= '" . $dVon . "'
                    AND dZeit < '" . $dBis . "'
                    AND cReferer != ''", 1
        );

        return (isset($oBesucher->nAnzahl) && $oBesucher->nAnzahl > 0) ?
            (int)$oBesucher->nAnzahl :
            0;
    }

    return 0;
}

/**
 * Holt die Anzahl von Bewertungen für einen bestimmten Zeitraum
 *
 * @param string $dVon
 * @param string $dBis
 * @return int
 */
function gibAnzahlBewertungen($dVon, $dBis)
{
    $dVon = Shop::DB()->escape($dVon);
    $dBis = Shop::DB()->escape($dBis);

    if (strlen($dVon) > 0 && strlen($dBis) > 0) {
        $oBewertung = Shop::DB()->query(
            "SELECT count(*) AS nAnzahl
                FROM tbewertung
                WHERE dDatum >= '" . $dVon . "'
                    AND dDatum < '" . $dBis . "'
                    AND nAktiv = 1", 1
        );

        return (isset($oBewertung->nAnzahl) && $oBewertung->nAnzahl > 0) ?
            (int)$oBewertung->nAnzahl :
            0;
    }

    return 0;
}

/**
 * Holt die Anzahl von Bewertungen für einen bestimmten Zeitraum die nicht freigeschaltet wurden
 *
 * @param string $dVon
 * @param string $dBis
 * @return int
 */
function gibAnzahlBewertungenNichtFreigeschaltet($dVon, $dBis)
{
    $dVon = Shop::DB()->escape($dVon);
    $dBis = Shop::DB()->escape($dBis);

    if (strlen($dVon) > 0 && strlen($dBis) > 0) {
        $oBewertung = Shop::DB()->query(
            "SELECT count(*) AS nAnzahl
                FROM tbewertung
                WHERE dDatum >= '" . $dVon . "'
                    AND dDatum < '" . $dBis . "'
                    AND nAktiv = 0", 1
        );

        return (isset($oBewertung->nAnzahl) && $oBewertung->nAnzahl > 0) ?
            (int)$oBewertung->nAnzahl :
            0;
    }

    return 0;
}

/**
 * Holt die Anzahl von gezahlten Guthaben für einen bestimmten Zeitraum
 *
 * @param string $dVon
 * @param string $dBis
 * @return mixed
 */
function gibAnzahlGezahltesGuthaben($dVon, $dBis)
{
    $dVon                 = Shop::DB()->escape($dVon);
    $dBis                 = Shop::DB()->escape($dBis);
    $oTMP                 = new stdClass();
    $oTMP->nAnzahl        = 0;
    $oTMP->fSummeGuthaben = 0;

    if (strlen($dVon) > 0 && strlen($dBis) > 0) {
        $oBewertung = Shop::DB()->query(
            "SELECT count(*) AS nAnzahl, sum(fGuthabenBonus) AS fSummeGuthaben
                FROM tbewertungguthabenbonus
                WHERE dDatum >= '" . $dVon . "'
                    AND dDatum < '" . $dBis . "'", 1
        );

        if (isset($oBewertung->nAnzahl) && $oBewertung->nAnzahl > 0) {
            $oTMP                 = new stdClass();
            $oTMP->nAnzahl        = (int)$oBewertung->nAnzahl;
            $oTMP->fSummeGuthaben = $oBewertung->fSummeGuthaben;

            return $oTMP;
        }
    }

    return $oTMP;
}

/**
 * Holt die Anzahl von Tags für einen bestimmten Zeitraum
 *
 * @param string $dVon
 * @param string $dBis
 * @return int
 */
function gibAnzahlTags($dVon, $dBis)
{
    $dVon = Shop::DB()->escape($dVon);
    $dBis = Shop::DB()->escape($dBis);

    if (strlen($dVon) > 0 && strlen($dBis) > 0) {
        $oTag = Shop::DB()->query(
            "SELECT count(*) AS nAnzahl
                FROM ttagkunde
                JOIN ttag ON ttag.kTag = ttagkunde.kTag
                    AND ttag.nAktiv = 1
                WHERE ttagkunde.dZeit >= '" . $dVon . "'
                    AND ttagkunde.dZeit < '" . $dBis . "'", 1
        );

        return (isset($oTag->nAnzahl) && $oTag->nAnzahl > 0) ?
            (int)$oTag->nAnzahl :
            0;
    }

    return 0;
}

/**
 * Holt die Anzahl von Tags für einen bestimmten Zeitraum die nicht freigeschaltet wurden
 *
 * @param string $dVon
 * @param string $dBis
 * @return int
 */
function gibAnzahlTagsNichtFreigeschaltet($dVon, $dBis)
{
    $dVon = Shop::DB()->escape($dVon);
    $dBis = Shop::DB()->escape($dBis);

    if (strlen($dVon) > 0 && strlen($dBis) > 0) {
        $oTag = Shop::DB()->query(
            "SELECT count(*) AS nAnzahl
                FROM ttagkunde
                JOIN ttag ON ttag.kTag = ttagkunde.kTag
                    AND ttag.nAktiv = 0
                WHERE ttagkunde.dZeit >= '" . $dVon . "'
                    AND ttagkunde.dZeit < '" . $dBis . "'", 1
        );

        return (isset($oTag->nAnzahl) && $oTag->nAnzahl > 0) ?
            (int)$oTag->nAnzahl :
            0;
    }

    return 0;
}

/**
 * Holt die Anzahl Kunden die geworben wurden für einen bestimmten Zeitraum
 *
 * @param string $dVon
 * @param string $dBis
 * @return int
 */
function gibAnzahlGeworbenerKunden($dVon, $dBis)
{
    $dVon = Shop::DB()->escape($dVon);
    $dBis = Shop::DB()->escape($dBis);

    if (strlen($dVon) > 0 && strlen($dBis) > 0) {
        $oKwK = Shop::DB()->query(
            "SELECT count(*) AS nAnzahl
                FROM tkundenwerbenkunden
                WHERE dErstellt >= '" . $dVon . "'
                    AND dErstellt < '" . $dBis . "'", 1
        );

        return (isset($oKwK->nAnzahl) && $oKwK->nAnzahl > 0) ?
            (int)$oKwK->nAnzahl :
            0;
    }

    return 0;
}

/**
 * Holt die Anzahl Kunden die erfolgreich geworben wurden für einen bestimmten Zeitraum
 *
 * @param string $dVon
 * @param string $dBis
 * @return int
 */
function gibAnzahlErfolgreichGeworbenerKunden($dVon, $dBis)
{
    $dVon = Shop::DB()->escape($dVon);
    $dBis = Shop::DB()->escape($dBis);

    if (strlen($dVon) > 0 && strlen($dBis) > 0) {
        $oKwK = Shop::DB()->query(
            "SELECT count(*) AS nAnzahl
                FROM tkundenwerbenkunden
                WHERE dErstellt >= '" . $dVon . "'
                    AND dErstellt < '" . $dBis . "'
                    AND nRegistriert = 1
                    AND nGuthabenVergeben = 1", 1
        );

        return (isset($oKwK->nAnzahl) && $oKwK->nAnzahl > 0) ?
            (int)$oKwK->nAnzahl :
            0;
    }

    return 0;
}

/**
 * Holt die Anzahl von versendeten Wunschlisten für einen bestimmten Zeitraum
 *
 * @param string $dVon
 * @param string $dBis
 * @return int
 */
function gibAnzahlVersendeterWunschlisten($dVon, $dBis)
{
    $dVon = Shop::DB()->escape($dVon);
    $dBis = Shop::DB()->escape($dBis);

    if (strlen($dVon) > 0 && strlen($dBis) > 0) {
        $oWunschliste = Shop::DB()->query(
            "SELECT count(*) AS nAnzahl
                FROM twunschlisteversand
                WHERE dZeit >= '" . $dVon . "'
                    AND dZeit < '" . $dBis . "'", 1
        );

        return (isset($oWunschliste->nAnzahl) && $oWunschliste->nAnzahl > 0) ?
            (int)$oWunschliste->nAnzahl :
            0;
    }

    return 0;
}

/**
 * Holt die Anzahl durchgeführter Umfragen für einen bestimmten Zeitraum
 *
 * @param string $dVon
 * @param string $dBis
 * @return int
 */
function gibAnzahlDurchgefuehrteUmfragen($dVon, $dBis)
{
    $dVon = Shop::DB()->escape($dVon);
    $dBis = Shop::DB()->escape($dBis);

    if (strlen($dVon) > 0 && strlen($dBis) > 0) {
        $oUmfrage = Shop::DB()->query(
            "SELECT count(*) AS nAnzahl
                FROM tumfragedurchfuehrung
                WHERE dDurchgefuehrt >= '" . $dVon . "'
                    AND dDurchgefuehrt < '" . $dBis . "'", 1
        );

        return (isset($oUmfrage->nAnzahl) && $oUmfrage->nAnzahl > 0) ?
            (int)$oUmfrage->nAnzahl :
            0;
    }

    return 0;
}

/**
 * Holt die Anzahl an Newskommentare für einen bestimmten Zeitraum
 *
 * @param string $dVon
 * @param string $dBis
 * @return int
 */
function gibAnzahlNewskommentare($dVon, $dBis)
{
    $dVon = Shop::DB()->escape($dVon);
    $dBis = Shop::DB()->escape($dBis);

    if (strlen($dVon) > 0 && strlen($dBis) > 0) {
        $oNewskommentar = Shop::DB()->query(
            "SELECT count(*) AS nAnzahl
                FROM tnewskommentar
                WHERE dErstellt >= '" . $dVon . "'
                    AND dErstellt < '" . $dBis . "'
                    AND nAktiv = 1", 1
        );

        return (isset($oNewskommentar->nAnzahl) && $oNewskommentar->nAnzahl > 0) ?
            $oNewskommentar->nAnzahl :
            0;
    }

    return 0;
}

/**
 * Holt die Anzahl an Newskommentare nicht freigeschaltet für einen bestimmten Zeitraum
 *
 * @param string $dVon
 * @param string $dBis
 * @return int
 */
function gibAnzahlNewskommentareNichtFreigeschaltet($dVon, $dBis)
{
    $dVon = Shop::DB()->escape($dVon);
    $dBis = Shop::DB()->escape($dBis);

    if (strlen($dVon) > 0 && strlen($dBis) > 0) {
        $oNewskommentar = Shop::DB()->query(
            "SELECT count(*) AS nAnzahl
                FROM tnewskommentar
                WHERE dErstellt >= '" . $dVon . "'
                    AND dErstellt < '" . $dBis . "'
                    AND nAktiv = 0", 1
        );

        return (isset($oNewskommentar->nAnzahl) && $oNewskommentar->nAnzahl > 0) ?
            (int)$oNewskommentar->nAnzahl :
            0;
    }

    return 0;
}

/**
 * Holt die Anzahl an Produktanfragen zur Verfügbarkeit für einen bestimmten Zeitraum *
 *
 * @param string $dVon
 * @param string $dBis
 * @return int
 */
function gibAnzahlProduktanfrageVerfuegbarkeit($dVon, $dBis)
{
    $dVon = Shop::DB()->escape($dVon);
    $dBis = Shop::DB()->escape($dBis);

    if (strlen($dVon) > 0 && strlen($dBis) > 0) {
        $oVerfuegbarkeit = Shop::DB()->query(
            "SELECT count(*) AS nAnzahl
                FROM tverfuegbarkeitsbenachrichtigung
                WHERE dErstellt >= '" . $dVon . "'
                    AND dErstellt < '" . $dBis . "'", 1
        );

        return (isset($oVerfuegbarkeit->nAnzahl) && $oVerfuegbarkeit->nAnzahl > 0) ?
            (int)$oVerfuegbarkeit->nAnzahl :
            0;
    }

    return 0;
}

/**
 * Holt die Anzahl an Produktanfragen zum Artikel für einen bestimmten Zeitraum
 *
 * @param string $dVon
 * @param string $dBis
 * @return int
 */
function gibAnzahlProduktanfrageArtikel($dVon, $dBis)
{
    $dVon = Shop::DB()->escape($dVon);
    $dBis = Shop::DB()->escape($dBis);

    if (strlen($dVon) > 0 && strlen($dBis) > 0) {
        $oFrageProdukt = Shop::DB()->query(
            "SELECT count(*) AS nAnzahl
                FROM tproduktanfragehistory
                WHERE dErstellt >= '" . $dVon . "'
                    AND dErstellt < '" . $dBis . "'", 1
        );

        return (isset($oFrageProdukt->nAnzahl) && $oFrageProdukt->nAnzahl > 0) ?
            (int)$oFrageProdukt->nAnzahl :
            0;
    }

    return 0;
}

/**
 * Holt die Anzahl von Vergleichen für einen bestimmten Zeitraum
 *
 * @param string $dVon
 * @param string $dBis
 * @return int
 */
function gibAnzahlVergleiche($dVon, $dBis)
{
    $dVon = Shop::DB()->escape($dVon);
    $dBis = Shop::DB()->escape($dBis);

    if (strlen($dVon) > 0 && strlen($dBis) > 0) {
        $oVergleich = Shop::DB()->query(
            "SELECT count(*) AS nAnzahl
                FROM tvergleichsliste
                WHERE dDate >= '" . $dVon . "'
                    AND dDate < '" . $dBis . "'", 1
        );

        return (isset($oVergleich->nAnzahl) && $oVergleich->nAnzahl > 0) ?
            (int)$oVergleich->nAnzahl :
            0;
    }

    return 0;
}

/**
 * Holt die Anzahl von genutzten Kupons für einen bestimmten Zeitraum
 *
 * @param string $dVon
 * @param string $dBis
 * @return int
 */
function gibAnzahlGenutzteKupons($dVon, $dBis)
{
    $dVon = Shop::DB()->escape($dVon);
    $dBis = Shop::DB()->escape($dBis);

    if (strlen($dVon) > 0 && strlen($dBis) > 0) {
        $oKupon = Shop::DB()->query(
            "SELECT count(*) AS nAnzahl
                FROM tkuponkunde
                WHERE dErstellt >= '" . $dVon . "'
                    AND dErstellt < '" . $dBis . "'", 1
        );

        return (isset($oKupon->nAnzahl) && $oKupon->nAnzahl > 0) ?
            (int)$oKupon->nAnzahl :
            0;
    }

    return 0;
}

/**
 * @param object $oStatusemail
 * @param string $dVon
 * @param string $dBis
 * @return bool
 */
function baueStatusEmail($oStatusemail, $dVon, $dBis)
{
    // Mail Objekt anlegen und vorbelegen (wichtig fürs Template)
    $oMailObjekt                                           = new stdClass();
    $oMailObjekt->mail                                     = new stdClass();
    $oMailObjekt->oAnzahlArtikelProKundengruppe            = -1;
    $oMailObjekt->nAnzahlNeukunden                         = -1;
    $oMailObjekt->nAnzahlNeukundenGekauft                  = -1;
    $oMailObjekt->nAnzahlBestellungen                      = -1;
    $oMailObjekt->nAnzahlBestellungenNeukunden             = -1;
    $oMailObjekt->nAnzahlBesucher                          = -1;
    $oMailObjekt->nAnzahlBesucherSuchmaschine              = -1;
    $oMailObjekt->nAnzahlBewertungen                       = -1;
    $oMailObjekt->nAnzahlBewertungenNichtFreigeschaltet    = -1;
    $oMailObjekt->oAnzahlGezahltesGuthaben                 = -1;
    $oMailObjekt->nAnzahlTags                              = -1;
    $oMailObjekt->nAnzahlTagsNichtFreigeschaltet           = -1;
    $oMailObjekt->nAnzahlGeworbenerKunden                  = -1;
    $oMailObjekt->nAnzahlErfolgreichGeworbenerKunden       = -1;
    $oMailObjekt->nAnzahlVersendeterWunschlisten           = -1;
    $oMailObjekt->nAnzahlDurchgefuehrteUmfragen            = -1;
    $oMailObjekt->nAnzahlNewskommentare                    = -1;
    $oMailObjekt->nAnzahlNewskommentareNichtFreigeschaltet = -1;
    $oMailObjekt->nAnzahlProduktanfrageArtikel             = -1;
    $oMailObjekt->nAnzahlProduktanfrageVerfuegbarkeit      = -1;
    $oMailObjekt->nAnzahlVergleiche                        = -1;
    $oMailObjekt->nAnzahlGenutzteKupons                    = -1;
    $oMailObjekt->nAnzahlZahlungseingaengeVonBestellungen  = -1;
    $oMailObjekt->nAnzahlVersendeterBestellungen           = -1;

    $dVon = Shop::DB()->escape($dVon);
    $dBis = Shop::DB()->escape($dBis);

    if (is_array($oStatusemail->nInhalt_arr) && count($oStatusemail->nInhalt_arr) > 0 && strlen($dVon) > 0 && strlen($dBis) > 0) {
        foreach ($oStatusemail->nInhalt_arr as $nInhalt) {
            switch ($nInhalt) {
                // Anzahl Artikel pro Kundengruppe
                case 1:
                    $oMailObjekt->oAnzahlArtikelProKundengruppe = gibAnzahlArtikelProKundengruppe();
                    break;

                // Anzahl Neukunden
                case 2:
                    $oMailObjekt->nAnzahlNeukunden = gibAnzahlNeukunden($dVon, $dBis);
                    break;

                // Anzahl Neukunden die gekauft haben
                case 3:
                    $oMailObjekt->nAnzahlNeukundenGekauft = gibAnzahlNeukundenGekauft($dVon, $dBis);
                    break;

                // Anzahl Bestellungen
                case 4:
                    $oMailObjekt->nAnzahlBestellungen = gibAnzahlBestellungen($dVon, $dBis);
                    break;

                // Anzahl Bestellungen von Neukunden
                case 5:
                    $oMailObjekt->nAnzahlBestellungenNeukunden = gibAnzahlBestellungenNeukunden($dVon, $dBis);
                    break;

                // Anzahl Besucher
                case 6:
                    $oMailObjekt->nAnzahlBesucher = gibAnzahlBesucher($dVon, $dBis);
                    break;

                // Anzahl Besucher von Suchmaschinen
                case 7:
                    $oMailObjekt->nAnzahlBesucherSuchmaschine = gibAnzahlBesucherSuchmaschine($dVon, $dBis);
                    break;

                // Anzahl Bewertungen
                case 8:
                    $oMailObjekt->nAnzahlBewertungen = gibAnzahlBewertungen($dVon, $dBis);
                    break;

                // Anzahl nicht-freigeschaltete Bewertungen
                case 9:
                    $oMailObjekt->nAnzahlBewertungenNichtFreigeschaltet = gibAnzahlBewertungenNichtFreigeschaltet($dVon, $dBis);
                    break;

                case 10:
                    $oMailObjekt->oAnzahlGezahltesGuthaben = gibAnzahlGezahltesGuthaben($dVon, $dBis);
                    break;

                case 11:
                    $oMailObjekt->nAnzahlTags = gibAnzahlTags($dVon, $dBis);
                    break;

                case 12:
                    $oMailObjekt->nAnzahlTagsNichtFreigeschaltet = gibAnzahlTagsNichtFreigeschaltet($dVon, $dBis);
                    break;

                case 13:
                    $oMailObjekt->nAnzahlGeworbenerKunden = gibAnzahlGeworbenerKunden($dVon, $dBis);
                    break;

                case 14:
                    $oMailObjekt->nAnzahlErfolgreichGeworbenerKunden = gibAnzahlErfolgreichGeworbenerKunden($dVon, $dBis);
                    break;

                case 15:
                    $oMailObjekt->nAnzahlVersendeterWunschlisten = gibAnzahlVersendeterWunschlisten($dVon, $dBis);
                    break;

                case 16:
                    $oMailObjekt->nAnzahlDurchgefuehrteUmfragen = gibAnzahlDurchgefuehrteUmfragen($dVon, $dBis);
                    break;

                case 17:
                    $oMailObjekt->nAnzahlNewskommentare = gibAnzahlNewskommentare($dVon, $dBis);
                    break;

                case 18:
                    $oMailObjekt->nAnzahlNewskommentareNichtFreigeschaltet = gibAnzahlNewskommentareNichtFreigeschaltet($dVon, $dBis);
                    break;

                case 19:
                    $oMailObjekt->nAnzahlProduktanfrageArtikel = gibAnzahlProduktanfrageArtikel($dVon, $dBis);
                    break;

                case 20:
                    $oMailObjekt->nAnzahlProduktanfrageVerfuegbarkeit = gibAnzahlProduktanfrageVerfuegbarkeit($dVon, $dBis);
                    break;

                case 21:
                    $oMailObjekt->nAnzahlVergleiche = gibAnzahlVergleiche($dVon, $dBis);
                    break;

                case 22:
                    $oMailObjekt->nAnzahlGenutzteKupons = gibAnzahlGenutzteKupons($dVon, $dBis);
                    break;

                // Anzahl Zahlungseingänge von Bestellungen
                case 23:
                    $oMailObjekt->nAnzahlZahlungseingaengeVonBestellungen = gibAnzahlZahlungseingaengeVonBestellungen($dVon, $dBis);
                    break;

                // Anzahl versendeter Bestellungen
                case 24:
                    $oMailObjekt->nAnzahlVersendeterBestellungen = gibAnzahlVersendeterBestellungen($dVon, $dBis);
                    break;
            }
        }

        return $oMailObjekt;
    }

    return false;
}

/**
 * @param string $dStamp
 * @return bool
 */
function gibSplitStamp($dStamp)
{
    if (strlen($dStamp) > 0) {
        // DATETIME splitten
        list($dDatum, $dZeit)               = explode(' ', $dStamp);
        list($dJahr, $dMonat, $dTag)        = explode('-', $dDatum);
        list($dStunde, $dMinute, $dSekunde) = explode(':', $dZeit);

        $oZeit           = new stdClass();
        $oZeit->dDatum   = $dDatum;
        $oZeit->dZeit    = $dZeit;
        $oZeit->dJahr    = $dJahr;
        $oZeit->dMonat   = $dMonat;
        $oZeit->dTag     = $dTag;
        $oZeit->dStunde  = $dStunde;
        $oZeit->dMinute  = $dMinute;
        $oZeit->dSekunde = $dSekunde;

        return $oZeit;
    }

    return false;
}

/**
 * @param string $dStamp
 * @param int    $nIntervall
 * @return bool
 */
function pruefeIntervallUeberschritten($dStamp, $nIntervall)
{
    $nIntervall = (int) $nIntervall;
    if (strlen($dStamp) > 0 && $nIntervall > 0) {
        $oDateTime = new DateTime($dStamp);

        switch ($nIntervall) {
            case 1:
                $oDateTime->modify('+1 day');
                break;

            case 7:
                $oDateTime->modify('+1 week');
                break;

            case 30:
                $oDateTime->modify('+1 month');
                break;

            default:
                break;
        }

        return (time() >= intval($oDateTime->format('U')));
    }

    return false;
}
