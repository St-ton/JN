<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Shop;
use JTL\Sprache;
use JTL\Helpers\Text;
use JTL\Pagination\Pagination;
use JTL\Alert\Alert;

require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('UNLOCK_CENTRAL_VIEW', true, true);

require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'freischalten_inc.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'bewertung_inc.php';
/** @global \JTL\Smarty\JTLSmarty $smarty */
setzeSprache();

$step                  = 'freischalten_uebersicht';
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
$alertHelper           = Shop::Container()->getAlertService();

if (Request::verifyGPCDataInt('Suche') === 1) {
    $search = Shop::Container()->getDB()->escape(Text::filterXSS(Request::verifyGPDataString('cSuche')));

    if (mb_strlen($search) > 0) {
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
        $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorSearchTermMissing'), 'errorSearchTermMissing');
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
            if (schalteBewertungFrei($_POST['kBewertung'])) {
                $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successRatingUnlock'), 'successRatingUnlock');
            } else {
                $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorAtLeastOneRating'), 'errorAtLeastOneRating');
            }
        } elseif (isset($_POST['freischaltenleoschen'])) {
            if (loescheBewertung($_POST['kBewertung'])) {
                $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successRatingDelete'), 'successRatingDelete');
            } else {
                $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorAtLeastOneRating'), 'errorAtLeastOneRating');
            }
        }
    } elseif (Request::verifyGPCDataInt('suchanfragen') === 1) { // Suchanfragen
        // Mappen
        if (isset($_POST['submitMapping'])) {
            $cMapping = Request::verifyGPDataString('cMapping');
            if (mb_strlen($cMapping) > 0) {
                $nReturnValue = 0;
                if (is_array($_POST['kSuchanfrage']) && count($_POST['kSuchanfrage']) > 0) {
                    $nReturnValue = mappeLiveSuche($_POST['kSuchanfrage'], $cMapping);

                    if ($nReturnValue === 1) { // Alles O.K.
                        if (schalteSuchanfragenFrei($_POST['kSuchanfrage'])) {
                            $alertHelper->addAlert(
                                Alert::TYPE_SUCCESS,
                                sprintf(__('successLiveSearchMap'), $cMapping),
                                'successLiveSearchMap'
                            );
                        } else {
                            $alertHelper->addAlert(
                                Alert::TYPE_ERROR,
                                __('errorLiveSearchMapNotUnlock'),
                                'errorLiveSearchMapNotUnlock'
                            );
                        }
                    } else {
                        switch ($nReturnValue) {
                            case 2:
                                $searchError = __('errorMapUnknown');
                                break;
                            case 3:
                                $searchError = __('errorSearchNotFoundDB');
                                break;
                            case 4:
                                $searchError = __('errorMapDB');
                                break;
                            case 5:
                                $searchError = __('errorMapToNotExisting');
                                break;
                            case 6:
                                $searchError = __('errorMapSelf');
                                break;
                            default:
                                break;
                        }
                        $alertHelper->addAlert(Alert::TYPE_ERROR, $searchError, 'searchError');
                    }
                } else {
                    $alertHelper->addAlert(
                        Alert::TYPE_ERROR,
                        __('errorAtLeastOneLiveSearch'),
                        'errorAtLeastOneLiveSearch'
                    );
                }
            } else {
                $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorMapNameMissing'), 'errorMapNameMissing');
            }
        }

        if (isset($_POST['freischaltensubmit'])) {
            if (isset($_POST['kSuchanfrage']) && schalteSuchanfragenFrei($_POST['kSuchanfrage'])) {
                $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successSearchUnlock'), 'successSearchUnlock');
            } else {
                $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorAtLeastOneSearch'), 'errorAtLeastOneSearch');
            }
        } elseif (isset($_POST['freischaltenleoschen'])) {
            if (isset($_POST['kSuchanfrage']) && loescheSuchanfragen($_POST['kSuchanfrage'])) {
                $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successSearchDelete'), 'successSearchDelete');
            } else {
                $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorAtLeastOneSearch'), 'errorAtLeastOneSearch');
            }
        }
    } elseif (Request::verifyGPCDataInt('tags') === 1 && Form::validateToken()) { // Tags
        if (isset($_POST['freischaltensubmit'])) {
            if (isset($_POST['kTag']) && schalteTagsFrei($_POST['kTag'])) {
                $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successTagUnlock'), 'successTagUnlock');
            } else {
                $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorAtLeastOneTag'), 'errorAtLeastOneTag');
            }
        } elseif (isset($_POST['freischaltenleoschen'])) {
            if (isset($_POST['kTag']) && loescheTags($_POST['kTag'])) {
                $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successTagDelete'), 'successTagDelete');
            } else {
                $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorAtLeastOneTag'), 'errorAtLeastOneTag');
            }
        }
    } elseif (Request::verifyGPCDataInt('newskommentare') === 1 && Form::validateToken()) {
        if (isset($_POST['freischaltensubmit'])) {
            if (isset($_POST['kNewsKommentar']) && schalteNewskommentareFrei($_POST['kNewsKommentar'])) {
                $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successNewsCommentUnlock'), 'successNewsCommentUnlock');
            } else {
                $alertHelper->addAlert(
                    Alert::TYPE_ERROR,
                    __('errorAtLeastOneNewsComment'),
                    'errorAtLeastOneNewsComment'
                );
            }
        } elseif (isset($_POST['freischaltenleoschen'])) {
            if (isset($_POST['kNewsKommentar']) && loescheNewskommentare($_POST['kNewsKommentar'])) {
                $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successNewsCommentDelete'), 'successNewsCommentDelete');
            } else {
                $alertHelper->addAlert(
                    Alert::TYPE_ERROR,
                    __('errorAtLeastOneNewsComment'),
                    'errorAtLeastOneNewsComment'
                );
            }
        }
    } elseif (Request::verifyGPCDataInt('newsletterempfaenger') === 1 && Form::validateToken()) {
        if (isset($_POST['freischaltensubmit'])) {
            if (isset($_POST['kNewsletterEmpfaenger'])
                && schalteNewsletterempfaengerFrei($_POST['kNewsletterEmpfaenger'])
            ) {
                $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successNewsletterUnlock'), 'successNewsletterUnlock');
            } else {
                $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorAtLeastOneNewsletter'), 'errorAtLeastOneNewsletter');
            }
        } elseif (isset($_POST['freischaltenleoschen'])) {
            if (isset($_POST['kNewsletterEmpfaenger'])
                && loescheNewsletterempfaenger($_POST['kNewsletterEmpfaenger'])
            ) {
                $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successNewsletterDelete'), 'successNewsletterDelete');
            } else {
                $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorAtLeastOneNewsletter'), 'errorAtLeastOneNewsletter');
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

$smarty->assign('step', $step)
       ->assign('Sprachen', Sprache::getAllLanguages())
       ->assign('cTab', $tab)
       ->display('freischalten.tpl');
