<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once dirname(__FILE__) . '/includes/admininclude.php';

$oAccount->permission('ORDER_COUPON_VIEW', true, true);
/** @global JTLSmarty $smarty */
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'toolsajax_inc.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'kupons_inc.php';

$cHinweis     = '';
$cFehler      = '';
$action       = '';
$tab          = 'standard';
$oSprache_arr = gibAlleSprachen();
$oKupon       = null;

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

// Seite ausgeben

if ($action === 'bearbeiten') {
    // Seite: Bearbeiten
    $oSteuerklasse_arr = Shop::DB()->query("SELECT kSteuerklasse, cName FROM tsteuerklasse", 2);
    $oKundengruppe_arr = Shop::DB()->query("SELECT kKundengruppe, cName FROM tkundengruppe", 2);
    $oHersteller_arr   = getManufacturers($oKupon->cHersteller);
    $oKategorie_arr    = getCategories($oKupon->cKategorien);
    $oKunde_arr        = getCustomers($oKupon->cKunden);
    if ($oKupon->kKupon > 0) {
        $oKuponName_arr = getCouponNames((int)$oKupon->kKupon);
    } else {
        $oKuponName_arr = [];
        foreach ($oSprache_arr as $oSprache) {
            $oKuponName_arr[$oSprache->cISO] =
                (isset($_POST['cName_' . $oSprache->cISO]) && $_POST['cName_' . $oSprache->cISO] !== '')
                    ? $_POST['cName_' . $oSprache->cISO]
                    : $oKupon->cName;
        }
    }

    $smarty->assign('oSteuerklasse_arr', $oSteuerklasse_arr)
        ->assign('oKundengruppe_arr', $oKundengruppe_arr)
        ->assign('oHersteller_arr', $oHersteller_arr)
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

    deactivateOutdatedCoupons();
    deactivateExhaustedCoupons();

    $oFilterStandard = new Filter('standard');
    $oFilterStandard->addTextfield('Name', 'cName');
    $oFilterStandard->addTextfield('Code', 'cCode');
    $oAktivSelect = $oFilterStandard->addSelectfield('Status', 'cAktiv');
    $oAktivSelect->addSelectOption('alle', '', 0);
    $oAktivSelect->addSelectOption('aktiv', 'Y', 4);
    $oAktivSelect->addSelectOption('inaktiv', 'N', 4);
    $oFilterStandard->assemble();

    $oFilterVersand = new Filter('versand');
    $oFilterVersand->addTextfield('Name', 'cName');
    $oFilterVersand->addTextfield('Code', 'cCode');
    $oAktivSelect = $oFilterVersand->addSelectfield('Status', 'cAktiv');
    $oAktivSelect->addSelectOption('alle', '', 0);
    $oAktivSelect->addSelectOption('aktiv', 'Y', 4);
    $oAktivSelect->addSelectOption('inaktiv', 'N', 4);
    $oFilterVersand->assemble();

    $oFilterNeukunden = new Filter('neukunden');
    $oFilterNeukunden->addTextfield('Name', 'cName');
    $oAktivSelect = $oFilterNeukunden->addSelectfield('Status', 'cAktiv');
    $oAktivSelect->addSelectOption('alle', '', 0);
    $oAktivSelect->addSelectOption('aktiv', 'Y', 4);
    $oAktivSelect->addSelectOption('inaktiv', 'N', 4);
    $oFilterNeukunden->assemble();

    $cSortByOption_arr    = [['cName', 'Name'], ['cCode', 'Code'], ['nVerwendungenBisher', 'Verwendungen'], ['dLastUse', 'Zuletzt verwendet']];
    $oKuponStandard_arr   = getCoupons('standard', $oFilterStandard->getWhereSQL());
    $oKuponVersand_arr    = getCoupons('versandkupon', $oFilterVersand->getWhereSQL());
    $oKuponNeukunden_arr  = getCoupons('neukundenkupon', $oFilterNeukunden->getWhereSQL());
    $nKuponStandardCount  = getCouponCount('standard');
    $nKuponVersandCount   = getCouponCount('versandkupon');
    $nKuponNeukundenCount = getCouponCount('neukundenkupon');

    $oPaginationStandard = (new Pagination('standard'))
        ->setSortByOptions($cSortByOption_arr)
        ->setItemArray($oKuponStandard_arr)
        ->assemble();

    $oPaginationVersand = (new Pagination('versand'))
        ->setSortByOptions($cSortByOption_arr)
        ->setItemArray($oKuponVersand_arr)
        ->assemble();

    $oPaginationNeukunden = (new Pagination('neukunden'))
        ->setSortByOptions($cSortByOption_arr)
        ->setItemArray($oKuponNeukunden_arr)
        ->assemble();

    $oKuponStandard_arr  = $oPaginationStandard->getPageItems();
    $oKuponVersand_arr   = $oPaginationVersand->getPageItems();
    $oKuponNeukunden_arr = $oPaginationNeukunden->getPageItems();

    $smarty->assign('tab', $tab)
        ->assign('oFilterStandard', $oFilterStandard)
        ->assign('oFilterVersand', $oFilterVersand)
        ->assign('oFilterNeukunden', $oFilterNeukunden)
        ->assign('oPaginationStandard', $oPaginationStandard)
        ->assign('oPaginationVersandkupon', $oPaginationVersand)
        ->assign('oPaginationNeukundenkupon', $oPaginationNeukunden)
        ->assign('oKuponStandard_arr', $oKuponStandard_arr)
        ->assign('oKuponVersandkupon_arr', $oKuponVersand_arr)
        ->assign('oKuponNeukundenkupon_arr', $oKuponNeukunden_arr)
        ->assign('nKuponStandardCount', $nKuponStandardCount)
        ->assign('nKuponVersandCount', $nKuponVersandCount)
        ->assign('nKuponNeukundenCount', $nKuponNeukundenCount);
}

$smarty->assign('action', $action)
    ->assign('cHinweis', $cHinweis)
    ->assign('cFehler', $cFehler)
    ->display('kupons.tpl');
