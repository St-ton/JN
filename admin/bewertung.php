<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use Helpers\FormHelper;
use Helpers\Request;
use Pagination\Pagination;

require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('MODULE_VOTESYSTEM_VIEW', true, true);

require_once PFAD_ROOT . PFAD_INCLUDES . 'bewertung_inc.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'bewertung_inc.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'toolsajax_inc.php';
/** @global \Smarty\JTLSmarty $smarty */
$Einstellungen = Shop::getSettings([CONF_BEWERTUNG]);
$cHinweis      = '';
$cFehler       = '';
$step          = 'bewertung_uebersicht';
$cTab          = 'freischalten';
$cacheTags     = [];

setzeSprache();

if (strlen(Request::verifyGPDataString('tab')) > 0) {
    $cTab = Request::verifyGPDataString('tab');
}
if (FormHelper::validateToken()) {
    if (Request::verifyGPCDataInt('bewertung_editieren') === 1) {
        if (editiereBewertung($_POST)) {
            $cHinweis .= 'Ihre Bewertung wurde erfolgreich editiert. ';

            if (Request::verifyGPCDataInt('nFZ') === 1) {
                header('Location: freischalten.php');
                exit();
            }
        } else {
            $step     = 'bewertung_editieren';
            $cFehler .= 'Fehler: Bitte überprüfen Sie Ihre Eingaben. ';
        }
    } elseif (isset($_POST['einstellungen']) && (int)$_POST['einstellungen'] === 1) {
        if (Request::verifyGPDataString('bewertung_guthaben_nutzen') === 'Y'
            && Request::verifyGPDataString('bewertung_freischalten') !== 'Y'
        ) {
            $cFehler = 'Guthabenbonus kann nur mit "Bewertung freischalten" verwendet werden.';
        } else {
            Shop::Container()->getCache()->flushTags([CACHING_GROUP_ARTICLE]);
            $cHinweis .= saveAdminSectionSettings(CONF_BEWERTUNG, $_POST);
        }
    } elseif (isset($_POST['bewertung_nicht_aktiv']) && (int)$_POST['bewertung_nicht_aktiv'] === 1) {
        if (isset($_POST['aktivieren'])) {
            if (is_array($_POST['kBewertung']) && count($_POST['kBewertung']) > 0) {
                $kArtikel_arr = $_POST['kArtikel'];
                foreach ($_POST['kBewertung'] as $i => $kBewertung) {
                    $upd         = new stdClass();
                    $upd->nAktiv = 1;
                    Shop::Container()->getDB()->update('tbewertung', 'kBewertung', (int)$kBewertung, $upd);
                    aktualisiereDurchschnitt($kArtikel_arr[$i], $Einstellungen['bewertung']['bewertung_freischalten']);
                    checkeBewertungGuthabenBonus($kBewertung, $Einstellungen);
                    $cacheTags[] = $kArtikel_arr[$i];
                }
                array_walk(
                    $cacheTags,
                    function (&$i) {
                        $i = CACHING_GROUP_ARTICLE . '_' . $i;
                    }
                );
                Shop::Container()->getCache()->flushTags($cacheTags);
                $cHinweis .= count($_POST['kBewertung']) . ' Bewertung(en) wurde(n) erfolgreich aktiviert.';
            }
        } elseif (isset($_POST['loeschen'])) { // Bewertungen loeschen
            if (is_array($_POST['kBewertung']) && count($_POST['kBewertung']) > 0) {
                foreach ($_POST['kBewertung'] as $kBewertung) {
                    Shop::Container()->getDB()->delete('tbewertung', 'kBewertung', (int)$kBewertung);
                }
                $cHinweis .= count($_POST['kBewertung']) . ' Bewertung(en) wurde(n) erfolgreich gelöscht.';
            }
        }
    } elseif (isset($_POST['bewertung_aktiv']) && (int)$_POST['bewertung_aktiv'] === 1) {
        if (isset($_POST['cArtNr'])) {
            // Bewertungen holen
            $oBewertungAktiv_arr = Shop::Container()->getDB()->queryPrepared(
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
                \DB\ReturnType::ARRAY_OF_OBJECTS
            );
            $smarty->assign('cArtNr', StringHandler::filterXSS($_POST['cArtNr']));
        }
        // Bewertungen loeschen
        if (isset($_POST['loeschen']) && is_array($_POST['kBewertung']) && count($_POST['kBewertung']) > 0) {
            $kArtikel_arr = $_POST['kArtikel'];
            foreach ($_POST['kBewertung'] as $i => $kBewertung) {
                BewertungsGuthabenBonusLoeschen($kBewertung);
                Shop::Container()->getDB()->delete('tbewertung', 'kBewertung', (int)$kBewertung);
                aktualisiereDurchschnitt($kArtikel_arr[$i], $Einstellungen['bewertung']['bewertung_freischalten']);
                $cacheTags[] = $kArtikel_arr[$i];
            }
            array_walk(
                $cacheTags,
                function (&$i) {
                    $i = CACHING_GROUP_ARTICLE . '_' . $i;
                }
            );
            Shop::Container()->getCache()->flushTags($cacheTags);
            $cHinweis .= count($_POST['kBewertung']) . ' Bewertung(en) wurde(n) erfolgreich gelöscht.';
        }
    }
}

if ((isset($_GET['a']) && $_GET['a'] === 'editieren') || $step === 'bewertung_editieren') {
    $step = 'bewertung_editieren';
    $smarty->assign('oBewertung', holeBewertung(Request::verifyGPCDataInt('kBewertung')));
    if (Request::verifyGPCDataInt('nFZ') === 1) {
        $smarty->assign('nFZ', 1);
    }
} elseif ($step === 'bewertung_uebersicht') {
    if (isset($_GET['a']) && $_GET['a'] === 'delreply' && FormHelper::validateToken()) {
        removeReply(Request::verifyGPCDataInt('kBewertung'));
        $cHinweis = 'Antwort zu einer Bewertung wurde entfernt.';
    }
    $nBewertungen      = (int)Shop::Container()->getDB()->query(
        'SELECT COUNT(*) AS nAnzahl
            FROM tbewertung
            WHERE kSprache = ' . (int)$_SESSION['kSprache'] . '
                AND nAktiv = 0',
        \DB\ReturnType::SINGLE_OBJECT
    )->nAnzahl;
    $nBewertungenAktiv = (int)Shop::Container()->getDB()->query(
        'SELECT COUNT(*) AS nAnzahl
            FROM tbewertung
            WHERE kSprache = ' . (int)$_SESSION['kSprache'] . '
                AND nAktiv = 1',
        \DB\ReturnType::SINGLE_OBJECT
    )->nAnzahl;

    $oPagiInaktiv = (new Pagination('inactive'))
        ->setItemCount($nBewertungen)
        ->assemble();
    $oPageAktiv   = (new Pagination('active'))
        ->setItemCount($nBewertungenAktiv)
        ->assemble();

    $ratings       = Shop::Container()->getDB()->query(
        "SELECT tbewertung.*, DATE_FORMAT(tbewertung.dDatum, '%d.%m.%Y') AS Datum, tartikel.cName AS ArtikelName
            FROM tbewertung
            LEFT JOIN tartikel 
                ON tbewertung.kArtikel = tartikel.kArtikel
            WHERE tbewertung.kSprache = " . (int)$_SESSION['kSprache'] . '
                AND tbewertung.nAktiv = 0
            ORDER BY tbewertung.kArtikel, tbewertung.dDatum DESC
            LIMIT ' . $oPagiInaktiv->getLimitSQL(),
        \DB\ReturnType::ARRAY_OF_OBJECTS
    );
    $last50ratings = Shop::Container()->getDB()->query(
        "SELECT tbewertung.*, DATE_FORMAT(tbewertung.dDatum, '%d.%m.%Y') AS Datum, tartikel.cName AS ArtikelName
            FROM tbewertung
            LEFT JOIN tartikel 
                ON tbewertung.kArtikel = tartikel.kArtikel
            WHERE tbewertung.kSprache = " . (int)$_SESSION['kSprache'] . '
                AND tbewertung.nAktiv = 1
            ORDER BY tbewertung.dDatum DESC
            LIMIT ' . $oPageAktiv->getLimitSQL(),
        \DB\ReturnType::ARRAY_OF_OBJECTS
    );

    $smarty->assign('oPagiInaktiv', $oPagiInaktiv)
           ->assign('oPagiAktiv', $oPageAktiv)
           ->assign('oBewertung_arr', $ratings)
           ->assign('oBewertungLetzten50_arr', $last50ratings)
           ->assign('oBewertungAktiv_arr', $oBewertungAktiv_arr ?? null)
           ->assign('oConfig_arr', getAdminSectionSettings(CONF_BEWERTUNG))
           ->assign('Sprachen', Sprache::getAllLanguages());
}

$smarty->assign('hinweis', $cHinweis)
       ->assign('fehler', $cFehler)
       ->assign('step', $step)
       ->assign('cTab', $cTab)
       ->display('bewertung.tpl');
