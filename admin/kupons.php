<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use Helpers\FormHelper;
use Helpers\RequestHelper;
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
$tab              = 'standard';
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
    $cFehler  = 'Konnte CSV-Datei nicht vollständig importieren. ';
    $cFehler .= ($res === 1 ? '1 Zeile ist' : $res . ' Zeilen sind') . ' nicht importierbar.';
} elseif ($res === 0) {
    $cHinweis = 'CSV-Datei wurde erfolgreich importiert.';
}

// Aktion ausgeloest?
if (FormHelper::validateToken()) {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'speichern') {
            // Kupon speichern
            $action = 'speichern';
        } elseif ($_POST['action'] === 'loeschen') {
            // Kupons loeschen
            $action = 'loeschen';
        }
    } elseif (isset($_GET['kKupon']) && RequestHelper::verifyGPCDataInt('kKupon') >= 0) {
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
            && ($oKupon->cKuponTyp === 'standard' || $oKupon->cKuponTyp === 'versandkupon')
            && $oKupon->cAktiv === 'Y'
        ) {
            informCouponCustomers($oKupon);
        }
        $cHinweis = 'Der Kupon wurde erfolgreich gespeichert.';
    } else {
        $cFehler = 'Der Kupon konnte nicht gespeichert werden.';
    }
} elseif ($action === 'loeschen') {
    // Kupons loeschen
    if (isset($_POST['kKupon_arr']) && is_array($_POST['kKupon_arr']) && count($_POST['kKupon_arr']) > 0) {
        $kKupon_arr = array_map('\intval', $_POST['kKupon_arr']);
        if (loescheKupons($kKupon_arr)) {
            $cHinweis = 'Ihre markierten Kupons wurden erfolgreich gelöscht.';
        } else {
            $cFehler = 'Fehler: Ein oder mehrere Kupons konnten nicht gelöscht werden.';
        }
    } else {
        $cFehler = 'Fehler: Bitte markieren Sie mindestens einen Kupon.';
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
    if (RequestHelper::hasGPCData('tab')) {
        $tab = RequestHelper::verifyGPDataString('tab');
    } elseif (RequestHelper::hasGPCData('cKuponTyp')) {
        $tab = RequestHelper::verifyGPDataString('cKuponTyp');
    }

    deactivateOutdatedCoupons();
    deactivateExhaustedCoupons();

    $filterStandard = new Filter('standard');
    $filterStandard->addTextfield('Name', 'cName');
    $filterStandard->addTextfield('Code', 'cCode');
    $activeSelection = $filterStandard->addSelectfield('Status', 'cAktiv');
    $activeSelection->addSelectOption('alle', '', \Pagination\Operation::CUSTOM);
    $activeSelection->addSelectOption('aktiv', 'Y', \Pagination\Operation::EQUALS);
    $activeSelection->addSelectOption('inaktiv', 'N', \Pagination\Operation::EQUALS);
    $filterStandard->assemble();

    $filterVersand = new Filter('versand');
    $filterVersand->addTextfield('Name', 'cName');
    $filterVersand->addTextfield('Code', 'cCode');
    $activeSelection = $filterVersand->addSelectfield('Status', 'cAktiv');
    $activeSelection->addSelectOption('alle', '', \Pagination\Operation::CUSTOM);
    $activeSelection->addSelectOption('aktiv', 'Y', \Pagination\Operation::EQUALS);
    $activeSelection->addSelectOption('inaktiv', 'N', \Pagination\Operation::EQUALS);
    $filterVersand->assemble();

    $filterNeukunden = new Filter('neukunden');
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

    $nKuponStandardCount  = getCouponCount('standard', $filterStandard->getWhereSQL());
    $nKuponVersandCount   = getCouponCount('versandkupon', $filterVersand->getWhereSQL());
    $nKuponNeukundenCount = getCouponCount('neukundenkupon', $filterNeukunden->getWhereSQL());
    $nKuponStandardTotal  = getCouponCount('standard');
    $nKuponVersandTotal   = getCouponCount('versandkupon');
    $nKuponNeukundenTotal = getCouponCount('neukundenkupon');

    handleCsvExportAction(
        'standard',
        'standard.csv',
        function () use ($filterStandard) {
            return getExportableCoupons('standard', $filterStandard->getWhereSQL());
        },
        [],
        ['kKupon']
    );
    handleCsvExportAction(
        'versandkupon',
        'versandkupon.csv',
        function () use ($filterVersand) {
            return getExportableCoupons('versandkupon', $filterVersand->getWhereSQL());
        },
        [],
        ['kKupon']
    );
    handleCsvExportAction(
        'neukundenkupon',
        'neukundenkupon.csv',
        function () use ($filterNeukunden) {
            return getExportableCoupons('neukundenkupon', $filterNeukunden->getWhereSQL());
        },
        [],
        ['kKupon']
    );

    $paginationStandard  = (new Pagination('standard'))
        ->setSortByOptions($sortByOptions)
        ->setItemCount($nKuponStandardCount)
        ->assemble();
    $paginationVersand   = (new Pagination('versand'))
        ->setSortByOptions($sortByOptions)
        ->setItemCount($nKuponVersandCount)
        ->assemble();
    $paginationNeukunden = (new Pagination('neukunden'))
        ->setSortByOptions($sortByOptions)
        ->setItemCount($nKuponNeukundenCount)
        ->assemble();

    $oKuponStandard_arr  = getCoupons(
        'standard',
        $filterStandard->getWhereSQL(),
        $paginationStandard->getOrderSQL(),
        $paginationStandard->getLimitSQL()
    );
    $oKuponVersand_arr   = getCoupons(
        'versandkupon',
        $filterVersand->getWhereSQL(),
        $paginationVersand->getOrderSQL(),
        $paginationVersand->getLimitSQL()
    );
    $oKuponNeukunden_arr = getCoupons(
        'neukundenkupon',
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
       ->display('kupons.tpl');
