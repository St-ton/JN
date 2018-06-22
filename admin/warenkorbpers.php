<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('MODULE_SAVED_BASKETS_VIEW', true, true);

/** @global JTLSmarty $smarty */
$cHinweis          = '';
$cFehler           = '';
$step              = 'uebersicht';
$settingsIDs       = [540];
$cSucheSQL         = new stdClass();
$cSucheSQL->cJOIN  = '';
$cSucheSQL->cWHERE = '';
// Tabs
if (strlen(RequestHelper::verifyGPDataString('tab')) > 0) {
    $smarty->assign('cTab', RequestHelper::verifyGPDataString('tab'));
}
// Suche
if (strlen(RequestHelper::verifyGPDataString('cSuche')) > 0) {
    $cSuche = Shop::Container()->getDB()->escape(StringHandler::filterXSS(RequestHelper::verifyGPDataString('cSuche')));

    if (strlen($cSuche) > 0) {
        $cSucheSQL->cWHERE = " WHERE (tkunde.cKundenNr LIKE '%" . $cSuche . "%'
            OR tkunde.cVorname LIKE '%" . $cSuche . "%' 
            OR tkunde.cMail LIKE '%" . $cSuche . "%')";
    }

    $smarty->assign('cSuche', $cSuche);
}
// Einstellungen
if (isset($_POST['einstellungen'])
    && (int)$_POST['einstellungen'] === 1
    && (isset($_POST['speichern']) || (isset($_POST['a']) && $_POST['a'] === 'speichern'))
    && FormHelper::validateToken()
) {
    $step = 'uebersicht';
    $cHinweis .= saveAdminSettings($settingsIDs, $_POST);
    $smarty->assign('tab', 'einstellungen');
}

if (isset($_GET['l']) && (int)$_GET['l'] > 0 && FormHelper::validateToken()) {
    $kKunde         = (int)$_GET['l'];
    $oWarenkorbPers = new WarenkorbPers($kKunde);

    if ($oWarenkorbPers->entferneSelf()) {
        $cHinweis .= 'Ihr ausgew&auml;hlter Warenkorb wurde erfolgreich gel&ouml;scht.';
    }

    unset($oWarenkorbPers);
}

// Anzahl Kunden mit Warenkorb
$oKundeAnzahl = Shop::Container()->getDB()->query(
    "SELECT count(*) AS nAnzahl
        FROM
        (
            SELECT tkunde.kKunde
            FROM tkunde
            JOIN twarenkorbpers 
                ON tkunde.kKunde = twarenkorbpers.kKunde
            JOIN twarenkorbperspos 
                ON twarenkorbperspos.kWarenkorbPers = twarenkorbpers.kWarenkorbPers
            " . $cSucheSQL->cWHERE . "
            GROUP BY tkunde.kKunde
        ) AS tAnzahl",
    \DB\ReturnType::SINGLE_OBJECT
);

// Pagination
$oPagiKunden = (new Pagination('kunden'))
    ->setItemCount($oKundeAnzahl->nAnzahl)
    ->assemble();

// Gespeicherte Warenkoerbe
$oKunde_arr = Shop::Container()->getDB()->query(
    "SELECT tkunde.kKunde, tkunde.cFirma, tkunde.cVorname, tkunde.cNachname, 
        DATE_FORMAT(twarenkorbpers.dErstellt, '%d.%m.%Y  %H:%i') AS Datum, 
        count(twarenkorbperspos.kWarenkorbPersPos) AS nAnzahl
        FROM tkunde
        JOIN twarenkorbpers 
            ON tkunde.kKunde = twarenkorbpers.kKunde
        JOIN twarenkorbperspos 
            ON twarenkorbperspos.kWarenkorbPers = twarenkorbpers.kWarenkorbPers
        " . $cSucheSQL->cWHERE . "
        GROUP BY tkunde.kKunde
        ORDER BY twarenkorbpers.dErstellt DESC
        LIMIT " . $oPagiKunden->getLimitSQL(),
    \DB\ReturnType::ARRAY_OF_OBJECTS
);

foreach ($oKunde_arr as $i => $oKunde) {
    $oKundeTMP = new Kunde($oKunde->kKunde);

    $oKunde_arr[$i]->cNachname = $oKundeTMP->cNachname;
    $oKunde_arr[$i]->cFirma    = $oKundeTMP->cFirma;
}

$smarty->assign('oKunde_arr', $oKunde_arr)
       ->assign('oPagiKunden', $oPagiKunden);

// Anzeigen
if (isset($_GET['a']) && (int)$_GET['a'] > 0) {
    $step   = 'anzeigen';
    $kKunde = (int)$_GET['a'];

    $oWarenkorbPers = Shop::Container()->getDB()->query(
        "SELECT count(*) AS nAnzahl
            FROM twarenkorbperspos
            JOIN twarenkorbpers 
                ON twarenkorbpers.kWarenkorbPers = twarenkorbperspos.kWarenkorbPers
            WHERE twarenkorbpers.kKunde = " . $kKunde,
        \DB\ReturnType::SINGLE_OBJECT
    );

    $oPagiWarenkorb = (new Pagination('warenkorb'))
        ->setItemCount($oWarenkorbPers->nAnzahl)
        ->assemble();

    $oWarenkorbPersPos_arr = Shop::Container()->getDB()->query(
        "SELECT tkunde.kKunde AS kKundeTMP, tkunde.cVorname, tkunde.cNachname, twarenkorbperspos.kArtikel, 
            twarenkorbperspos.cArtikelName, twarenkorbpers.kKunde, twarenkorbperspos.fAnzahl, 
            DATE_FORMAT(twarenkorbperspos.dHinzugefuegt, '%d.%m.%Y  %H:%i') AS Datum
            FROM twarenkorbpers
            JOIN tkunde 
                ON tkunde.kKunde = twarenkorbpers.kKunde
            JOIN twarenkorbperspos 
                ON twarenkorbpers.kWarenkorbPers = twarenkorbperspos.kWarenkorbPers
            WHERE twarenkorbpers.kKunde = " . $kKunde . "
            LIMIT " . $oPagiWarenkorb->getLimitSQL(),
        \DB\ReturnType::ARRAY_OF_OBJECTS
    );
    foreach ($oWarenkorbPersPos_arr as $i => $oWarenkorbPersPos) {
        $oKundeTMP = new Kunde($oWarenkorbPersPos->kKundeTMP);

        $oWarenkorbPersPos_arr[$i]->cNachname = $oKundeTMP->cNachname;
        $oWarenkorbPersPos_arr[$i]->cFirma    = $oKundeTMP->cFirma;
    }

    $smarty->assign('oWarenkorbPersPos_arr', $oWarenkorbPersPos_arr)
           ->assign('kKunde', $kKunde)
           ->assign('oPagiWarenkorb', $oPagiWarenkorb);
} else {
    // uebersicht
    // Config holen
    $oConfig_arr = Shop::Container()->getDB()->query(
        "SELECT *
            FROM teinstellungenconf
            WHERE kEinstellungenConf IN (" . implode(',', $settingsIDs) . ")
            ORDER BY nSort",
        \DB\ReturnType::ARRAY_OF_OBJECTS
    );
    $configCount = count($oConfig_arr);
    for ($i = 0; $i < $configCount; $i++) {
        $oConfig_arr[$i]->ConfWerte = Shop::Container()->getDB()->selectAll(
            'teinstellungenconfwerte',
            'kEinstellungenConf',
            (int)$oConfig_arr[$i]->kEinstellungenConf,
            '*',
            'nSort'
        );

        $oSetValue = Shop::Container()->getDB()->select(
            'teinstellungen',
            'kEinstellungenSektion',
            (int)$oConfig_arr[$i]->kEinstellungenSektion,
            'cName',
            $oConfig_arr[$i]->cWertName
        );
        $oConfig_arr[$i]->gesetzterWert = $oSetValue->cWert ?? null;
    }

    $smarty->assign('oConfig_arr', $oConfig_arr);
}

$smarty->assign('step', $step)
       ->assign('cHinweis', $cHinweis)
       ->assign('cFehler', $cFehler)
       ->display('warenkorbpers.tpl');
