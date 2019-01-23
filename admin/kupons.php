<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use Helpers\Form;
use Helpers\Request;
use Pagination\Filter;
use Pagination\Pagination;

require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('ORDER_COUPON_VIEW', true, true);
/** @global Smarty\JTLSmarty $smarty */
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'toolsajax_inc.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'kupons_inc.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'csv_exporter_inc.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'csv_importer_inc.php';

$cHinweis         = '';
$cFehler          = '';
$action           = '';
$tab              = Kupon::TYPE_STANDARD;
$oSprache_arr     = Sprache::getAllLanguages();
$oKupon           = null;
$importDeleteDone = false;
$res              = handleCsvImportAction('kupon', function ($obj, $importType = 2) {
    global $importDeleteDone;

    if ($importType === 0 && $importDeleteDone === false) {
        Shop::Container()->getDB()->query('TRUNCATE TABLE tkupon', \DB\ReturnType::AFFECTED_ROWS);
        Shop::Container()->getDB()->query('TRUNCATE TABLE tkuponsprache', \DB\ReturnType::AFFECTED_ROWS);
        $importDeleteDone = true;
    }

    $couponNames = [];

    foreach (get_object_vars($obj) as $key => $val) {
        if (strpos($key, 'cName_') === 0) {
            $couponNames[substr($key, 6)] = $val;
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
    $cFehler  = __('errorImportCSV');
    $cFehler .= __('errorImportRow');
} elseif ($res === 0) {
    $cHinweis = __('successImportCSV');
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
    $oKupon      = createCouponFromInput();
    $cFehler_arr = validateCoupon($oKupon);
    if (count($cFehler_arr) > 0) {
        // Es gab Fehler bei der Validierung => weiter bearbeiten
        $cFehler = __('checkInput') . ':<ul>';

        foreach ($cFehler_arr as $fehler) {
            $cFehler .= '<li>' . $fehler . '</li>';
        }

        $cFehler .= '</ul>';
        $action   = 'bearbeiten';
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
        $cHinweis = __('successCouponSave');
    } else {
        $cFehler = __('errorCouponSave');
    }
} elseif ($action === 'loeschen') {
    // Kupons loeschen
    if (isset($_POST['kKupon_arr']) && is_array($_POST['kKupon_arr']) && count($_POST['kKupon_arr']) > 0) {
        $kKupon_arr = array_map('\intval', $_POST['kKupon_arr']);
        if (loescheKupons($kKupon_arr)) {
            $cHinweis = __('successCouponDelete');
        } else {
            $cFehler = __('errorCouponDelete');
        }
    } else {
        $cFehler = __('errorAtLeastOneCoupon');
    }
}

// Seite ausgeben
if ($action === 'bearbeiten') {
    // Seite: Bearbeiten
    $oSteuerklasse_arr = Shop::Container()->getDB()->query(
        'SELECT kSteuerklasse, cName FROM tsteuerklasse',
        \DB\ReturnType::ARRAY_OF_OBJECTS
    );
    $oKundengruppe_arr = Shop::Container()->getDB()->query(
        'SELECT kKundengruppe, cName FROM tkundengruppe',
        \DB\ReturnType::ARRAY_OF_OBJECTS
    );
    $oHersteller_arr   = getManufacturers($oKupon->cHersteller);
    $oKategorie_arr    = getCategories($oKupon->cKategorien);
    $kKunde_arr        = array_filter(
        StringHandler::parseSSK($oKupon->cKunden),
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
    $activeSelection->addSelectOption('alle', '', \Pagination\Operation::CUSTOM);
    $activeSelection->addSelectOption('aktiv', 'Y', \Pagination\Operation::EQUALS);
    $activeSelection->addSelectOption('inaktiv', 'N', \Pagination\Operation::EQUALS);
    $filterStandard->assemble();

    $filterVersand = new Filter(Kupon::TYPE_SHIPPING);
    $filterVersand->addTextfield('Name', 'cName');
    $filterVersand->addTextfield('Code', 'cCode');
    $activeSelection = $filterVersand->addSelectfield('Status', 'cAktiv');
    $activeSelection->addSelectOption('alle', '', \Pagination\Operation::CUSTOM);
    $activeSelection->addSelectOption('aktiv', 'Y', \Pagination\Operation::EQUALS);
    $activeSelection->addSelectOption('inaktiv', 'N', \Pagination\Operation::EQUALS);
    $filterVersand->assemble();

    $filterNeukunden = new Filter(Kupon::TYPE_NEWCUSTOMER);
    $filterNeukunden->addTextfield('Name', 'cName');
    $activeSelection = $filterNeukunden->addSelectfield('Status', 'cAktiv');
    $activeSelection->addSelectOption('alle', '', \Pagination\Operation::CUSTOM);
    $activeSelection->addSelectOption('aktiv', 'Y', \Pagination\Operation::EQUALS);
    $activeSelection->addSelectOption('inaktiv', 'N', \Pagination\Operation::EQUALS);
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
       ->assign('cHinweis', $cHinweis)
       ->assign('cFehler', $cFehler)
       ->assign('couponTypes', Kupon::getCouponTypes())
       ->display('kupons.tpl');
