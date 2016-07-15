<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once dirname(__FILE__) . '/includes/admininclude.php';

$oAccount->permission('ORDER_COUPON_VIEW', true, true);

require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'toolsajax_inc.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'kupons_inc.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'blaetternavi.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'filtertools.php';

$cHinweis        = '';
$cFehler         = '';
$action          = '';
$tab             = 'standard';
$oSprache_arr    = gibAlleSprachen();
$nAnzahlProSeite = 2;
$oKupon          = null;

// Aktion ausgeloest?

if (validateToken()) {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'speichern') {
            // Kupon speichern
            $action = 'speichern';
        } elseif ($_POST['action'] === 'loeschen') {
            // Kupons loeschen
            $action = 'loeschen';
        } elseif ($_POST['action'] === 'filter') {
            // Filterung gewuenscht

        }
    } elseif (isset($_POST['kKuponBearbeiten'])) {
        // Kupon bearbeiten
        $action = 'bearbeiten';
    }
}

// Aktion behandeln

if ($action === 'bearbeiten') {
    // Kupon bearbeiten
    $kKupon    = (int)$_POST['kKuponBearbeiten'];
    $cKuponTyp = $_POST['cKuponTyp'];

    if ($kKupon > 0) {
        $oKupon = getCoupon($kKupon);
    } else {
        $oKupon = createNewCoupon($cKuponTyp);
    }
} elseif ($action === 'speichern') {
    // Kupon speichern
    $oKupon      = createCouponFromInput();
    $cFehler_arr = validateCoupon($oKupon);

    if (count($cFehler_arr) > 0) {
        // Es gab Fehler bei der Validierung => weiter bearbeiten
        $cFehler = 'Bitte &uuml;berpr&uuml;fen Sie folgende Eingaben:<ul>';

        foreach ($cFehler_arr as $fehler) {
            $cFehler .= '<li>' . $fehler . '</li>';
        }

        $cFehler .= '</ul>';
        $action   = 'bearbeiten';
        augmentCoupon($oKupon);
    } else {
        // Validierung erfolgreich => Kupon speichern
        if (saveCoupon($oKupon, $oSprache_arr) > 0) {
            // erfolgreich gespeichert => evtl. Emails versenden
            if (isset($_POST['informieren']) && $_POST['informieren'] === 'Y') {
                informCouponCustomers($oKupon);
            }
            $cHinweis = 'Der Kupon wurde erfolgreich gespeichert.';
        } else {
            $cFehler = 'Der Kupon konnte nicht gespeichert werden.';
        }
    }
} elseif ($action === 'loeschen') {
    // Kupons loeschen
    if (isset($_POST['kKupon_arr']) && is_array($_POST['kKupon_arr']) && count($_POST['kKupon_arr']) > 0) {
        $kKupon_arr = array_map('intval', $_POST['kKupon_arr']);
        if (loescheKupons($kKupon_arr)) {
            $cHinweis = 'Ihre markierten Kupons wurden erfolgreich gel&ouml;scht.';
        } else {
            $cFehler = 'Fehler: Ein oder mehrere Kupons konnten nicht gel&ouml;scht werden.';
        }
    } else {
        $cFehler = 'Fehler: Bitte markieren Sie mindestens einen Kupon.';
    }
}

// Seite ausgeben

if ($action === 'bearbeiten') {
    // Seite: Bearbeiten
    $oSteuerklasse_arr = Shop::DB()->query("SELECT kSteuerklasse, cName FROM tsteuerklasse", 2);
    $oKundengruppe_arr = Shop::DB()->query("SELECT kKundengruppe, cName FROM tkundengruppe", 2);
    $oKategorie_arr    = getCategories($oKupon->cKategorien);
    $oKunde_arr        = getCustomers($oKupon->cKunden);
    $oKuponName_arr    = getCouponNames((int)$oKupon->kKupon);

    $smarty->assign('oSteuerklasse_arr', $oSteuerklasse_arr)
        ->assign('oKundengruppe_arr', $oKundengruppe_arr)
        ->assign('oKategorie_arr', $oKategorie_arr)
        ->assign('oKunde_arr', $oKunde_arr)
        ->assign('oSprache_arr', $oSprache_arr)
        ->assign('oKuponName_arr', $oKuponName_arr)
        ->assign('oKupon', $oKupon);
} else {
    // Seite: Uebersicht
    if (hasGPCDataInteger('tab')) {
        $tab = verifyGPDataString('tab');
    } elseif (hasGPCDataInteger('cKuponTyp')) {
        $tab = verifyGPDataString('cKuponTyp');
    }

    $oFilter = createFilter();
    addFilterTextfield($oFilter, 'Name', 'cName', false);
    addFilterTextfield($oFilter, 'Code', 'cCode', false);
    addFilterSelect($oFilter, 'Status', 'cAktiv',
        array('alle', 'aktiv', 'inaktiv'),
        array("", "= 'Y'", "= 'N'"));
    assembleFilter($oFilter);

    $oBlaetterNaviConf           = baueBlaetterNaviGetterSetter(3, $nAnzahlProSeite);
    $oKuponStandard_arr          = getCoupons('standard', $oBlaetterNaviConf->cSQL1, 'kKupon', $oFilter->cWhereSQL);
    $oKuponVersandkupon_arr      = getCoupons('versandkupon', $oBlaetterNaviConf->cSQL2, 'kKupon', $oFilter->cWhereSQL);
    $oKuponNeukundenkupon_arr    = getCoupons('neukundenkupon', $oBlaetterNaviConf->cSQL3, 'kKupon', $oFilter->cWhereSQL);
    /*
    $nStandardAnzahl             = getCouponCount('standard');
    $nVersandkuponAnzahl         = getCouponCount('versandkupon');
    $nNeukundenkuponAnzahl       = getCouponCount('neukundenkupon');
    */
    $nStandardAnzahl             = getCouponCount('standard', $oFilter->cWhereSQL);
    $nVersandkuponAnzahl         = getCouponCount('versandkupon', $oFilter->cWhereSQL);
    $nNeukundenkuponAnzahl       = getCouponCount('neukundenkupon', $oFilter->cWhereSQL);
    $oBlaetterNaviStandard       = baueBlaetterNavi($oBlaetterNaviConf->nAktuelleSeite1, $nStandardAnzahl, $nAnzahlProSeite);
    $oBlaetterNaviVersandkupon   = baueBlaetterNavi($oBlaetterNaviConf->nAktuelleSeite2, $nVersandkuponAnzahl, $nAnzahlProSeite);
    $oBlaetterNaviNeukundenkupon = baueBlaetterNavi($oBlaetterNaviConf->nAktuelleSeite3, $nNeukundenkuponAnzahl, $nAnzahlProSeite);

    $smarty->assign('tab', $tab)
        ->assign('oFilter', $oFilter)
        ->assign('oBlaetterNaviStandard', $oBlaetterNaviStandard)
        ->assign('oBlaetterNaviVersandkupon', $oBlaetterNaviVersandkupon)
        ->assign('oBlaetterNaviNeukundenkupon', $oBlaetterNaviNeukundenkupon)
        ->assign('oKuponStandard_arr', $oKuponStandard_arr)
        ->assign('oKuponVersandkupon_arr', $oKuponVersandkupon_arr)
        ->assign('oKuponNeukundenkupon_arr', $oKuponNeukundenkupon_arr);
}

$smarty->assign('action', $action)
    ->assign('cHinweis', $cHinweis)
    ->assign('cFehler', $cFehler)
    ->display('kupons.tpl');
