<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once dirname(__FILE__) . '/includes/admininclude.php';

$oAccount->permission('ORDER_COUPON_VIEW', true, true);

require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'toolsajax_inc.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'kupons_inc.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'pagination.php';
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
    $oAktivSelect = addFilterSelect($oFilter, 'Status', 'cAktiv');
    addFilterSelectOption($oAktivSelect, 'alle', "");
    addFilterSelectOption($oAktivSelect, 'aktiv', "= 'Y'");
    addFilterSelectOption($oAktivSelect, 'inaktiv', "= 'N'");
    assembleFilter($oFilter);

    $oKuponStandard_arr          = getCoupons('standard', 'kKupon', $oFilter->cWhereSQL);
    $oKuponVersandkupon_arr      = getCoupons('versandkupon', 'kKupon', $oFilter->cWhereSQL);
    $oKuponNeukundenkupon_arr    = getCoupons('neukundenkupon', 'kKupon', $oFilter->cWhereSQL);

    $nItemsPerPageOption_arr = [1,2,5,10];
    $cSortByOption_arr = [['cName', 'Name'], ['cCode', 'Code'], ['nVerwendungenBisher', 'Verwendungen']];
    $oPaginationStandard       = createPagination('standard', $oKuponStandard_arr, $nItemsPerPageOption_arr, $cSortByOption_arr);
    $oPaginationVersandkupon   = createPagination('versandkupon', $oKuponVersandkupon_arr, $nItemsPerPageOption_arr, $cSortByOption_arr);
    $oPaginationNeukundenkupon = createPagination('neukundenkupon', $oKuponNeukundenkupon_arr, $nItemsPerPageOption_arr, $cSortByOption_arr);

    $oPaginationStandard->cAddGetVar_arr['tab'] = 'standard';
    $oPaginationVersandkupon->cAddGetVar_arr['tab'] = 'versandkupon';
    $oPaginationNeukundenkupon->cAddGetVar_arr['tab'] = 'neukundenkupon';

    $smarty->assign('tab', $tab)
        ->assign('oFilter', $oFilter)
        ->assign('oPaginationStandard', $oPaginationStandard)
        ->assign('oPaginationVersandkupon', $oPaginationVersandkupon)
        ->assign('oPaginationNeukundenkupon', $oPaginationNeukundenkupon)
        ->assign('oKuponStandard_arr', $oPaginationStandard->oPageItem_arr)
        ->assign('oKuponVersandkupon_arr', $oPaginationVersandkupon->oPageItem_arr)
        ->assign('oKuponNeukundenkupon_arr', $oPaginationNeukundenkupon->oPageItem_arr);
}

$smarty->assign('action', $action)
    ->assign('cHinweis', $cHinweis)
    ->assign('cFehler', $cFehler)
    ->display('kupons.tpl');
