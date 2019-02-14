<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Customer\Kunde;
use JTL\Shop;
use JTL\Sprache;
use JTL\Pagination\Pagination;
use JTL\DB\ReturnType;

require_once __DIR__ . '/includes/admininclude.php';
require_once PFAD_ROOT . PFAD_DBES . 'seo.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'toolsajax_inc.php';

$oAccount->permission('MODULE_CAC_VIEW', true, true);
/** @global \JTL\Smarty\JTLSmarty $smarty */
$cHinweis = '';
$cFehler  = '';
$step     = 'kwk_uebersicht';

setzeSprache();
if (mb_strlen(Request::verifyGPDataString('tab')) > 0) {
    $smarty->assign('cTab', Request::verifyGPDataString('tab'));
}
if (isset($_POST['einstellungen']) && (int)$_POST['einstellungen'] > 0) {
    $cHinweis .= saveAdminSectionSettings(CONF_KUNDENWERBENKUNDEN, $_POST);
}
if (Request::verifyGPCDataInt('KwK') === 1
    && Request::verifyGPCDataInt('nichtreggt_loeschen') === 1
    && Form::validateToken()
) {
    $kwkIDs = $_POST['kKundenWerbenKunden'];
    if (is_array($kwkIDs) && count($kwkIDs) > 0) {
        foreach ($kwkIDs as $id) {
            Shop::Container()->getDB()->delete('tkundenwerbenkunden', 'kKundenWerbenKunden', (int)$id);
        }
        $cHinweis .= __('successNewCustomerDelete') . '<br />';
    } else {
        $cFehler .= __('errorAtLeastOneNewCustomer') . '<br />';
    }
}
if ($step === 'kwk_uebersicht') {
    $regCount    = Shop::Container()->getDB()->query(
        'SELECT COUNT(*) AS nAnzahl
            FROM tkundenwerbenkunden
            WHERE nRegistriert = 0',
        ReturnType::SINGLE_OBJECT
    );
    $nonRegCount = Shop::Container()->getDB()->query(
        'SELECT COUNT(*) AS nAnzahl
            FROM tkundenwerbenkunden
            WHERE nRegistriert = 1',
        ReturnType::SINGLE_OBJECT
    );
    $bonusCount  = Shop::Container()->getDB()->query(
        'SELECT COUNT(*) AS nAnzahl
            FROM tkundenwerbenkundenbonus',
        ReturnType::SINGLE_OBJECT
    );
    $pagiNonReg  = (new Pagination('nichtreg'))
        ->setItemCount($regCount->nAnzahl)
        ->assemble();
    $pagiReg     = (new Pagination('reg'))
        ->setItemCount($nonRegCount->nAnzahl)
        ->assemble();
    $pagiBonus   = (new Pagination('praemie'))
        ->setItemCount($bonusCount->nAnzahl)
        ->assemble();

    $nonRegistered = Shop::Container()->getDB()->query(
        "SELECT tkundenwerbenkunden.*, tkunde.kKunde AS kKundeBestand, tkunde.cMail, 
            DATE_FORMAT(tkundenwerbenkunden.dErstellt, '%d.%m.%Y %H:%i') AS dErstellt_de,
            tkunde.cVorname AS cBestandVorname, tkunde.cNachname AS cBestandNachname
            FROM tkundenwerbenkunden
            JOIN tkunde 
                ON tkunde.kKunde = tkundenwerbenkunden.kKunde
            WHERE tkundenwerbenkunden.nRegistriert = 0
            ORDER BY tkundenwerbenkunden.dErstellt DESC 
            LIMIT " . $pagiNonReg->getLimitSQL(),
        ReturnType::ARRAY_OF_OBJECTS
    );
    foreach ($nonRegistered as $item) {
        $cstmr                  = new Kunde((int)($item->kKundeBestand ?? 0));
        $item->cBestandNachname = $cstmr->cNachname;
    }
    $registered = Shop::Container()->getDB()->query(
        "SELECT tkundenwerbenkunden.*, 
            DATE_FORMAT(tkundenwerbenkunden.dErstellt, '%d.%m.%Y %H:%i') AS dErstellt_de,
            DATE_FORMAT(tkunde.dErstellt, '%d.%m.%Y') AS dBestandErstellt_de
            FROM tkundenwerbenkunden
            JOIN tkunde 
                ON tkunde.cMail = tkundenwerbenkunden.cEmail
            WHERE tkundenwerbenkunden.nRegistriert = 1
            ORDER BY tkundenwerbenkunden.dErstellt DESC 
            LIMIT " . $pagiReg->getLimitSQL(),
        ReturnType::ARRAY_OF_OBJECTS
    );
    foreach ($registered as $customer) {
        $regCstmr = new Kunde($customer->kKunde ?? 0);

        $customer->cBestandVorname  = $regCstmr->cVorname;
        $customer->cBestandNachname = $regCstmr->cNachname;
        $customer->cMail            = $regCstmr->cMail;
    }
    // letzten 100 Bestandskunden die Guthaben erhalten haben
    $last100bonus = Shop::Container()->getDB()->query(
        "SELECT tkundenwerbenkundenbonus.*, tkunde.kKunde AS kKundeBestand, tkunde.cMail, 
            DATE_FORMAT(tkundenwerbenkundenbonus.dErhalten, '%d.%m.%Y %H:%i') AS dErhalten_de,
            tkunde.cVorname AS cBestandVorname, tkunde.cNachname AS cBestandNachname
            FROM tkundenwerbenkundenbonus
            JOIN tkunde 
                ON tkunde.kKunde = tkundenwerbenkundenbonus.kKunde
            ORDER BY dErhalten DESC 
            LIMIT " . $pagiBonus->getLimitSQL(),
        ReturnType::ARRAY_OF_OBJECTS
    );
    foreach ($last100bonus as $item) {
        $cstmr                  = new Kunde((int)($item->kKundeBestand ?? 0));
        $item->cBestandNachname = $cstmr->cNachname;
    }
    $smarty->assign('oConfig_arr', getAdminSectionSettings(CONF_KUNDENWERBENKUNDEN))
           ->assign('oKwKNichtReg_arr', $nonRegistered)
           ->assign('oKwKReg_arr', $registered)
           ->assign('oKwKBestandBonus_arr', $last100bonus)
           ->assign('oPagiNichtReg', $pagiNonReg)
           ->assign('oPagiReg', $pagiReg)
           ->assign('oPagiPraemie', $pagiBonus);
}
$smarty->assign('Sprachen', Sprache::getAllLanguages())
       ->assign('kSprache', $_SESSION['kSprache'])
       ->assign('hinweis', $cHinweis)
       ->assign('fehler', $cFehler)
       ->assign('step', $step)
       ->display('kundenwerbenkunden.tpl');
