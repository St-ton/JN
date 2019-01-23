<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use Helpers\Form;
use Helpers\Request;
use Pagination\Pagination;

require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('MODULE_SAVED_BASKETS_VIEW', true, true);

/** @global Smarty\JTLSmarty $smarty */
$cHinweis          = '';
$cFehler           = '';
$step              = 'uebersicht';
$settingsIDs       = [540];
$searchSQL         = new stdClass();
$searchSQL->cJOIN  = '';
$searchSQL->cWHERE = '';
if (strlen(Request::verifyGPDataString('tab')) > 0) {
    $smarty->assign('cTab', Request::verifyGPDataString('tab'));
}
if (strlen(Request::verifyGPDataString('cSuche')) > 0) {
    $cSuche = Shop::Container()->getDB()->escape(StringHandler::filterXSS(Request::verifyGPDataString('cSuche')));
    if (strlen($cSuche) > 0) {
        $searchSQL->cWHERE = " WHERE (tkunde.cKundenNr LIKE '%" . $cSuche . "%'
            OR tkunde.cVorname LIKE '%" . $cSuche . "%' 
            OR tkunde.cMail LIKE '%" . $cSuche . "%')";
    }

    $smarty->assign('cSuche', $cSuche);
}
if (isset($_POST['einstellungen'])
    && (int)$_POST['einstellungen'] === 1
    && (isset($_POST['speichern']) || (isset($_POST['a']) && $_POST['a'] === 'speichern'))
    && Form::validateToken()
) {
    $step      = 'uebersicht';
    $cHinweis .= saveAdminSettings($settingsIDs, $_POST);
    $smarty->assign('tab', 'einstellungen');
}

if (isset($_GET['l']) && (int)$_GET['l'] > 0 && Form::validateToken()) {
    $kKunde         = (int)$_GET['l'];
    $oWarenkorbPers = new WarenkorbPers($kKunde);

    if ($oWarenkorbPers->entferneSelf()) {
        $cHinweis .= __('successCartPersPosDelete');
    }

    unset($oWarenkorbPers);
}
$customerCount = (int)Shop::Container()->getDB()->query(
    'SELECT COUNT(*) AS count
        FROM
        (
            SELECT tkunde.kKunde
            FROM tkunde
            JOIN twarenkorbpers 
                ON tkunde.kKunde = twarenkorbpers.kKunde
            JOIN twarenkorbperspos 
                ON twarenkorbperspos.kWarenkorbPers = twarenkorbpers.kWarenkorbPers
            ' . $searchSQL->cWHERE . '
            GROUP BY tkunde.kKunde
        ) AS tAnzahl',
    \DB\ReturnType::SINGLE_OBJECT
)->count;

$oPagiKunden = (new Pagination('kunden'))
    ->setItemCount($customerCount)
    ->assemble();

$customers = Shop::Container()->getDB()->query(
    "SELECT tkunde.kKunde, tkunde.cFirma, tkunde.cVorname, tkunde.cNachname, 
        DATE_FORMAT(twarenkorbpers.dErstellt, '%d.%m.%Y  %H:%i') AS Datum, 
        COUNT(twarenkorbperspos.kWarenkorbPersPos) AS nAnzahl
        FROM tkunde
        JOIN twarenkorbpers 
            ON tkunde.kKunde = twarenkorbpers.kKunde
        JOIN twarenkorbperspos 
            ON twarenkorbperspos.kWarenkorbPers = twarenkorbpers.kWarenkorbPers
        " . $searchSQL->cWHERE . '
        GROUP BY tkunde.kKunde
        ORDER BY twarenkorbpers.dErstellt DESC
        LIMIT ' . $oPagiKunden->getLimitSQL(),
    \DB\ReturnType::ARRAY_OF_OBJECTS
);

foreach ($customers as $item) {
    $customer = new Kunde($item->kKunde);

    $item->cNachname = $customer->cNachname;
    $item->cFirma    = $customer->cFirma;
}

$smarty->assign('oKunde_arr', $customers)
       ->assign('oPagiKunden', $oPagiKunden);

if (isset($_GET['a']) && (int)$_GET['a'] > 0) {
    $step   = 'anzeigen';
    $kKunde = (int)$_GET['a'];

    $oWarenkorbPers = Shop::Container()->getDB()->query(
        'SELECT COUNT(*) AS nAnzahl
            FROM twarenkorbperspos
            JOIN twarenkorbpers 
                ON twarenkorbpers.kWarenkorbPers = twarenkorbperspos.kWarenkorbPers
            WHERE twarenkorbpers.kKunde = ' . $kKunde,
        \DB\ReturnType::SINGLE_OBJECT
    );

    $oPagiWarenkorb = (new Pagination('warenkorb'))
        ->setItemCount($oWarenkorbPers->nAnzahl)
        ->assemble();

    $carts = Shop::Container()->getDB()->query(
        "SELECT tkunde.kKunde AS kKundeTMP, tkunde.cVorname, tkunde.cNachname, twarenkorbperspos.kArtikel, 
            twarenkorbperspos.cArtikelName, twarenkorbpers.kKunde, twarenkorbperspos.fAnzahl, 
            DATE_FORMAT(twarenkorbperspos.dHinzugefuegt, '%d.%m.%Y  %H:%i') AS Datum
            FROM twarenkorbpers
            JOIN tkunde 
                ON tkunde.kKunde = twarenkorbpers.kKunde
            JOIN twarenkorbperspos 
                ON twarenkorbpers.kWarenkorbPers = twarenkorbperspos.kWarenkorbPers
            WHERE twarenkorbpers.kKunde = " . $kKunde . '
            LIMIT ' . $oPagiWarenkorb->getLimitSQL(),
        \DB\ReturnType::ARRAY_OF_OBJECTS
    );
    foreach ($carts as $cart) {
        $customer = new Kunde($cart->kKundeTMP);

        $cart->cNachname = $customer->cNachname;
        $cart->cFirma    = $customer->cFirma;
    }

    $smarty->assign('oWarenkorbPersPos_arr', $carts)
           ->assign('kKunde', $kKunde)
           ->assign('oPagiWarenkorb', $oPagiWarenkorb);
}

$smarty->assign('step', $step)
       ->assign('cHinweis', $cHinweis)
       ->assign('cFehler', $cFehler)
       ->assign('oConfig_arr', getAdminSectionSettings($settingsIDs))
       ->display('warenkorbpers.tpl');
