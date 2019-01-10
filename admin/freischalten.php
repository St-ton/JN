<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use Helpers\Form;
use Helpers\Request;
use Pagination\Pagination;

require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('UNLOCK_CENTRAL_VIEW', true, true);

require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'freischalten_inc.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'bewertung_inc.php';
require_once PFAD_ROOT . PFAD_DBES . 'seo.php';
/** @global Smarty\JTLSmarty $smarty */
setzeSprache();

$cHinweis = '';
$cFehler  = '';
$step     = 'freischalten_uebersicht';

$Einstellungen = Shop::getSettings([CONF_BEWERTUNG]);

$ratingsSQL            = new stdClass();
$liveSearchSQL         = new stdClass();
$tagsSQL               = new stdClass();
$commentsSQL           = new stdClass();
$recipientsSQL         = new stdClass();
$ratingsSQL->cWhere    = '';
$liveSearchSQL->cWhere = '';
$liveSearchSQL->cOrder = ' dZuletztGesucht DESC ';
$tagsSQL->cWhere       = '';
$commentsSQL->cWhere   = '';
$recipientsSQL->cWhere = '';
$recipientsSQL->cOrder = ' tnewsletterempfaenger.dEingetragen DESC';
$tab                   = Request::verifyGPDataString('tab');

if (Request::verifyGPCDataInt('Suche') === 1) {
    $search = Shop::Container()->getDB()->escape(StringHandler::filterXSS(Request::verifyGPDataString('cSuche')));

    if (strlen($search) > 0) {
        switch (Request::verifyGPDataString('cSuchTyp')) {
            case 'Bewertung':
                $tab                = 'bewertungen';
                $ratingsSQL->cWhere = " AND (tbewertung.cName LIKE '%" . $search . "%'
                                            OR tbewertung.cTitel LIKE '%" . $search . "%'
                                            OR tartikel.cName LIKE '%" . $search . "%')";
                break;
            case 'Livesuche':
                $tab                   = 'livesearch';
                $liveSearchSQL->cWhere = " AND tsuchanfrage.cSuche LIKE '%" . $search . "%'";
                break;
            case 'Tag':
                $tab             = 'tags';
                $tagsSQL->cWhere = " AND (ttag.cName LIKE '%" . $search . "%'
                                        OR tartikel.cName LIKE '%" . $search . "%')";
                break;
            case 'Newskommentar':
                $tab                 = 'newscomments';
                $commentsSQL->cWhere = " AND (tnewskommentar.cKommentar LIKE '%" . $search . "%'
                                                OR tkunde.cVorname LIKE '%" . $search . "%'
                                                OR tkunde.cNachname LIKE '%" . $search . "%'
                                                OR t.title LIKE '%" . $search . "%')";
                break;
            case 'Newsletterempfaenger':
                $tab                   = 'newsletter';
                $recipientsSQL->cWhere = " AND (tnewsletterempfaenger.cVorname LIKE '%" . $search . "%'
                                                        OR tnewsletterempfaenger.cNachname LIKE '%" . $search . "%'
                                                        OR tnewsletterempfaenger.cEmail LIKE '%" . $search . "%')";
                break;
            default:
                break;
        }

        $smarty->assign('cSuche', $search)
               ->assign('cSuchTyp', Request::verifyGPDataString('cSuchTyp'));
    } else {
        $cFehler = 'Fehler: Bitte geben Sie einen Suchbegriff ein.';
    }
}

if (Request::verifyGPCDataInt('nSort') > 0) {
    $smarty->assign('nSort', Request::verifyGPCDataInt('nSort'));

    switch (Request::verifyGPCDataInt('nSort')) {
        case 1:
            $liveSearchSQL->cOrder = ' tsuchanfrage.cSuche ASC ';
            break;
        case 11:
            $liveSearchSQL->cOrder = ' tsuchanfrage.cSuche DESC ';
            break;
        case 2:
            $liveSearchSQL->cOrder = ' tsuchanfrage.nAnzahlGesuche DESC ';
            break;
        case 22:
            $liveSearchSQL->cOrder = ' tsuchanfrage.nAnzahlGesuche ASC ';
            break;
        case 3:
            $liveSearchSQL->cOrder = ' tsuchanfrage.nAnzahlTreffer DESC ';
            break;
        case 33:
            $liveSearchSQL->cOrder = ' tsuchanfrage.nAnzahlTreffer ASC ';
            break;
        case 4:
            $recipientsSQL->cOrder = ' tnewsletterempfaenger.dEingetragen DESC ';
            break;
        case 44:
            $recipientsSQL->cOrder = ' tnewsletterempfaenger.dEingetragen ASC ';
            break;
        default:
            break;
    }
} else {
    $smarty->assign('nLivesucheSort', -1);
}

// Freischalten
if (Request::verifyGPCDataInt('freischalten') === 1 && Form::validateToken()) {
    // Bewertungen
    if (Request::verifyGPCDataInt('bewertungen') === 1) {
        if (isset($_POST['freischaltensubmit'])) {
            if (schalteBewertungFrei($_POST['kBewertung'], $_POST['kArtikel'], $_POST['kBewertungAll'])) {
                $cHinweis .= 'Ihre markierten Bewertungen wurden erfolgreich freigeschaltet.<br />';
            } else {
                $cFehler .= 'Fehler: Bitte markieren Sie mindestens eine Bewertung.<br />';
            }
        } elseif (isset($_POST['freischaltenleoschen'])) {
            if (loescheBewertung($_POST['kBewertung'])) {
                $cHinweis .= 'Ihre markierten Bewertungen wurden erfolgreich gelöscht.<br />';
            } else {
                $cFehler .= 'Fehler: Bitte markieren Sie mindestens eine Bewertung.<br />';
            }
        }
    } elseif (Request::verifyGPCDataInt('suchanfragen') === 1) { // Suchanfragen
        // Mappen
        if (isset($_POST['submitMapping'])) {
            $cMapping = Request::verifyGPDataString('cMapping');
            if (strlen($cMapping) > 0) {
                $nReturnValue = 0;
                if (is_array($_POST['kSuchanfrage']) && count($_POST['kSuchanfrage']) > 0) {
                    $nReturnValue = mappeLiveSuche($_POST['kSuchanfrage'], $cMapping);

                    if ($nReturnValue === 1) { // Alles O.K.
                        if (schalteSuchanfragenFrei($_POST['kSuchanfrage'])) {
                            $cHinweis = 'Ihre markierten Livesuchen wurden erfolgreich auf "' .
                                $cMapping . '" gemappt.';
                        } else {
                            $cFehler = 'Fehler: Ihre Livesuche wurde zwar erfolgreich gemappt, ' .
                                'konnte jedoch aufgrund eines unbekannten Fehlers, nicht freigeschaltet werden.';
                        }
                    } else {
                        switch ($nReturnValue) {
                            case 2:
                                $cFehler = 'Fehler: Mapping konnte aufgrund eines ' .
                                    'unbekannten Fehlers nicht durchgeführt werden.';
                                break;
                            case 3:
                                $cFehler = 'Fehler: Mindestens eine Suchanfrage wurde nicht in der Datenbank gefunden.';
                                break;
                            case 4:
                                $cFehler = 'Fehler: Mindestens eine Suchanfrage konnte nicht ' .
                                    'als Mapping in die Datenbank gespeichert werden.';
                                break;
                            case 5:
                                $cFehler = 'Fehler: Sie haben versucht auf eine ' .
                                    'nicht existierende Suchanfrage zu mappen.';
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
            if (isset($_POST['kSuchanfrage']) && schalteSuchanfragenFrei($_POST['kSuchanfrage'])) {
                $cHinweis .= 'Ihre markierten Suchanfragen wurden erfolgreich freigeschaltet.<br />';
            } else {
                $cFehler .= 'Fehler: Bitte markieren Sie mindestens eine Suchanfrage.<br />';
            }
        } elseif (isset($_POST['freischaltenleoschen'])) {
            if (isset($_POST['kSuchanfrage']) && loescheSuchanfragen($_POST['kSuchanfrage'])) {
                $cHinweis .= 'Ihre markierten Suchanfragen wurden erfolgreich gelöscht.<br />';
            } else {
                $cFehler .= 'Fehler: Bitte markieren Sie mindestens eine Suchanfrage.<br />';
            }
        }
    } elseif (Request::verifyGPCDataInt('tags') === 1 && Form::validateToken()) { // Tags
        if (isset($_POST['freischaltensubmit'])) {
            if (isset($_POST['kTag']) && schalteTagsFrei($_POST['kTag'])) {
                $cHinweis .= 'Ihre markierten Tags wurden erfolgreich freigeschaltet.<br />';
            } else {
                $cFehler .= 'Fehler: Bitte markieren Sie mindestens einen Tag.<br />';
            }
        } elseif (isset($_POST['freischaltenleoschen'])) {
            if (isset($_POST['kTag']) && loescheTags($_POST['kTag'])) {
                $cHinweis .= 'Ihre markierten Tags wurden erfolgreich gelöscht.<br />';
            } else {
                $cFehler .= 'Fehler: Bitte markieren Sie mindestens einen Tag.<br />';
            }
        }
    } elseif (Request::verifyGPCDataInt('newskommentare') === 1 && Form::validateToken()) {
        if (isset($_POST['freischaltensubmit'])) {
            if (isset($_POST['kNewsKommentar']) && schalteNewskommentareFrei($_POST['kNewsKommentar'])) {
                $cHinweis .= 'Ihre markierten Newskommentare wurden erfolgreich freigeschaltet.<br />';
            } else {
                $cFehler .= 'Fehler: Bitte markieren Sie mindestens einen Newskommentar.<br />';
            }
        } elseif (isset($_POST['freischaltenleoschen'])) {
            if (isset($_POST['kNewsKommentar']) && loescheNewskommentare($_POST['kNewsKommentar'])) {
                $cHinweis .= 'Ihre markierten Newskommentare wurden erfolgreich gelöscht.<br />';
            } else {
                $cFehler .= 'Fehler: Bitte markieren Sie mindestens einen Newskommentar.<br />';
            }
        }
    } elseif (Request::verifyGPCDataInt('newsletterempfaenger') === 1 && Form::validateToken()) {
        if (isset($_POST['freischaltensubmit'])) {
            if (isset($_POST['kNewsletterEmpfaenger'])
                && schalteNewsletterempfaengerFrei($_POST['kNewsletterEmpfaenger'])
            ) {
                $cHinweis .= 'Ihre markierten Newsletterempfänger wurden erfolgreich freigeschaltet.<br />';
            } else {
                $cFehler .= 'Fehler: Bitte markieren Sie mindestens einen Newsletterempfänger.<br />';
            }
        } elseif (isset($_POST['freischaltenleoschen'])) {
            if (isset($_POST['kNewsletterEmpfaenger'])
                && loescheNewsletterempfaenger($_POST['kNewsletterEmpfaenger'])
            ) {
                $cHinweis .= 'Ihre markierten Newsletterempfänger wurden erfolgreich gelöscht.<br />';
            } else {
                $cFehler .= 'Fehler: Bitte markieren Sie mindestens einen Newsletterempfänger.<br />';
            }
        }
    }
}

if ($step === 'freischalten_uebersicht') {
    $pagiRatings    = (new Pagination('bewertungen'))
        ->setItemCount(gibMaxBewertungen())
        ->assemble();
    $pagiQueries    = (new Pagination('suchanfragen'))
        ->setItemCount(gibMaxSuchanfragen())
        ->assemble();
    $pagiTags       = (new Pagination('tags'))
        ->setItemCount(gibMaxTags())
        ->assemble();
    $pagiComments   = (new Pagination('newskommentare'))
        ->setItemCount(gibMaxNewskommentare())
        ->assemble();
    $pagiRecipients = (new Pagination('newsletter'))
        ->setItemCount(gibMaxNewsletterEmpfaenger())
        ->assemble();

    $ratings      = gibBewertungFreischalten(' LIMIT ' . $pagiRatings->getLimitSQL(), $ratingsSQL);
    $queries      = gibSuchanfrageFreischalten(' LIMIT ' . $pagiQueries->getLimitSQL(), $liveSearchSQL);
    $tags         = gibTagFreischalten(' LIMIT ' . $pagiTags->getLimitSQL(), $tagsSQL);
    $newsComments = gibNewskommentarFreischalten(' LIMIT ' . $pagiComments->getLimitSQL(), $commentsSQL);
    $recipients   = gibNewsletterEmpfaengerFreischalten(' LIMIT ' . $pagiRecipients->getLimitSQL(), $recipientsSQL);
    $smarty->assign('oBewertung_arr', $ratings)
           ->assign('oSuchanfrage_arr', $queries)
           ->assign('oTag_arr', $tags)
           ->assign('oNewsKommentar_arr', $newsComments)
           ->assign('oNewsletterEmpfaenger_arr', $recipients)
           ->assign('oPagiBewertungen', $pagiRatings)
           ->assign('oPagiSuchanfragen', $pagiQueries)
           ->assign('oPagiTags', $pagiTags)
           ->assign('oPagiNewskommentare', $pagiComments)
           ->assign('oPagiNewsletterEmpfaenger', $pagiRecipients);
}

$smarty->assign('hinweis', $cHinweis)
       ->assign('fehler', $cFehler)
       ->assign('step', $step)
       ->assign('Sprachen', Sprache::getAllLanguages())
       ->assign('cTab', $tab)
       ->display('freischalten.tpl');
