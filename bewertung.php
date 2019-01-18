<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use Helpers\Request;

require_once __DIR__ . '/includes/globalinclude.php';
require_once PFAD_INCLUDES . 'bewertung_inc.php';

Shop::run();
Shop::setPageType(PAGE_BEWERTUNG);
$cParameter_arr = Shop::getParameters();
$Einstellungen  = Shop::getSettings([CONF_GLOBAL, CONF_RSS, CONF_BEWERTUNG]);
$ratingAllowed  = true;
if (isset($_POST['bfh']) && (int)$_POST['bfh'] === 1) {
    speicherBewertung(
        $cParameter_arr['kArtikel'],
        \Session\Frontend::getCustomer()->getID(),
        Shop::getLanguageID(),
        Request::verifyGPDataString('cTitel'),
        Request::verifyGPDataString('cText'),
        $cParameter_arr['nSterne']
    );
} elseif (isset($_POST['bhjn']) && (int)$_POST['bhjn'] === 1) {
    speicherHilfreich(
        $cParameter_arr['kArtikel'],
        \Session\Frontend::getCustomer()->getID(),
        Shop::getLanguageID(),
        Request::verifyGPCDataInt('btgseite'),
        Request::verifyGPCDataInt('btgsterne')
    );
} elseif (Request::verifyGPCDataInt('bfa') === 1) {
    if (\Session\Frontend::getCustomer()->getID() <= 0) {
        $helper = Shop::Container()->getLinkService();
        header(
            'Location: ' . $helper->getStaticRoute('jtl.php') .
                '?a=' . Request::verifyGPCDataInt('a') .
                '&bfa=1&r=' . R_LOGIN_BEWERTUNG,
            true,
            303
        );
        exit();
    }
    $AktuellerArtikel = new Artikel();
    $AktuellerArtikel->fuelleArtikel($cParameter_arr['kArtikel'], Artikel::getDefaultOptions());
    if (!$AktuellerArtikel->kArtikel) {
        header('Location: ' . Shop::getURL() . '/', true, 303);
        exit;
    }
    $AufgeklappteKategorien = new KategorieListe();
    if ($AktuellerArtikel->Bewertungen === null) {
        $AktuellerArtikel->holeBewertung(
            Shop::getLanguageID(),
            $Einstellungen['bewertung']['bewertung_anzahlseite'],
            0,
            -1,
            $Einstellungen['bewertung']['bewertung_freischalten'],
            $cParameter_arr['nSortierung']
        );
        $AktuellerArtikel->holehilfreichsteBewertung(Shop::getLanguageID());
    }
    if (!\Session\Frontend::getCustomer()->isLoggedIn()) {
        Shop::Container()->getAlertService()->addAlert(
            Alert::TYPE_DANGER,
            Shop::Lang()->get('loginFirst', 'product rating'),
            'loginFirst',
            ['showInAlertListTemplate' => false]
        );
        $ratingAllowed = false;
    } elseif (pruefeKundeArtikelGekauft($AktuellerArtikel->kArtikel, $_SESSION['Kunde']->kKunde) === false) {
        Shop::Container()->getAlertService()->addAlert(
            Alert::TYPE_DANGER,
            Shop::Lang()->get('productNotBuyed', 'product rating'),
            'productNotBuyed',
            ['showInAlertListTemplate' => false]
        );
        $ratingAllowed = false;
    }

    Shop::Smarty()->assign(
        'BereitsBewertet',
        pruefeKundeArtikelBewertet(
            $AktuellerArtikel->kArtikel,
            $_SESSION['Kunde']->kKunde
        )
    )
        ->assign('Artikel', $AktuellerArtikel)
        ->assign('ratingAllowed', $ratingAllowed)
        ->assign(
            'oBewertung',
            Shop::Container()->getDB()->select(
                'tbewertung',
                ['kArtikel', 'kKunde'],
                [$AktuellerArtikel->kArtikel, \Session\Frontend::getCustomer()->getID()]
            )
        );

    require PFAD_ROOT . PFAD_INCLUDES . 'letzterInclude.php';
    Shop::Smarty()->display('productdetails/review_form.tpl');
} else {
    header('Location: ' . Shop::getURL() . '/', true, 303);
    exit;
}

require PFAD_ROOT . PFAD_INCLUDES . 'profiler_inc.php';
