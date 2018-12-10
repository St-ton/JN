<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/admininclude.php';
require_once PFAD_ROOT . PFAD_DBES . 'seo.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'toolsajax_inc.php';

$oAccount->permission('MODULE_CAC_VIEW', true, true);
/** @global Smarty\JTLSmarty $smarty */
$Einstellungen = Shop::getSettings([CONF_KUNDENWERBENKUNDEN]);
$cHinweis      = '';
$cFehler       = '';
$step          = 'kwk_uebersicht';

setzeSprache();
if (strlen(RequestHelper::verifyGPDataString('tab')) > 0) {
    $smarty->assign('cTab', RequestHelper::verifyGPDataString('tab'));
}
if (isset($_POST['einstellungen']) && (int)$_POST['einstellungen'] > 0) {
    $cHinweis .= saveAdminSectionSettings(CONF_KUNDENWERBENKUNDEN, $_POST);
}
if (RequestHelper::verifyGPCDataInt('KwK') === 1
    && RequestHelper::verifyGPCDataInt('nichtreggt_loeschen') === 1
    && FormHelper::validateToken()
) {
    $kKundenWerbenKunden_arr = $_POST['kKundenWerbenKunden'];
    if (is_array($kKundenWerbenKunden_arr) && count($kKundenWerbenKunden_arr) > 0) {
        foreach ($kKundenWerbenKunden_arr as $kKundenWerbenKunden) {
            Shop::Container()->getDB()->delete('tkundenwerbenkunden', 'kKundenWerbenKunden', (int)$kKundenWerbenKunden);
        }
        $cHinweis .= 'Ihre markierten Neukunden wurden erfolgreich gelöscht.<br />';
    } else {
        $cFehler .= 'Fehler: Bitte markieren Sie mindestens einen Neukunden<br />';
    }
}
if ($step === 'kwk_uebersicht') {
    $oAnzahlReg      = Shop::Container()->getDB()->query(
        'SELECT COUNT(*) AS nAnzahl
            FROM tkundenwerbenkunden
            WHERE nRegistriert = 0',
        \DB\ReturnType::SINGLE_OBJECT
    );
    $oAnzahlNichtReg = Shop::Container()->getDB()->query(
        'SELECT COUNT(*) AS nAnzahl
            FROM tkundenwerbenkunden
            WHERE nRegistriert = 1',
        \DB\ReturnType::SINGLE_OBJECT
    );
    $oAnzahlPraemie  = Shop::Container()->getDB()->query(
        'SELECT COUNT(*) AS nAnzahl
            FROM tkundenwerbenkundenbonus',
        \DB\ReturnType::SINGLE_OBJECT
    );

    $oPagiNichtReg = (new Pagination('nichtreg'))
        ->setItemCount($oAnzahlReg->nAnzahl)
        ->assemble();
    $oPagiReg      = (new Pagination('reg'))
        ->setItemCount($oAnzahlNichtReg->nAnzahl)
        ->assemble();
    $oPagiPraemie  = (new Pagination('praemie'))
        ->setItemCount($oAnzahlPraemie->nAnzahl)
        ->assemble();

    $oKwKNichtReg_arr = Shop::Container()->getDB()->query(
        "SELECT tkundenwerbenkunden.*, tkunde.kKunde AS kKundeBestand, tkunde.cMail, 
            DATE_FORMAT(tkundenwerbenkunden.dErstellt, '%d.%m.%Y %H:%i') AS dErstellt_de,
            tkunde.cVorname AS cBestandVorname, tkunde.cNachname AS cBestandNachname
            FROM tkundenwerbenkunden
            JOIN tkunde 
                ON tkunde.kKunde = tkundenwerbenkunden.kKunde
            WHERE tkundenwerbenkunden.nRegistriert = 0
            ORDER BY tkundenwerbenkunden.dErstellt DESC 
            LIMIT " . $oPagiNichtReg->getLimitSQL(),
        \DB\ReturnType::ARRAY_OF_OBJECTS
    );
    foreach ($oKwKNichtReg_arr as $i => $oKwKNichtReg) {
        $oKunde = new Kunde($oKwKNichtReg->kKundeBestand ?? 0);

        $oKwKNichtReg_arr[$i]->cBestandNachname = $oKunde->cNachname;
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
            LIMIT " . $oPagiReg->getLimitSQL(),
        \DB\ReturnType::ARRAY_OF_OBJECTS
    );
    foreach ($registered as $customer) {
        $oBestandsKunde = new Kunde($customer->kKunde ?? 0);

        $customer->cBestandVorname  = $oBestandsKunde->cVorname;
        $customer->cBestandNachname = $oBestandsKunde->cNachname;
        $customer->cMail            = $oBestandsKunde->cMail;
    }
    // letzten 100 Bestandskunden die Guthaben erhalten haben
    $oKwKBestandBonus_arr = Shop::Container()->getDB()->query(
        "SELECT tkundenwerbenkundenbonus.*, tkunde.kKunde AS kKundeBestand, tkunde.cMail, 
            DATE_FORMAT(tkundenwerbenkundenbonus.dErhalten, '%d.%m.%Y %H:%i') AS dErhalten_de,
            tkunde.cVorname AS cBestandVorname, tkunde.cNachname AS cBestandNachname
            FROM tkundenwerbenkundenbonus
            JOIN tkunde 
                ON tkunde.kKunde = tkundenwerbenkundenbonus.kKunde
            ORDER BY dErhalten DESC 
            LIMIT " . $oPagiPraemie->getLimitSQL(),
        \DB\ReturnType::ARRAY_OF_OBJECTS
    );
    foreach ($oKwKBestandBonus_arr as $i => $oKwKBestandBonus) {
        $oKunde = new Kunde($oKwKBestandBonus->kKundeBestand ?? 0);

        $oKwKBestandBonus_arr[$i]->cBestandNachname = $oKunde->cNachname;
    }
    $smarty->assign('oConfig_arr', getAdminSectionSettings(CONF_KUNDENWERBENKUNDEN))
           ->assign('oKwKNichtReg_arr', $oKwKNichtReg_arr)
           ->assign('oKwKReg_arr', $registered)
           ->assign('oKwKBestandBonus_arr', $oKwKBestandBonus_arr)
           ->assign('oPagiNichtReg', $oPagiNichtReg)
           ->assign('oPagiReg', $oPagiReg)
           ->assign('oPagiPraemie', $oPagiPraemie);
}
$smarty->assign('Sprachen', Sprache::getAllLanguages())
       ->assign('kSprache', $_SESSION['kSprache'])
       ->assign('hinweis', $cHinweis)
       ->assign('fehler', $cFehler)
       ->assign('step', $step)
       ->display('kundenwerbenkunden.tpl');
