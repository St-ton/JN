<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTL\Alert\Alert;
use JTL\Cart\PersistentCart;
use JTL\Customer\Customer;
use JTL\DB\ReturnType;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Pagination\Pagination;
use JTL\Shop;

require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('MODULE_SAVED_BASKETS_VIEW', true, true);

/** @global \JTL\Smarty\JTLSmarty $smarty */
$step              = 'uebersicht';
$settingsIDs       = [540];
$searchSQL         = new stdClass();
$searchSQL->cJOIN  = '';
$searchSQL->cWHERE = '';
$alertHelper       = Shop::Container()->getAlertService();
if (mb_strlen(Request::verifyGPDataString('tab')) > 0) {
    $smarty->assign('cTab', Request::verifyGPDataString('tab'));
}
if (mb_strlen(Request::verifyGPDataString('cSuche')) > 0) {
    $cSuche = Shop::Container()->getDB()->escape(Text::filterXSS(Request::verifyGPDataString('cSuche')));
    if (mb_strlen($cSuche) > 0) {
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
    $step = 'uebersicht';
    $alertHelper->addAlert(Alert::TYPE_SUCCESS, saveAdminSettings($settingsIDs, $_POST), 'saveSettings');
    $smarty->assign('tab', 'einstellungen');
}

if (isset($_GET['l']) && (int)$_GET['l'] > 0 && Form::validateToken()) {
    $customerID = (int)$_GET['l'];
    $persCart   = new PersistentCart($customerID);
    if ($persCart->entferneSelf()) {
        $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successCartPersPosDelete'), 'successCartPersPosDelete');
    }

    unset($persCart);
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
    ReturnType::SINGLE_OBJECT
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
    ReturnType::ARRAY_OF_OBJECTS
);

foreach ($customers as $item) {
    $customer = new Customer($item->kKunde);

    $item->cNachname = $customer->cNachname;
    $item->cFirma    = $customer->cFirma;
}

$smarty->assign('oKunde_arr', $customers)
       ->assign('oPagiKunden', $oPagiKunden);

if (isset($_GET['a']) && (int)$_GET['a'] > 0) {
    $step       = 'anzeigen';
    $customerID = (int)$_GET['a'];

    $persCart = Shop::Container()->getDB()->query(
        'SELECT COUNT(*) AS nAnzahl
            FROM twarenkorbperspos
            JOIN twarenkorbpers 
                ON twarenkorbpers.kWarenkorbPers = twarenkorbperspos.kWarenkorbPers
            WHERE twarenkorbpers.kKunde = ' . $customerID,
        ReturnType::SINGLE_OBJECT
    );

    $oPagiWarenkorb = (new Pagination('warenkorb'))
        ->setItemCount($persCart->nAnzahl)
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
            WHERE twarenkorbpers.kKunde = " . $customerID . '
            LIMIT ' . $oPagiWarenkorb->getLimitSQL(),
        ReturnType::ARRAY_OF_OBJECTS
    );
    foreach ($carts as $cart) {
        $customer = new Customer($cart->kKundeTMP);

        $cart->cNachname = $customer->cNachname;
        $cart->cFirma    = $customer->cFirma;
    }

    $smarty->assign('oWarenkorbPersPos_arr', $carts)
           ->assign('kKunde', $customerID)
           ->assign('oPagiWarenkorb', $oPagiWarenkorb);
}

$smarty->assign('step', $step)
       ->assign('oConfig_arr', getAdminSectionSettings($settingsIDs))
       ->display('warenkorbpers.tpl');
