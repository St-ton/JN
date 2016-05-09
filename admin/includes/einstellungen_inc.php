<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * @param string $cSuche
 * @param bool   $bSpeichern
 * @return mixed
 */
function bearbeiteEinstellungsSuche($cSuche, $bSpeichern = false)
{
    $cSuche                 = StringHandler::filterXSS($cSuche);
    $oSQL                   = new stdClass();
    $oSQL->cSearch          = '';
    $oSQL->cWHERE           = '';
    $oSQL->nSuchModus       = 0;
    $oSQL->cSuche           = $cSuche;
    $oSQL->oEinstellung_arr = array();

    if (strlen($cSuche) > 0) {
        //Einstellungen die zu den Exportformaten gehören nicht holen
        $oSQL->cWHERE = "AND kEinstellungenSektion != 101 ";
        // Einstellungen Kommagetrennt?
        $kEinstellungenConf_arr = explode(',', $cSuche);

        $bKommagetrennt = false;
        if (is_array($kEinstellungenConf_arr) && count($kEinstellungenConf_arr) > 1) {
            $bKommagetrennt = true;
            foreach ($kEinstellungenConf_arr as $i => $kEinstellungenConf) {
                if (intval($kEinstellungenConf) === 0) {
                    $bKommagetrennt = false;
                }
            }
        }

        if ($bKommagetrennt) {
            $oSQL->nSuchModus = 1;
            $oSQL->cSearch    = "Suche nach ID: ";
            $oSQL->cWHERE .= " AND kEinstellungenConf IN (";
            foreach ($kEinstellungenConf_arr as $i => $kEinstellungenConf) {
                if ($kEinstellungenConf > 0) {
                    if ($i > 0) {
                        $oSQL->cSearch .= ", " . $kEinstellungenConf;
                        $oSQL->cWHERE .= ", " . $kEinstellungenConf;
                    } else {
                        $oSQL->cSearch .= $kEinstellungenConf;
                        $oSQL->cWHERE .= $kEinstellungenConf;
                    }
                }
            }
            $oSQL->cWHERE .= ")";
        } // Range von Einstellungen?
        else {
            $kEinstellungenConf_arr = explode('-', $cSuche);

            $bRange = false;
            if (is_array($kEinstellungenConf_arr) && count($kEinstellungenConf_arr) === 2) {
                $kEinstellungenConf_arr[0] = (int)$kEinstellungenConf_arr[0];
                $kEinstellungenConf_arr[1] = (int)$kEinstellungenConf_arr[1];

                if ($kEinstellungenConf_arr[0] > 0 && $kEinstellungenConf_arr[1] > 0) {
                    $bRange = true;
                }
            }

            if ($bRange) {
                // Suche war eine Range
                $oSQL->nSuchModus = 2;
                $oSQL->cSearch    = "Suche nach ID Range: " . $kEinstellungenConf_arr[0] . " - " . $kEinstellungenConf_arr[1];
                $oSQL->cWHERE .= " AND kEinstellungenConf BETWEEN " . $kEinstellungenConf_arr[0] . " AND " . $kEinstellungenConf_arr[1];
            } // Suche in cName oder kEinstellungenConf suchen
            else {
                if (intval($cSuche) > 0) {
                    $oSQL->nSuchModus = 3;
                    $oSQL->cSearch    = "Suche nach ID: " . $cSuche;
                    $oSQL->cWHERE .= " AND kEinstellungenConf = '" . $cSuche . "'";
                } else {
                    $cSuche    = strtolower($cSuche);
                    $cSucheEnt = StringHandler::htmlentities($cSuche);    // HTML Entities

                    $oSQL->nSuchModus = 4;
                    $oSQL->cSearch    = "Suche nach Name: " . $cSuche;

                    if ($cSuche === $cSucheEnt) {
                        $oSQL->cWHERE .= " AND cName LIKE '%" . $cSuche . "%'";
                    } else {
                        $oSQL->cWHERE .= " AND (cName LIKE '%" . $cSuche . "%' OR cName LIKE '%" . $cSucheEnt . "%')";
                    }
                }
            }
        }
    }

    return holeEinstellungen($oSQL, $bSpeichern);
}

/**
 * @param object $oSQL
 * @param bool   $bSpeichern
 * @return mixed
 */
function holeEinstellungen($oSQL, $bSpeichern)
{
    if (strlen($oSQL->cWHERE) > 0) {
        $oSQL->oEinstellung_arr = Shop::DB()->query(
            "SELECT *
                FROM teinstellungenconf
                WHERE (cModulId IS NULL OR cModulId = '') " . $oSQL->cWHERE . "
                ORDER BY kEinstellungenSektion, nSort", 2
        );

        if (count($oSQL->oEinstellung_arr) > 0) {
            foreach ($oSQL->oEinstellung_arr as $j => $oEinstellung) {
                if ($oSQL->nSuchModus == 3 && $oEinstellung->cConf === 'Y') {
                    $oSQL->oEinstellung_arr = array();
                    $oEinstellungHeadline   = holeEinstellungHeadline($oEinstellung->nSort, $oEinstellung->kEinstellungenSektion);

                    if (isset($oEinstellungHeadline->kEinstellungenConf) && $oEinstellungHeadline->kEinstellungenConf > 0) {
                        $oSQL->oEinstellung_arr[] = $oEinstellungHeadline;
                        $oSQL                     = holeEinstellungAbteil($oSQL, $oEinstellungHeadline->nSort, $oEinstellungHeadline->kEinstellungenSektion);
                    }
                } elseif ($oEinstellung->cConf === 'N') {
                    $oSQL = holeEinstellungAbteil($oSQL, $oEinstellung->nSort, $oEinstellung->kEinstellungenSektion);
                }
            }
        }

        // Aufräumen
        if (count($oSQL->oEinstellung_arr) > 0) {
            $kEinstellungenConf_arr = array();
            foreach ($oSQL->oEinstellung_arr as $i => $oEinstellung) {
                if (isset($oEinstellung->kEinstellungenConf) && $oEinstellung->kEinstellungenConf > 0 && !in_array($oEinstellung->kEinstellungenConf, $kEinstellungenConf_arr)) {
                    $kEinstellungenConf_arr[$i] = $oEinstellung->kEinstellungenConf;
                } else {
                    unset($oSQL->oEinstellung_arr[$i]);
                }

                if ($bSpeichern && $oEinstellung->cConf === 'N') {
                    unset($oSQL->oEinstellung_arr[$i]);
                }
            }
            $oSQL->oEinstellung_arr = sortiereEinstellungen($oSQL->oEinstellung_arr);
        }
    }

    return $oSQL;
}

/**
 * @param object $oSQL
 * @param int    $nSort
 * @param int    $kEinstellungenSektion
 * @return mixed
 */
function holeEinstellungAbteil($oSQL, $nSort, $kEinstellungenSektion)
{
    if (intval($nSort) > 0 && intval($kEinstellungenSektion) > 0) {
        $oEinstellungTMP_arr = Shop::DB()->query(
            "SELECT *
                FROM teinstellungenconf
                WHERE nSort > " . (int)$nSort . "
                    AND kEinstellungenSektion = " . (int)$kEinstellungenSektion . "
                ORDER BY nSort", 2
        );

        if (is_array($oEinstellungTMP_arr) && count($oEinstellungTMP_arr) > 0) {
            foreach ($oEinstellungTMP_arr as $oEinstellungTMP) {
                if ($oEinstellungTMP->cConf !== 'N') {
                    $oSQL->oEinstellung_arr[] = $oEinstellungTMP;
                } else {
                    break;
                }
            }
        }
    }

    return $oSQL;
}

/**
 * @param int $nSort
 * @param int $kEinstellungenSektion
 * @return stdClass
 */
function holeEinstellungHeadline($nSort, $kEinstellungenSektion)
{
    $oEinstellungHeadline  = new stdClass();
    $kEinstellungenSektion = (int)$kEinstellungenSektion;
    if (intval($nSort) > 0 && $kEinstellungenSektion > 0) {
        $oEinstellungTMP_arr = Shop::DB()->query(
            "SELECT *
                FROM teinstellungenconf
                WHERE nSort < " . (int)$nSort . "
                    AND kEinstellungenSektion = " . $kEinstellungenSektion . "
                ORDER BY nSort DESC", 2
        );

        if (is_array($oEinstellungTMP_arr) && count($oEinstellungTMP_arr) > 0) {
            foreach ($oEinstellungTMP_arr as $oEinstellungTMP) {
                if ($oEinstellungTMP->cConf === 'N') {
                    $oEinstellungHeadline                = $oEinstellungTMP;
                    $oEinstellungHeadline->cSektionsPfad = gibEinstellungsSektionsPfad($kEinstellungenSektion);
                    break;
                }
            }
        }
    }

    return $oEinstellungHeadline;
}

/**
 * @param int $kEinstellungenSektion
 * @return string
 */
function gibEinstellungsSektionsPfad($kEinstellungenSektion)
{
    $kEinstellungenSektion = intval($kEinstellungenSektion);
    if ($kEinstellungenSektion >= 100) {
        // Einstellungssektion ist in den Defines
        switch ($kEinstellungenSektion) {
            case CONF_ZAHLUNGSARTEN:
                return 'Storefront-&gt;Zahlungsarten-&gt;&Uuml;bersicht';
                break;
            case CONF_EXPORTFORMATE:
                return 'System-&gt;Export-&gt;Exportformate';
                break;
            case CONF_KONTAKTFORMULAR:
                return 'Storefront-&gt;Formulare-&gt;Kontaktformular';
                break;
            case CONF_SHOPINFO:
                return 'System-&gt;Export-&gt;Exportformate';
                break;
            case CONF_RSS:
                return 'System-&gt;Export-&gt;RSS Feed';
                break;
            case CONF_PREISVERLAUF:
                return 'Storefront-&gt;Artikel-&gt;Preisverlauf';
                break;
            case CONF_VERGLEICHSLISTE:
                return 'Storefront-&gt;Artikel-&gt;Vergleichsliste';
                break;
            case CONF_BEWERTUNG:
                return 'Storefront-&gt;Artikel-&gt;Bewertungen';
                break;
            case CONF_NEWSLETTER:
                return 'System-&gt;E-Mails-&gt;Newsletter';
                break;
            case CONF_KUNDENFELD:
                return 'Storefront-&gt;Formulare-&gt;Eigene Kundenfelder';
                break;
            case CONF_NAVIGATIONSFILTER:
                return 'Storefront-&gt;Suche-&gt;Filter';
                break;
            case CONF_EMAILBLACKLIST:
                return 'System-&gt;E-Mails-&gt;Blacklist';
                break;
            case CONF_METAANGABEN:
                return 'System-&gt;E-Mails-&gt;Globale Einstellungen-&gt;Globale Meta-Angaben';
                break;
            case CONF_NEWS:
                return 'Inhalte-&gt;News';
                break;
            case CONF_SITEMAP:
                return 'System-&gt;Export-&gt;Sitemap';
                break;
            case CONF_UMFRAGE:
                return 'Inhalte-&gt;Umfragen';
                break;
            case CONF_KUNDENWERBENKUNDEN:
                return 'System-&gt;Benutzer- &amp; Kundenverwaltung-&gt;Kunden werben Kunden';
                break;
            case CONF_TRUSTEDSHOPS:
                return 'Storefront-&gt;Kaufabwicklung-&gt;Trusted Shops';
                break;
            case CONF_PREISANZEIGE:
                return 'Storefront-&gt;Artikel-&gt;Preisanzeige';
                break;
            case CONF_SUCHSPECIAL:
                return 'Storefront-&gt;Artikel-&gt;Besondere Produkte';
                break;
        }
    } else {
        // Einstellungssektion in der Datenbank nachschauen
        $oEinstellungsSektion = Shop::DB()->query(
            "SELECT *
                FROM teinstellungensektion
                WHERE kEinstellungenSektion = " . $kEinstellungenSektion, 1
        );

        if (isset($oEinstellungsSektion->kEinstellungenSektion) && $oEinstellungsSektion->kEinstellungenSektion > 0) {
            return 'Einstellungen-&gt;' . $oEinstellungsSektion->cName;
        }
    }
}

/**
 * @param array $oEinstellung_arr
 * @return array
 */
function sortiereEinstellungen($oEinstellung_arr)
{
    if (is_array($oEinstellung_arr) && count($oEinstellung_arr) > 0) {
        $oEinstellungTMP_arr     = array();
        $oEinstellungSektion_arr = array();
        foreach ($oEinstellung_arr as $i => $oEinstellung) {
            if (isset($oEinstellung->kEinstellungenSektion) && $oEinstellung->cConf !== 'N') {
                if (!isset($oEinstellungSektion_arr[$oEinstellung->kEinstellungenSektion])) {
                    $headline = holeEinstellungHeadline($oEinstellung->nSort, $oEinstellung->kEinstellungenSektion);
                    if (isset($headline->kEinstellungenSektion)) {
                        $oEinstellungSektion_arr[$oEinstellung->kEinstellungenSektion] = true;
                        $oEinstellungTMP_arr[]                                         = $headline;
                    }
                }
                $oEinstellungTMP_arr[] = $oEinstellung;
            }
        }
        foreach ($oEinstellungTMP_arr as $key => $value) {
            $kEinstellungenSektion[$key] = $value->kEinstellungenSektion;
            $nSort[$key]                 = $value->nSort;
        }
        array_multisort($kEinstellungenSektion, SORT_ASC, $nSort, SORT_ASC, $oEinstellungTMP_arr);

        return $oEinstellungTMP_arr;
    }

    return false;
}
