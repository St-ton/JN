<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/globalinclude.php';
require_once PFAD_INCLUDES . 'bewertung_inc.php';

$AktuelleSeite = 'BEWERTUNG';
Shop::run();
Shop::setPageType(PAGE_BEWERTUNG);
$cParameter_arr = Shop::getParameters();
$Einstellungen  = Shop::getSettings([CONF_GLOBAL, CONF_RSS, CONF_BEWERTUNG]);

// Bewertung in die Datenbank speichern
if (isset($_POST['bfh']) && (int)$_POST['bfh'] === 1) {
    speicherBewertung(
        $cParameter_arr['kArtikel'],
        Session::Customer()->getID(),
        Shop::getLanguage(),
        RequestHelper::verifyGPDataString('cTitel'),
        RequestHelper::verifyGPDataString('cText'),
        $cParameter_arr['nSterne']
    );
} elseif (isset($_POST['bhjn']) && (int)$_POST['bhjn'] === 1) { // Hilfreich abspeichern
    speicherHilfreich(
        $cParameter_arr['kArtikel'],
        Session::Customer()->getID(),
        Shop::getLanguage(),
        RequestHelper::verifyGPCDataInt('btgseite'),
        RequestHelper::verifyGPCDataInt('btgsterne')
    );
} elseif (RequestHelper::verifyGPCDataInt('bfa') === 1) {
    // Prüfe, ob Kunde eingeloggt
    if (empty($_SESSION['Kunde']->kKunde)) {
        $helper = Shop::Container()->getLinkService();
        header('Location: ' . $helper->getStaticRoute('jtl.php') .
                '?a=' . RequestHelper::verifyGPCDataInt('a') .
                '&bfa=1&r=' . R_LOGIN_BEWERTUNG,
            true,
            303
        );
        exit();
    }
    // hole aktuellen Artikel
    $AktuellerArtikel = new Artikel();
    $AktuellerArtikel->fuelleArtikel($cParameter_arr['kArtikel'], Artikel::getDefaultOptions());
    //falls kein Artikel vorhanden, zurück zum Shop
    if (!$AktuellerArtikel->kArtikel) {
        header('Location: ' . Shop::getURL() . '/', true, 303);
        exit;
    }
    $AufgeklappteKategorien = new KategorieListe();
    if ($AktuellerArtikel->Bewertungen === null) {
        $AktuellerArtikel->holeBewertung(
            Shop::getLanguage(),
            $Einstellungen['bewertung']['bewertung_anzahlseite'],
            0,
            -1,
            $Einstellungen['bewertung']['bewertung_freischalten'],
            $cParameter_arr['nSortierung']
        );
        $AktuellerArtikel->holehilfreichsteBewertung(Shop::getLanguage());
    }

    if ($Einstellungen['bewertung']['bewertung_artikel_gekauft'] === 'Y') {
        Shop::Smarty()->assign('nArtikelNichtGekauft', pruefeKundeArtikelGekauft(
            $AktuellerArtikel->kArtikel,
            $_SESSION['Kunde']->kKunde)
        );
    }
    Shop::Smarty()->assign('BereitsBewertet', pruefeKundeArtikelBewertet(
        $AktuellerArtikel->kArtikel,
        $_SESSION['Kunde']->kKunde))
        ->assign('Artikel', $AktuellerArtikel)
        ->assign('oBewertung', Shop::Container()->getDB()->select(
            'tbewertung',
            ['kArtikel', 'kKunde'],
            [$AktuellerArtikel->kArtikel, Session::Customer()->getID()]));

    require PFAD_ROOT . PFAD_INCLUDES . 'letzterInclude.php';
    Shop::Smarty()->display('productdetails/review_form.tpl');
} else {
    header('Location: ' . Shop::getURL() . '/', true, 303);
    exit;
}

require PFAD_ROOT . PFAD_INCLUDES . 'profiler_inc.php';
