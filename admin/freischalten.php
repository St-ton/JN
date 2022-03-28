<?php declare(strict_types=1);

use JTL\DB\SqlObject;
use JTL\Helpers\Form;
use JTL\Helpers\GeneralObject;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Pagination\Pagination;
use JTL\Shop;

require_once __DIR__ . '/includes/admininclude.php';
/** @global \JTL\Backend\AdminAccount $oAccount */
/** @global \JTL\Smarty\JTLSmarty $smarty */

$oAccount->permission('UNLOCK_CENTRAL_VIEW', true, true);

require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'freischalten_inc.php';
setzeSprache();

$ratingsSQL    = new SqlObject();
$liveSearchSQL = new SqlObject();
$commentsSQL   = new SqlObject();
$recipientsSQL = new SqlObject();
$liveSearchSQL->setOrder(' dZuletztGesucht DESC ');
$recipientsSQL->setOrder(' tnewsletterempfaenger.dEingetragen DESC');
$tab         = Request::verifyGPDataString('tab');
$alertHelper = Shop::Container()->getAlertService();

if (Request::verifyGPCDataInt('Suche') === 1) {
    $search = Text::filterXSS(Request::verifyGPDataString('cSuche'));

    if (mb_strlen($search) > 0) {
        switch (Request::verifyGPDataString('cSuchTyp')) {
            case 'Bewertung':
                $tab = 'bewertungen';
                $ratingsSQL->setWhere(' AND (tbewertung.cName LIKE :srch
                                            OR tbewertung.cTitel LIKE :srch
                                            OR tartikel.cName LIKE :srch)');
                $ratingsSQL->addParam('srch', '%' . $search . '%');
                break;
            case 'Livesuche':
                $tab = 'livesearch';
                $liveSearchSQL->setWhere(' AND tsuchanfrage.cSuche LIKE :srch');
                $liveSearchSQL->addParam('srch', '%' . $search . '%');
                break;
            case 'Newskommentar':
                $tab = 'newscomments';
                $commentsSQL->setWhere(' AND (tnewskommentar.cKommentar LIKE :srch
                                            OR tkunde.cVorname LIKE :srch
                                            OR tkunde.cNachname LIKE :srch
                                            OR t.title LIKE :srch)');
                $commentsSQL->addParam('srch', '%' . $search . '%');
                break;
            case 'Newsletterempfaenger':
                $tab = 'newsletter';
                $recipientsSQL->setWhere(' AND (tnewsletterempfaenger.cVorname LIKE :srch
                                                OR tnewsletterempfaenger.cNachname LIKE :srch
                                                OR tnewsletterempfaenger.cEmail LIKE :srch)');
                $recipientsSQL->addParam('srch', '%' . $search . '%');
                break;
            default:
                break;
        }

        $smarty->assign('cSuche', $search)
            ->assign('cSuchTyp', Request::verifyGPDataString('cSuchTyp'));
    } else {
        $alertHelper->addError(__('errorSearchTermMissing'), 'errorSearchTermMissing');
    }
}

if (Request::verifyGPCDataInt('nSort') > 0) {
    $smarty->assign('nSort', Request::verifyGPCDataInt('nSort'));

    switch (Request::verifyGPCDataInt('nSort')) {
        case 1:
            $liveSearchSQL->setOrder(' tsuchanfrage.cSuche ASC ');
            break;
        case 11:
            $liveSearchSQL->setOrder(' tsuchanfrage.cSuche DESC ');
            break;
        case 2:
            $liveSearchSQL->setOrder(' tsuchanfrage.nAnzahlGesuche DESC ');
            break;
        case 22:
            $liveSearchSQL->setOrder(' tsuchanfrage.nAnzahlGesuche ASC ');
            break;
        case 3:
            $liveSearchSQL->setOrder(' tsuchanfrage.nAnzahlTreffer DESC ');
            break;
        case 33:
            $liveSearchSQL->setOrder(' tsuchanfrage.nAnzahlTreffer ASC ');
            break;
        case 4:
            $recipientsSQL->setOrder(' tnewsletterempfaenger.dEingetragen DESC ');
            break;
        case 44:
            $recipientsSQL->setOrder(' tnewsletterempfaenger.dEingetragen ASC ');
            break;
        default:
            break;
    }
} else {
    $smarty->assign('nLivesucheSort', -1);
}

if (Request::verifyGPCDataInt('freischalten') === 1 && Form::validateToken()) {
    // Bewertungen
    if (Request::verifyGPCDataInt('bewertungen') === 1) {
        if (isset($_POST['freischaltensubmit'])) {
            if (schalteBewertungFrei(Request::postVar('kBewertung', []))) {
                $alertHelper->addSuccess(__('successRatingUnlock'), 'successRatingUnlock');
            } else {
                $alertHelper->addError(__('errorAtLeastOneRating'), 'errorAtLeastOneRating');
            }
        } elseif (isset($_POST['freischaltenleoschen'])) {
            if (loescheBewertung(Request::postVar('kBewertung', []))) {
                $alertHelper->addSuccess(__('successRatingDelete'), 'successRatingDelete');
            } else {
                $alertHelper->addError(__('errorAtLeastOneRating'), 'errorAtLeastOneRating');
            }
        }
    } elseif (Request::verifyGPCDataInt('suchanfragen') === 1) { // Suchanfragen
        // Mappen
        if (isset($_POST['submitMapping'])) {
            $mapping = Request::verifyGPDataString('cMapping');
            if (mb_strlen($mapping) > 0) {
                $res = 0;
                if (GeneralObject::hasCount('kSuchanfrage', $_POST)) {
                    $res = mappeLiveSuche($_POST['kSuchanfrage'], $mapping);
                    if ($res === 1) { // Alles O.K.
                        if (schalteSuchanfragenFrei(Request::postVar('kSuchanfrage', []))) {
                            $alertHelper->addSuccess(
                                sprintf(__('successLiveSearchMap'), $mapping),
                                'successLiveSearchMap'
                            );
                        } else {
                            $alertHelper->addError(
                                __('errorLiveSearchMapNotUnlock'),
                                'errorLiveSearchMapNotUnlock'
                            );
                        }
                    } else {
                        switch ($res) {
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
                                $searchError = '';
                                break;
                        }
                        $alertHelper->addError($searchError, 'searchError');
                    }
                } else {
                    $alertHelper->addError(__('errorAtLeastOneLiveSearch'), 'errorAtLeastOneLiveSearch');
                }
            } else {
                $alertHelper->addError(__('errorMapNameMissing'), 'errorMapNameMissing');
            }
        }

        if (isset($_POST['freischaltensubmit'])) {
            if (schalteSuchanfragenFrei(Request::postVar('kSuchanfrage', []))) {
                $alertHelper->addSuccess(__('successSearchUnlock'), 'successSearchUnlock');
            } else {
                $alertHelper->addError(__('errorAtLeastOneSearch'), 'errorAtLeastOneSearch');
            }
        } elseif (isset($_POST['freischaltenleoschen'])) {
            if (loescheSuchanfragen(Request::postVar('kSuchanfrage', []))) {
                $alertHelper->addSuccess(__('successSearchDelete'), 'successSearchDelete');
            } else {
                $alertHelper->addError(__('errorAtLeastOneSearch'), 'errorAtLeastOneSearch');
            }
        }
    } elseif (Request::verifyGPCDataInt('newskommentare') === 1 && Form::validateToken()) {
        if (isset($_POST['freischaltensubmit'])) {
            if (schalteNewskommentareFrei(Request::postVar('kNewsKommentar', []))) {
                $alertHelper->addSuccess(__('successNewsCommentUnlock'), 'successNewsCommentUnlock');
            } else {
                $alertHelper->addError(__('errorAtLeastOneNewsComment'), 'errorAtLeastOneNewsComment');
            }
        } elseif (isset($_POST['freischaltenleoschen'])) {
            if (loescheNewskommentare(Request::postVar('kNewsKommentar', []))) {
                $alertHelper->addSuccess(__('successNewsCommentDelete'), 'successNewsCommentDelete');
            } else {
                $alertHelper->addError(__('errorAtLeastOneNewsComment'), 'errorAtLeastOneNewsComment');
            }
        }
    } elseif (Request::verifyGPCDataInt('newsletterempfaenger') === 1 && Form::validateToken()) {
        if (isset($_POST['freischaltensubmit'])) {
            if (schalteNewsletterempfaengerFrei(Request::postVar('kNewsletterEmpfaenger', []))) {
                $alertHelper->addSuccess(__('successNewsletterUnlock'), 'successNewsletterUnlock');
            } else {
                $alertHelper->addError(__('errorAtLeastOneNewsletter'), 'errorAtLeastOneNewsletter');
            }
        } elseif (isset($_POST['freischaltenleoschen'])) {
            if (loescheNewsletterempfaenger(Request::postVar('kNewsletterEmpfaenger', []))) {
                $alertHelper->addSuccess(__('successNewsletterDelete'), 'successNewsletterDelete');
            } else {
                $alertHelper->addError(__('errorAtLeastOneNewsletter'), 'errorAtLeastOneNewsletter');
            }
        }
    }
}
$pagiRatings    = (new Pagination('bewertungen'))
    ->setItemCount(gibMaxBewertungen())
    ->assemble();
$pagiQueries    = (new Pagination('suchanfragen'))
    ->setItemCount(gibMaxSuchanfragen())
    ->assemble();
$pagiComments   = (new Pagination('newskommentare'))
    ->setItemCount(gibMaxNewskommentare())
    ->assemble();
$pagiRecipients = (new Pagination('newsletter'))
    ->setItemCount(gibMaxNewsletterEmpfaenger())
    ->assemble();

$reviews      = gibBewertungFreischalten(' LIMIT ' . $pagiRatings->getLimitSQL(), $ratingsSQL);
$queries      = gibSuchanfrageFreischalten(' LIMIT ' . $pagiQueries->getLimitSQL(), $liveSearchSQL);
$newsComments = gibNewskommentarFreischalten(' LIMIT ' . $pagiComments->getLimitSQL(), $commentsSQL);
$recipients   = gibNewsletterEmpfaengerFreischalten(' LIMIT ' . $pagiRecipients->getLimitSQL(), $recipientsSQL);
$smarty->assign('ratings', $reviews)
    ->assign('searchQueries', $queries)
    ->assign('comments', $newsComments)
    ->assign('recipients', $recipients)
    ->assign('oPagiBewertungen', $pagiRatings)
    ->assign('oPagiSuchanfragen', $pagiQueries)
    ->assign('oPagiNewskommentare', $pagiComments)
    ->assign('oPagiNewsletterEmpfaenger', $pagiRecipients)
    ->assign('step', 'freischalten_uebersicht')
    ->assign('cTab', $tab)
    ->display('freischalten.tpl');
