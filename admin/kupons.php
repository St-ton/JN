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

$cHinweis        = '';
$cFehler         = '';
$action          = '';
$tab             = 'standard';
$oSprache_arr    = gibAlleSprachen();
$nAnzahlProSeite = 100;
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
        }
    } elseif (isset($_POST['kKuponBearbeiten'])) {
        // Kupon bearbeiten
        $action = 'bearbeiten';
    }
}

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
            if (isset($_POST['informieren']) && $_POST['informieren'] === 'Y' && ($oKupon->cKuponTyp === 'standard' || $oKupon->cKuponTyp === 'versandkupon') && $oKupon->cAktiv === 'Y') {
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

if ($action === 'bearbeiten') {
    // Seite: Bearbeiten
    $oSteuerklasse_arr = Shop::DB()->query("SELECT kSteuerklasse, cName FROM tsteuerklasse", 2);
    $oKundengruppe_arr = Shop::DB()->query("SELECT kKundengruppe, cName FROM tkundengruppe", 2);
    $oKategorie_arr    = getCategories($oKupon->cKategorien);
    $oKunde_arr        = getCustomers($oKupon->cKunden);
    if ($oKupon->kKupon > 0) {
        $oKuponName_arr = getCouponNames((int)$oKupon->kKupon);
    } else {
        foreach ($oSprache_arr as $oSprache) {
            $oKuponName_arr[$oSprache->cISO] =
                (isset($_POST['cName_' . $oSprache->cISO]) && $_POST['cName_' . $oSprache->cISO] !== '')
                    ? $_POST['cName_' . $oSprache->cISO]
                    : $oKupon->cName;
        }
    }

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

    $nStandardAnzahl             = getCouponCount('standard');
    $nVersandkuponAnzahl         = getCouponCount('versandkupon');
    $nNeukundenkuponAnzahl       = getCouponCount('neukundenkupon');
    $oBlaetterNaviConf           = baueBlaetterNaviGetterSetter(3, $nAnzahlProSeite);
    $oBlaetterNaviStandard       = baueBlaetterNavi($oBlaetterNaviConf->nAktuelleSeite1, $nStandardAnzahl, $nAnzahlProSeite);
    $oBlaetterNaviVersandkupon   = baueBlaetterNavi($oBlaetterNaviConf->nAktuelleSeite2, $nVersandkuponAnzahl, $nAnzahlProSeite);
    $oBlaetterNaviNeukundenkupon = baueBlaetterNavi($oBlaetterNaviConf->nAktuelleSeite3, $nNeukundenkuponAnzahl, $nAnzahlProSeite);
    $oKuponStandard_arr          = getCoupons('standard', $oBlaetterNaviConf->cSQL1);
    $oKuponVersandkupon_arr      = getCoupons('versandkupon', $oBlaetterNaviConf->cSQL2);
    $oKuponNeukundenkupon_arr    = getCoupons('neukundenkupon', $oBlaetterNaviConf->cSQL3);

    $smarty->assign('tab', $tab)
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
