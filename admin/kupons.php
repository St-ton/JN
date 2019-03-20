<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Checkout\Kupon;
use JTL\Shop;
use JTL\Sprache;
use JTL\Helpers\Text;
use JTL\Pagination\Filter;
use JTL\Pagination\Pagination;
use JTL\Pagination\Operation;
use JTL\DB\ReturnType;

require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('ORDER_COUPON_VIEW', true, true);
/** @global \JTL\Smarty\JTLSmarty $smarty */
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'toolsajax_inc.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'kupons_inc.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'csv_exporter_inc.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'csv_importer_inc.php';

$action       = '';
$tab          = Kupon::TYPE_STANDARD;
$oSprache_arr = Sprache::getAllLanguages();
$oKupon       = null;
$alertHelper  = Shop::Container()->getAlertService();
$res          = handleCsvImportAction('kupon', function ($obj, &$importDeleteDone, $importType = 2) {
    if ($importType === 0 && $importDeleteDone === false) {
        Shop::Container()->getDB()->query('TRUNCATE TABLE tkupon', ReturnType::AFFECTED_ROWS);
        Shop::Container()->getDB()->query('TRUNCATE TABLE tkuponsprache', ReturnType::AFFECTED_ROWS);
        $importDeleteDone = true;
    }

    $couponNames = [];

    foreach (get_object_vars($obj) as $key => $val) {
        if (mb_strpos($key, 'cName_') === 0) {
            $couponNames[mb_substr($key, 6)] = $val;
            unset($obj->$key);
        }
    }

    if (isset($obj->cCode)
        && Shop::Container()->getDB()->select('tkupon', 'cCode', $obj->cCode) !== null
    ) {
        return false;
    }

    unset($obj->dLastUse);
    $kKupon = Shop::Container()->getDB()->insert('tkupon', $obj);

    if ($kKupon === 0) {
        return false;
    }

    foreach ($couponNames as $key => $val) {
        $res = Shop::Container()->getDB()->insert(
            'tkuponsprache',
            (object)['kKupon' => $kKupon, 'cISOSprache' => $key, 'cName' => $val]
        );

        if ($res === 0) {
            return false;
        }
    }

    return true;
});

if ($res > 0) {
    $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorImportCSV') . ' ' . __('errorImportRow'), 'errorImportCSV');
} elseif ($res === 0) {
    $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successImportCSV'), 'successImportCSV');
}

// Aktion ausgeloest?
if (Form::validateToken()) {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'speichern') {
            // Kupon speichern
            $action = 'speichern';
        } elseif ($_POST['action'] === 'loeschen') {
            // Kupons loeschen
            $action = 'loeschen';
        }
    } elseif (isset($_GET['kKupon']) && Request::verifyGPCDataInt('kKupon') >= 0) {
        // Kupon bearbeiten
        $action = 'bearbeiten';
    }
}

// Aktion behandeln
if ($action === 'bearbeiten') {
    // Kupon bearbeiten
    $kKupon = isset($_GET['kKupon']) ? (int)$_GET['kKupon'] : (int)$_POST['kKuponBearbeiten'];
    if ($kKupon > 0) {
        $oKupon = getCoupon($kKupon);
    } else {
        $oKupon = createNewCoupon($_REQUEST['cKuponTyp']);
    }
} elseif ($action === 'speichern') {
    // Kupon speichern
    $oKupon       = createCouponFromInput();
    $couponErrors = validateCoupon($oKupon);
    if (count($couponErrors) > 0) {
        // Es gab Fehler bei der Validierung => weiter bearbeiten
        $errorMessage = __('errorCheckInput') . ':<ul>';

        foreach ($couponErrors as $couponError) {
            $errorMessage .= '<li>' . $couponError . '</li>';
        }

        $errorMessage .= '</ul>';
        $action        = 'bearbeiten';
        $alertHelper->addAlert(Alert::TYPE_ERROR, $errorMessage, 'errorCheckInput');
        augmentCoupon($oKupon);
    } elseif (saveCoupon($oKupon, $oSprache_arr) > 0) {// Validierung erfolgreich => Kupon speichern
        // erfolgreich gespeichert => evtl. Emails versenden
        if (isset($_POST['informieren'])
            && $_POST['informieren'] === 'Y'
            && ($oKupon->cKuponTyp === Kupon::TYPE_STANDARD || $oKupon->cKuponTyp === Kupon::TYPE_SHIPPING)
            && $oKupon->cAktiv === 'Y'
        ) {
            informCouponCustomers($oKupon);
        }
        $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successCouponSave'), 'successCouponSave');
    } else {
        $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorCouponSave'), 'errorCouponSave');
    }
} elseif ($action === 'loeschen') {
    // Kupons loeschen
    if (isset($_POST['kKupon_arr']) && is_array($_POST['kKupon_arr']) && count($_POST['kKupon_arr']) > 0) {
        $kKupon_arr = array_map('\intval', $_POST['kKupon_arr']);
        if (loescheKupons($kKupon_arr)) {
            $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successCouponDelete'), 'successCouponDelete');
        } else {
            $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorCouponDelete'), 'errorCouponDelete');
        }
    } else {
        $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorAtLeastOneCoupon'), 'errorAtLeastOneCoupon');
    }
}

// Seite ausgeben
if ($action === 'bearbeiten') {
    // Seite: Bearbeiten
    $oSteuerklasse_arr = Shop::Container()->getDB()->query(
        'SELECT kSteuerklasse, cName FROM tsteuerklasse',
        ReturnType::ARRAY_OF_OBJECTS
    );
    $oKundengruppe_arr = Shop::Container()->getDB()->query(
        'SELECT kKundengruppe, cName FROM tkundengruppe',
        ReturnType::ARRAY_OF_OBJECTS
    );
    $oHersteller_arr   = getManufacturers($oKupon->cHersteller);
    $oKategorie_arr    = getCategories($oKupon->cKategorien);
    $kKunde_arr        = array_filter(
        Text::parseSSK($oKupon->cKunden),
        function ($kKunde) {
            return (int)$kKunde > 0;
        }
    );
    if ($oKupon->kKupon > 0) {
        $oKuponName_arr = getCouponNames((int)$oKupon->kKupon);
    } else {
        $oKuponName_arr = [];
        foreach ($oSprache_arr as $oSprache) {
            $postVarName                     = 'cName_' . $oSprache->cISO;
            $oKuponName_arr[$oSprache->cISO] = (isset($_POST[$postVarName]) && $_POST[$postVarName] !== '')
                ? $_POST[$postVarName]
                : $oKupon->cName;
        }
    }

    $smarty->assign('oSteuerklasse_arr', $oSteuerklasse_arr)
           ->assign('oKundengruppe_arr', $oKundengruppe_arr)
           ->assign('oHersteller_arr', $oHersteller_arr)
           ->assign('oKategorie_arr', $oKategorie_arr)
           ->assign('kKunde_arr', $kKunde_arr)
           ->assign('oSprache_arr', $oSprache_arr)
           ->assign('oKuponName_arr', $oKuponName_arr)
           ->assign('oKupon', $oKupon);
} else {
    // Seite: Uebersicht
    if (Request::hasGPCData('tab')) {
        $tab = Request::verifyGPDataString('tab');
    } elseif (Request::hasGPCData('cKuponTyp')) {
        $tab = Request::verifyGPDataString('cKuponTyp');
    }

    deactivateOutdatedCoupons();
    deactivateExhaustedCoupons();

    $filterStandard = new Filter(Kupon::TYPE_STANDARD);
    $filterStandard->addTextfield('Name', 'cName');
    $filterStandard->addTextfield('Code', 'cCode');
    $activeSelection = $filterStandard->addSelectfield('Status', 'cAktiv');
    $activeSelection->addSelectOption('alle', '', Operation::CUSTOM);
    $activeSelection->addSelectOption('aktiv', 'Y', Operation::EQUALS);
    $activeSelection->addSelectOption('inaktiv', 'N', Operation::EQUALS);
    $filterStandard->assemble();

    $filterVersand = new Filter(Kupon::TYPE_SHIPPING);
    $filterVersand->addTextfield('Name', 'cName');
    $filterVersand->addTextfield('Code', 'cCode');
    $activeSelection = $filterVersand->addSelectfield('Status', 'cAktiv');
    $activeSelection->addSelectOption('alle', '', Operation::CUSTOM);
    $activeSelection->addSelectOption('aktiv', 'Y', Operation::EQUALS);
    $activeSelection->addSelectOption('inaktiv', 'N', Operation::EQUALS);
    $filterVersand->assemble();

    $filterNeukunden = new Filter(Kupon::TYPE_NEWCUSTOMER);
    $filterNeukunden->addTextfield('Name', 'cName');
    $activeSelection = $filterNeukunden->addSelectfield('Status', 'cAktiv');
    $activeSelection->addSelectOption('alle', '', Operation::CUSTOM);
    $activeSelection->addSelectOption('aktiv', 'Y', Operation::EQUALS);
    $activeSelection->addSelectOption('inaktiv', 'N', Operation::EQUALS);
    $filterNeukunden->assemble();

    $sortByOptions = [
        ['cName', 'Name'],
        ['cCode', 'Code'],
        ['nVerwendungenBisher', 'Verwendungen'],
        ['dLastUse', 'Zuletzt verwendet']
    ];


    $nKuponStandardCount  = getCouponCount(Kupon::TYPE_STANDARD, $filterStandard->getWhereSQL());
    $nKuponVersandCount   = getCouponCount(Kupon::TYPE_SHIPPING, $filterVersand->getWhereSQL());
    $nKuponNeukundenCount = getCouponCount(Kupon::TYPE_NEWCUSTOMER, $filterNeukunden->getWhereSQL());
    $nKuponStandardTotal  = getCouponCount(Kupon::TYPE_STANDARD);
    $nKuponVersandTotal   = getCouponCount(Kupon::TYPE_SHIPPING);
    $nKuponNeukundenTotal = getCouponCount(Kupon::TYPE_NEWCUSTOMER);

    handleCsvExportAction(
        Kupon::TYPE_STANDARD,
        Kupon::TYPE_STANDARD . '.csv',
        function () use ($filterStandard) {
            return getExportableCoupons(Kupon::TYPE_STANDARD, $filterStandard->getWhereSQL());
        },
        [],
        ['kKupon']
    );
    handleCsvExportAction(
        Kupon::TYPE_SHIPPING,
        Kupon::TYPE_SHIPPING . '.csv',
        function () use ($filterVersand) {
            return getExportableCoupons(Kupon::TYPE_SHIPPING, $filterVersand->getWhereSQL());
        },
        [],
        ['kKupon']
    );
    handleCsvExportAction(
        Kupon::TYPE_NEWCUSTOMER,
        Kupon::TYPE_NEWCUSTOMER . '.csv',
        function () use ($filterNeukunden) {
            return getExportableCoupons(Kupon::TYPE_NEWCUSTOMER, $filterNeukunden->getWhereSQL());
        },
        [],
        ['kKupon']
    );
    $paginationStandard  = (new Pagination(Kupon::TYPE_STANDARD))
        ->setSortByOptions($sortByOptions)
        ->setItemCount($nKuponStandardCount)
        ->assemble();
    $paginationVersand   = (new Pagination(Kupon::TYPE_SHIPPING))
        ->setSortByOptions($sortByOptions)
        ->setItemCount($nKuponVersandCount)
        ->assemble();
    $paginationNeukunden = (new Pagination(Kupon::TYPE_NEWCUSTOMER))
        ->setSortByOptions($sortByOptions)
        ->setItemCount($nKuponNeukundenCount)
        ->assemble();

    $oKuponStandard_arr  = getCoupons(
        Kupon::TYPE_STANDARD,
        $filterStandard->getWhereSQL(),
        $paginationStandard->getOrderSQL(),
        $paginationStandard->getLimitSQL()
    );
    $oKuponVersand_arr   = getCoupons(
        Kupon::TYPE_SHIPPING,
        $filterVersand->getWhereSQL(),
        $paginationVersand->getOrderSQL(),
        $paginationVersand->getLimitSQL()
    );
    $oKuponNeukunden_arr = getCoupons(
        Kupon::TYPE_NEWCUSTOMER,
        $filterNeukunden->getWhereSQL(),
        $paginationNeukunden->getOrderSQL(),
        $paginationNeukunden->getLimitSQL()
    );

    $smarty->assign('tab', $tab)
           ->assign('oFilterStandard', $filterStandard)
           ->assign('oFilterVersand', $filterVersand)
           ->assign('oFilterNeukunden', $filterNeukunden)
           ->assign('oPaginationStandard', $paginationStandard)
           ->assign('oPaginationVersandkupon', $paginationVersand)
           ->assign('oPaginationNeukundenkupon', $paginationNeukunden)
           ->assign('oKuponStandard_arr', $oKuponStandard_arr)
           ->assign('oKuponVersandkupon_arr', $oKuponVersand_arr)
           ->assign('oKuponNeukundenkupon_arr', $oKuponNeukunden_arr)
           ->assign('nKuponStandardCount', $nKuponStandardTotal)
           ->assign('nKuponVersandCount', $nKuponVersandTotal)
           ->assign('nKuponNeukundenCount', $nKuponNeukundenTotal);
}

$smarty->assign('action', $action)
       ->assign('couponTypes', Kupon::getCouponTypes())
       ->display('kupons.tpl');
