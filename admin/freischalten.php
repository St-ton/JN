<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once dirname(__FILE__) . '/includes/admininclude.php';

$oAccount->permission('UNLOCK_CENTRAL_VIEW', true, true);

require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'freischalten_inc.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'blaetternavi.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'bewertung_inc.php';
require_once PFAD_ROOT . PFAD_DBES . 'seo.php';

$nAnzahlProSeite = 15;
$cSQL1           = ' LIMIT ' . $nAnzahlProSeite;
$cSQL2           = ' LIMIT ' . $nAnzahlProSeite;
$cSQL3           = ' LIMIT ' . $nAnzahlProSeite;
$cSQL4           = ' LIMIT ' . $nAnzahlProSeite;
$cSQL5           = ' LIMIT ' . $nAnzahlProSeite;
$nAktuelleSeite1 = 1;
$nAktuelleSeite2 = 1;
$nAktuelleSeite3 = 1;
$nAktuelleSeite4 = 1;
$nAktuelleSeite5 = 1;
if (verifyGPCDataInteger('s1') > 0) {
    $s1              = verifyGPCDataInteger('s1');
    $cSQL1           = " LIMIT " . (($s1 - 1) * $nAnzahlProSeite) . ", " . $nAnzahlProSeite;
    $nAktuelleSeite1 = $s1;
} elseif (verifyGPCDataInteger('s2') > 0) {
    $s2              = verifyGPCDataInteger('s2');
    $cSQL2           = " LIMIT " . (($s2 - 1) * $nAnzahlProSeite) . ", " . $nAnzahlProSeite;
    $nAktuelleSeite2 = $s2;
} elseif (verifyGPCDataInteger('s3') > 0) {
    $s3              = verifyGPCDataInteger('s3');
    $cSQL3           = " LIMIT " . (($s3 - 1) * $nAnzahlProSeite) . ", " . $nAnzahlProSeite;
    $nAktuelleSeite3 = $s3;
} elseif (verifyGPCDataInteger('s4') > 0) {
    $s4              = verifyGPCDataInteger('s4');
    $cSQL4           = " LIMIT " . (($s4 - 1) * $nAnzahlProSeite) . ", " . $nAnzahlProSeite;
    $nAktuelleSeite4 = $s4;
} elseif (verifyGPCDataInteger('s5') > 0) {
    $s5              = verifyGPCDataInteger('s5');
    $cSQL5           = " LIMIT " . (($s5 - 1) * $nAnzahlProSeite) . ", " . $nAnzahlProSeite;
    $nAktuelleSeite5 = $s5;
}

setzeSprache();

$cHinweis = '';
$cFehler  = '';
$step     = 'freischalten_uebersicht';

$Einstellungen = Shop::getSettings(array(CONF_BEWERTUNG));

// Suche
if (!isset($cBewertungSQL)) {
    $cBewertungSQL = new stdClass();
}
if (!isset($cLivesucheSQL)) {
    $cLivesucheSQL = new stdClass();
}
if (!isset($cTagSQL)) {
    $cTagSQL = new stdClass();
}
if (!isset($cNewskommentarSQL)) {
    $cNewskommentarSQL = new stdClass();
}
if (!isset($cNewsletterempfaengerSQL)) {
    $cNewsletterempfaengerSQL = new stdClass();
}
$cBewertungSQL->cWhere            = '';
$cLivesucheSQL->cWhere            = '';
$cLivesucheSQL->cOrder            = ' dZuletztGesucht DESC ';
$cTagSQL->cWhere                  = '';
$cNewskommentarSQL->cWhere        = '';
$cNewsletterempfaengerSQL->cWhere = '';
$cNewsletterempfaengerSQL->cOrder = ' tnewsletterempfaenger.dEingetragen DESC';
if (verifyGPCDataInteger('Suche') === 1) {
    $cSuche = Shop::DB()->escape(StringHandler::filterXSS(verifyGPDataString('cSuche')));

    if (strlen($cSuche) > 0) {
        switch (verifyGPDataString('cSuchTyp')) {
            case 'Bewertung':
                $cBewertungSQL->cWhere = " AND (tbewertung.cName LIKE '%" . $cSuche . "%'
                                            OR tbewertung.cTitel LIKE '%" . $cSuche . "%'
                                            OR tartikel.cName LIKE '%" . $cSuche . "%')";
                break;
            case 'Livesuche':
                $cLivesucheSQL->cWhere = " AND tsuchanfrage.cSuche LIKE '%" . $cSuche . "%'";
                break;
            case 'Tag':
                $cTagSQL->cWhere = " AND (ttag.cName LIKE '%" . $cSuche . "%'
                                        OR tartikel.cName LIKE '%" . $cSuche . "%')";
                break;
            case 'Newskommentar':
                $cNewskommentarSQL->cWhere = " AND (tnewskommentar.cKommentar LIKE '%" . $cSuche . "%'
                                                OR tkunde.cVorname LIKE '%" . $cSuche . "%'
                                                OR tkunde.cNachname LIKE '%" . $cSuche . "%'
                                                OR tnews.cBetreff LIKE '%" . $cSuche . "%')";
                break;
            case 'Newsletterempfaenger':
                $cNewsletterempfaengerSQL->cWhere = " AND (tnewsletterempfaenger.cVorname LIKE '%" . $cSuche . "%'
                                                        OR tnewsletterempfaenger.cNachname LIKE '%" . $cSuche . "%'
                                                        OR tnewsletterempfaenger.cEmail LIKE '%" . $cSuche . "%')";
                break;
            default:
                break;
        }

        $smarty->assign('cSuche', $cSuche)
               ->assign('cSuchTyp', verifyGPDataString('cSuchTyp'));
    } else {
        $cFehler = 'Fehler: Bitte geben Sie einen Suchbegriff ein.';
    }
}

if (verifyGPCDataInteger('nSort') > 0) {
    $smarty->assign('nSort', verifyGPCDataInteger('nSort'));

    switch (verifyGPCDataInteger('nSort')) {
        case 1:
            $cLivesucheSQL->cOrder = ' tsuchanfrage.cSuche ASC ';
            break;
        case 11:
            $cLivesucheSQL->cOrder = ' tsuchanfrage.cSuche DESC ';
            break;
        case 2:
            $cLivesucheSQL->cOrder = ' tsuchanfrage.nAnzahlGesuche DESC ';
            break;
        case 22:
            $cLivesucheSQL->cOrder = ' tsuchanfrage.nAnzahlGesuche ASC ';
            break;
        case 3:
            $cLivesucheSQL->cOrder = ' tsuchanfrage.nAnzahlTreffer DESC ';
            break;
        case 33:
            $cLivesucheSQL->cOrder = ' tsuchanfrage.nAnzahlTreffer ASC ';
            break;
        case 4:
            $cNewsletterempfaengerSQL->cOrder = ' tnewsletterempfaenger.dEingetragen DESC ';
            break;
        case 44:
            $cNewsletterempfaengerSQL->cOrder = ' tnewsletterempfaenger.dEingetragen ASC ';
            break;
        default:
            break;
    }
} else {
    $smarty->assign('nLivesucheSort', -1);
}

// Freischalten
if (verifyGPCDataInteger('freischalten') === 1 && validateToken()) {
    // Bewertungen
    if (verifyGPCDataInteger('bewertungen') === 1 && validateToken()) {
        if (isset($_POST['freischaltensubmit'])) {
            if (schalteBewertungFrei($_POST['kBewertung'], $_POST['kArtikel'], $_POST['kBewertungAll'])) {
                $cHinweis .= 'Ihre markierten Bewertungen wurden erfolgreich freigeschaltet.<br />';
            } else {
                $cFehler .= 'Fehler: Bitte markieren Sie mindestens eine Bewertung.<br />';
            }
        } elseif (isset($_POST['freischaltenleoschen'])) {
            if (loescheBewertung($_POST['kBewertung'])) {
                $cHinweis .= 'Ihre markierten Bewertungen wurden erfolgreich gel&ouml;scht.<br />';
            } else {
                $cFehler .= 'Fehler: Bitte markieren Sie mindestens eine Bewertung.<br />';
            }
        }
    } elseif (verifyGPCDataInteger('suchanfragen') === 1 && validateToken()) { // Suchanfragen
        // Mappen
        if (verifyGPCDataInteger('nMapping') === 1 && isset($_POST['submitMapping'])) {
            $cMapping = verifyGPDataString('cMapping');
            if (strlen($cMapping) > 0) {
                $nReturnValue = 0;
                if (is_array($_POST['kSuchanfrage']) && count($_POST['kSuchanfrage']) > 0) {
                    $nReturnValue = mappeLiveSuche($_POST['kSuchanfrage'], $cMapping); // Mappen

                    if ($nReturnValue == 1) { // Alles O.K.
                        if (schalteSuchanfragenFrei($_POST['kSuchanfrage'])) {
                            // Freischalten

                            $cHinweis = 'Ihre markierten Livesuchen wurden erfolgreich auf "' . $cMapping . '" gemappt.';
                        } else {
                            $cFehler = 'Fehler: Ihre Livesuche wurde zwar erfolgreich gemappt, konnte jedoch aufgrund eines unbekannten Fehlers, nicht freigeschaltet werden.';
                        }
                    } else {
                        switch ($nReturnValue) {
                            case 2:
                                $cFehler = 'Fehler: Mapping konnte aufgrund eines unbekannten Fehlers nicht durchgef&uuml;hrt werden.';
                                break;
                            case 3:
                                $cFehler = 'Fehler: Mindestens eine Suchanfrage wurde nicht in der Datenbank gefunden.';
                                break;
                            case 4:
                                $cFehler = 'Fehler: Mindestens eine Suchanfrage konnte nicht als Mapping in die Datenbank gespeichert werden.';
                                break;
                            case 5:
                                $cFehler = 'Fehler: Sie haben versucht auf eine nicht existierende Suchanfrage zu mappen.';
                                break;
                            case 6:
                                $cFehler = 'Fehler: Es kann nicht auf sich selbst gemappt werden.';
                                break;
                            default:
                                break;
                        }
                    }
                } else {
                    $cFehler = 'Fehler: Bitte markieren Sie mindestens eine Livesuche.';
                }
            } else {
                $cFehler = 'Fehler: Bitte geben Sie ein Mappingnamen an.';
            }
        }

        if (isset($_POST['freischaltensubmit'])) {
            if (schalteSuchanfragenFrei($_POST['kSuchanfrage'])) {
                $cHinweis .= 'Ihre markierten Suchanfragen wurden erfolgreich freigeschaltet.<br />';
            } else {
                $cFehler .= 'Fehler: Bitte markieren Sie mindestens eine Suchanfrage.<br />';
            }
        } elseif (isset($_POST['freischaltenleoschen'])) {
            if (loescheSuchanfragen($_POST['kSuchanfrage'])) {
                $cHinweis .= 'Ihre markierten Suchanfragen wurden erfolgreich gel&ouml;scht.<br />';
            } else {
                $cFehler .= 'Fehler: Bitte markieren Sie mindestens eine Suchanfrage.<br />';
            }
        }
    } elseif (verifyGPCDataInteger('tags') === 1 && validateToken()) { // Tags
        if (isset($_POST['freischaltensubmit'])) {
            if (schalteTagsFrei($_POST['kTag'])) {
                $cHinweis .= 'Ihre markierten Tags wurden erfolgreich freigeschaltet.<br />';
            } else {
                $cFehler .= 'Fehler: Bitte markieren Sie mindestens einen Tag.<br />';
            }
        } elseif (isset($_POST['freischaltenleoschen'])) {
            if (loescheTags($_POST['kTag'])) {
                $cHinweis .= 'Ihre markierten Tags wurden erfolgreich gel&ouml;scht.<br />';
            } else {
                $cFehler .= 'Fehler: Bitte markieren Sie mindestens einen Tag.<br />';
            }
        }
    } elseif (verifyGPCDataInteger('newskommentare') === 1 && validateToken()) { // Newskommentare
        if (isset($_POST['freischaltensubmit'])) {
            if (schalteNewskommentareFrei($_POST['kNewsKommentar'])) {
                $cHinweis .= 'Ihre markierten Newskommentare wurden erfolgreich freigeschaltet.<br />';
            } else {
                $cFehler .= 'Fehler: Bitte markieren Sie mindestens einen Newskommentar.<br />';
            }
        } elseif (isset($_POST['freischaltenleoschen'])) {
            if (loescheNewskommentare($_POST['kNewsKommentar'])) {
                $cHinweis .= 'Ihre markierten Newskommentare wurden erfolgreich gel&ouml;scht.<br />';
            } else {
                $cFehler .= 'Fehler: Bitte markieren Sie mindestens einen Newskommentar.<br />';
            }
        }
    } elseif (verifyGPCDataInteger('newsletterempfaenger') === 1 && validateToken()) { // Newsletterempfaenger
        if (isset($_POST['freischaltensubmit'])) {
            if (schalteNewsletterempfaengerFrei($_POST['kNewsletterEmpfaenger'])) {
                $cHinweis .= 'Ihre markierten Newsletterempf&auml;nger wurden erfolgreich freigeschaltet.<br />';
            } else {
                $cFehler .= 'Fehler: Bitte markieren Sie mindestens einen Newsletterempf&auml;nger.<br />';
            }
        } elseif (isset($_POST['freischaltenleoschen'])) {
            if (loescheNewsletterempfaenger($_POST['kNewsletterEmpfaenger'])) {
                $cHinweis .= 'Ihre markierten Newsletterempf&auml;nger wurden erfolgreich gel&ouml;scht.<br />';
            } else {
                $cFehler .= 'Fehler: Bitte markieren Sie mindestens einen Newsletterempf&auml;nger.<br />';
            }
        }
    }
}

if ($step === 'freischalten_uebersicht') {
    $smarty->assign('oBewertung_arr', gibBewertungFreischalten($cSQL1, $cBewertungSQL))
           ->assign('oSuchanfrage_arr', gibSuchanfrageFreischalten($cSQL2, $cLivesucheSQL))
           ->assign('oTag_arr', gibTagFreischalten($cSQL3, $cTagSQL))
           ->assign('oNewsKommentar_arr', gibNewskommentarFreischalten($cSQL4, $cNewskommentarSQL))
           ->assign('oNewsletterEmpfaenger_arr', gibNewsletterEmpfaengerFreischalten($cSQL5, $cNewsletterempfaengerSQL))
           ->assign('oBlaetterNaviBewertungen', baueBlaetterNavi($nAktuelleSeite1, gibMaxBewertungen(), $nAnzahlProSeite))
           ->assign('oBlaetterNaviSuchanfrage', baueBlaetterNavi($nAktuelleSeite2, gibMaxSuchanfragen(), $nAnzahlProSeite))
           ->assign('oBlaetterNaviTag', baueBlaetterNavi($nAktuelleSeite3, gibMaxTags(), $nAnzahlProSeite))
           ->assign('oBlaetterNaviNewsKommentar', baueBlaetterNavi($nAktuelleSeite4, gibMaxNewskommentare(), $nAnzahlProSeite))
           ->assign('oBlaetterNaviNewsletterEmpfaenger', baueBlaetterNavi($nAktuelleSeite5, gibMaxNewsletterEmpfaenger(), $nAnzahlProSeite));
}

$smarty->assign('hinweis', $cHinweis)
       ->assign('fehler', $cFehler)
       ->assign('step', $step)
       ->assign('Sprachen', gibAlleSprachen())
       ->display('freischalten.tpl');
