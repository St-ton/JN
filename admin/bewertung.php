<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTL\Alert\Alert;
use JTL\DB\ReturnType;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Pagination\Pagination;
use JTL\Rating\RatingController;
use JTL\Shop;
use JTL\Sprache;

require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('MODULE_VOTESYSTEM_VIEW', true, true);

require_once PFAD_ROOT . PFAD_INCLUDES . 'bewertung_inc.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'bewertung_inc.php';
/** @global \JTL\Smarty\JTLSmarty $smarty */
$step        = 'bewertung_uebersicht';
$cacheTags   = [];
$cache       = Shop::Container()->getCache();
$alertHelper = Shop::Container()->getAlertService();
$db          = Shop::Container()->getDB();
$controller  = new RatingController($db, $cache, $smarty);
$cTab        = mb_strlen(Request::verifyGPDataString('tab')) > 0 ? Request::verifyGPDataString('tab') : 'freischalten';

setzeSprache();
if (Form::validateToken()) {
    if (Request::verifyGPCDataInt('bewertung_editieren') === 1) {
        if ($controller->edit($_POST)) {
            $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successRatingEdit'), 'successRatingEdit');
            if (Request::verifyGPCDataInt('nFZ') === 1) {
                header('Location: freischalten.php');
                exit();
            }
        } else {
            $step = 'bewertung_editieren';
            $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorFillRequired'), 'errorFillRequired');
        }
    } elseif (isset($_POST['einstellungen']) && (int)$_POST['einstellungen'] === 1) {
        if (Request::verifyGPDataString('bewertung_guthaben_nutzen') === 'Y'
            && Request::verifyGPDataString('bewertung_freischalten') !== 'Y'
        ) {
            $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorCreditUnlock'), 'errorCreditUnlock');
        } else {
            $cache->flushTags([CACHING_GROUP_ARTICLE]);
            $alertHelper->addAlert(
                Alert::TYPE_SUCCESS,
                saveAdminSectionSettings(CONF_BEWERTUNG, $_POST),
                'saveConf'
            );
        }
    } elseif (isset($_POST['bewertung_nicht_aktiv']) && (int)$_POST['bewertung_nicht_aktiv'] === 1) {
        if (isset($_POST['aktivieren'])) {
            if (is_array($_POST['kBewertung']) && count($_POST['kBewertung']) > 0) {
                $alertHelper->addAlert(
                    Alert::TYPE_SUCCESS,
                    $controller->activate($_POST['kBewertung']) . __('successRatingUnlock'),
                    'successRatingUnlock'
                );
            }
        } elseif (isset($_POST['loeschen'])) {
            if (is_array($_POST['kBewertung']) && count($_POST['kBewertung']) > 0) {
                $alertHelper->addAlert(
                    Alert::TYPE_SUCCESS,
                    $controller->delete($_POST['kBewertung']) . __('successRatingDelete'),
                    'successRatingDelete'
                );
            }
        }
    } elseif (isset($_POST['bewertung_aktiv']) && (int)$_POST['bewertung_aktiv'] === 1) {
        if (isset($_POST['cArtNr'])) {
            $activeRatings = $db->queryPrepared(
                "SELECT tbewertung.*, DATE_FORMAT(tbewertung.dDatum, '%d.%m.%Y') AS Datum, tartikel.cName AS ArtikelName
                    FROM tbewertung
                    LEFT JOIN tartikel 
                        ON tbewertung.kArtikel = tartikel.kArtikel
                    WHERE tbewertung.kSprache = :lang
                        AND (tartikel.cArtNr LIKE :cartnr
                            OR tartikel.cName LIKE :cartnr)
                    ORDER BY tbewertung.kArtikel, tbewertung.dDatum DESC",
                [
                    'lang'   => (int)$_SESSION['kSprache'],
                    'cartnr' => '%' . $_POST['cArtNr'] . '%'
                ],
                ReturnType::ARRAY_OF_OBJECTS
            );
            $smarty->assign('cArtNr', Text::filterXSS($_POST['cArtNr']));
        }
        if (isset($_POST['loeschen']) && is_array($_POST['kBewertung']) && count($_POST['kBewertung']) > 0) {
            $alertHelper->addAlert(
                Alert::TYPE_SUCCESS,
                $controller->delete($_POST['kBewertung']) . __('successRatingDelete'),
                'successRatingDelete'
            );
        }
    }
}

if ((isset($_GET['a']) && $_GET['a'] === 'editieren') || $step === 'bewertung_editieren') {
    $step = 'bewertung_editieren';
    $smarty->assign('oBewertung', $controller->holeBewertung(Request::verifyGPCDataInt('kBewertung')));
    if (Request::verifyGPCDataInt('nFZ') === 1) {
        $smarty->assign('nFZ', 1);
    }
} elseif ($step === 'bewertung_uebersicht') {
    if (isset($_GET['a']) && $_GET['a'] === 'delreply' && Form::validateToken()) {
        $controller->removeReply(Request::verifyGPCDataInt('kBewertung'));
        $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successRatingCommentDelete'), 'successRatingCommentDelete');
    }
    $totalCount  = (int)$db->query(
        'SELECT COUNT(*) AS nAnzahl
            FROM tbewertung
            WHERE kSprache = ' . (int)$_SESSION['kSprache'] . '
                AND nAktiv = 0',
        ReturnType::SINGLE_OBJECT
    )->nAnzahl;
    $activeCount = (int)$db->query(
        'SELECT COUNT(*) AS nAnzahl
            FROM tbewertung
            WHERE kSprache = ' . (int)$_SESSION['kSprache'] . '
                AND nAktiv = 1',
        ReturnType::SINGLE_OBJECT
    )->nAnzahl;

    $oPagiInaktiv = (new Pagination('inactive'))
        ->setItemCount($totalCount)
        ->assemble();
    $oPageAktiv   = (new Pagination('active'))
        ->setItemCount($activeCount)
        ->assemble();

    $ratings       = $db->query(
        "SELECT tbewertung.*, DATE_FORMAT(tbewertung.dDatum, '%d.%m.%Y') AS Datum, tartikel.cName AS ArtikelName
            FROM tbewertung
            LEFT JOIN tartikel 
                ON tbewertung.kArtikel = tartikel.kArtikel
            WHERE tbewertung.kSprache = " . (int)$_SESSION['kSprache'] . '
                AND tbewertung.nAktiv = 0
            ORDER BY tbewertung.kArtikel, tbewertung.dDatum DESC
            LIMIT ' . $oPagiInaktiv->getLimitSQL(),
        ReturnType::ARRAY_OF_OBJECTS
    );
    $last50ratings = $db->query(
        "SELECT tbewertung.*, DATE_FORMAT(tbewertung.dDatum, '%d.%m.%Y') AS Datum, tartikel.cName AS ArtikelName
            FROM tbewertung
            LEFT JOIN tartikel 
                ON tbewertung.kArtikel = tartikel.kArtikel
            WHERE tbewertung.kSprache = " . (int)$_SESSION['kSprache'] . '
                AND tbewertung.nAktiv = 1
            ORDER BY tbewertung.dDatum DESC
            LIMIT ' . $oPageAktiv->getLimitSQL(),
        ReturnType::ARRAY_OF_OBJECTS
    );

    $smarty->assign('oPagiInaktiv', $oPagiInaktiv)
        ->assign('oPagiAktiv', $oPageAktiv)
        ->assign('oBewertung_arr', $ratings)
        ->assign('oBewertungLetzten50_arr', $last50ratings)
        ->assign('oBewertungAktiv_arr', $activeRatings ?? null)
        ->assign('oConfig_arr', getAdminSectionSettings(CONF_BEWERTUNG))
        ->assign('Sprachen', Sprache::getAllLanguages());
}

$smarty->assign('step', $step)
    ->assign('cTab', $cTab)
    ->display('bewertung.tpl');
