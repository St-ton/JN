<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTL\Alert\Alert;
use JTL\DB\ReturnType;
use JTL\Helpers\Form;
use JTL\Helpers\GeneralObject;
use JTL\Helpers\Request;
use JTL\Language\LanguageHelper;
use JTL\Pagination\Pagination;
use JTL\Shop;

require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('ORDER_PACKAGE_VIEW', true, true);

/** @global \JTL\Smarty\JTLSmarty $smarty */
$step        = 'zusatzverpackung';
$languages   = LanguageHelper::getAllLanguages();
$action      = '';
$alertHelper = Shop::Container()->getAlertService();
$db          = Shop::Container()->getDB();
if (Form::validateToken()) {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
    } elseif (isset($_GET['kVerpackung']) && Request::verifyGPCDataInt('kVerpackung') >= 0) {
        $action = 'edit';
    }
}

if ($action === 'save') {
    $packagingID                    = (int)$_POST['kVerpackung'];
    $customerGroupIDs               = $_POST['kKundengruppe'] ?? null;
    $packaging                      = new stdClass();
    $packaging->fBrutto             = (float)str_replace(',', '.', $_POST['fBrutto'] ?? 0);
    $packaging->fMindestbestellwert = (float)str_replace(',', '.', $_POST['fMindestbestellwert'] ?? 0);
    $packaging->fKostenfrei         = (float)str_replace(',', '.', $_POST['fKostenfrei'] ?? 0);
    $packaging->kSteuerklasse       = isset($_POST['kSteuerklasse']) ? (int)$_POST['kSteuerklasse'] : 0;
    $packaging->nAktiv              = isset($_POST['nAktiv']) ? (int)$_POST['nAktiv'] : 0;
    $packaging->cName               = htmlspecialchars(
        strip_tags(trim($_POST['cName_' . $languages[0]->cISO])),
        ENT_COMPAT | ENT_HTML401,
        JTL_CHARSET
    );

    if (!(isset($_POST['cName_' . $languages[0]->cISO])
        && mb_strlen($_POST['cName_' . $languages[0]->cISO]) > 0)
    ) {
        $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorNameMissing'), 'errorNameMissing');
    }
    if (!(is_array($customerGroupIDs) && count($customerGroupIDs) > 0)) {
        $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorCustomerGroupMissing'), 'errorCustomerGroupMissing');
    }

    if ($alertHelper->alertTypeExists(Alert::TYPE_ERROR)) {
        holdInputOnError($packaging, $customerGroupIDs, $packagingID, $smarty);
        $action = 'edit';
    } else {
        if ($customerGroupIDs[0] == '-1') {
            $packaging->cKundengruppe = '-1';
        } else {
            $packaging->cKundengruppe = ';' . implode(';', $customerGroupIDs) . ';';
        }
        // Update?
        if ($packagingID > 0) {
            $db->query(
                'DELETE tverpackung, tverpackungsprache
                    FROM tverpackung
                    LEFT JOIN tverpackungsprache 
                        ON tverpackungsprache.kVerpackung = tverpackung.kVerpackung
                    WHERE tverpackung.kVerpackung = ' . $packagingID,
                ReturnType::AFFECTED_ROWS
            );
            $packaging->kVerpackung = $packagingID;
            $db->insert('tverpackung', $packaging);
        } else {
            $packagingID = $db->insert('tverpackung', $packaging);
        }
        foreach ($languages as $lang) {
            $langCode                 = $lang->getCode();
            $localized                = new stdClass();
            $localized->kVerpackung   = $packagingID;
            $localized->cISOSprache   = $langCode;
            $localized->cName         = !empty($_POST['cName_' . $langCode])
                ? htmlspecialchars($_POST['cName_' . $langCode], ENT_COMPAT | ENT_HTML401, JTL_CHARSET)
                : htmlspecialchars($_POST['cName_' . $languages[0]->cISO], ENT_COMPAT | ENT_HTML401, JTL_CHARSET);
            $localized->cBeschreibung = !empty($_POST['cBeschreibung_' . $langCode])
                ? htmlspecialchars($_POST['cBeschreibung_' . $langCode], ENT_COMPAT | ENT_HTML401, JTL_CHARSET)
                : htmlspecialchars(
                    $_POST['cBeschreibung_' . $languages[0]->cISO],
                    ENT_COMPAT | ENT_HTML401,
                    JTL_CHARSET
                );
            $db->insert('tverpackungsprache', $localized);
        }
        $alertHelper->addAlert(
            Alert::TYPE_SUCCESS,
            sprintf(__('successPackagingSave'), $_POST['cName_' . $languages[0]->cISO]),
            'successPackagingSave'
        );
    }
} elseif ($action === 'edit' && Request::verifyGPCDataInt('kVerpackung') > 0) { // Editieren
    $packagingID = Request::verifyGPCDataInt('kVerpackung');
    $packaging   = $db->select('tverpackung', 'kVerpackung', $packagingID);

    if (isset($packaging->kVerpackung) && $packaging->kVerpackung > 0) {
        $packaging->oSprach_arr = [];
        $localizations          = $db->selectAll(
            'tverpackungsprache',
            'kVerpackung',
            $packagingID,
            'cISOSprache, cName, cBeschreibung'
        );
        foreach ($localizations as $localization) {
            $packaging->oSprach_arr[$localization->cISOSprache] = $localization;
        }
        $customerGroup                = gibKundengruppeObj($packaging->cKundengruppe);
        $packaging->kKundengruppe_arr = $customerGroup->kKundengruppe_arr;
        $packaging->cKundengruppe_arr = $customerGroup->cKundengruppe_arr;
    }
    $smarty->assign('kVerpackung', $packaging->kVerpackung)
        ->assign('oVerpackungEdit', $packaging);
} elseif ($action === 'delete') {
    if (GeneralObject::hasCount('kVerpackung', $_POST)) {
        foreach ($_POST['kVerpackung'] as $packagingID) {
            $packagingID = (int)$packagingID;
            // tverpackung loeschen
            $db->delete('tverpackung', 'kVerpackung', $packagingID);
            $db->delete('tverpackungsprache', 'kVerpackung', $packagingID);
        }
        $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successPackagingDelete'), 'successPackagingDelete');
    } else {
        $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorAtLeastOnePackaging'), 'errorAtLeastOnePackaging');
    }
} elseif ($action === 'refresh') {
    if (isset($_POST['nAktivTMP']) && is_array($_POST['nAktivTMP']) && count($_POST['nAktivTMP']) > 0) {
        foreach ($_POST['nAktivTMP'] as $packagingID) {
            $upd         = new stdClass();
            $upd->nAktiv = isset($_POST['nAktiv']) && in_array($packagingID, $_POST['nAktiv'], true) ? 1 : 0;
            $db->update('tverpackung', 'kVerpackung', (int)$packagingID, $upd);
        }
        $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successPackagingSaveMultiple'), 'successPackagingSaveMultiple');
    }
}

$customerGroups = $db->query(
    'SELECT kKundengruppe, cName FROM tkundengruppe',
    ReturnType::ARRAY_OF_OBJECTS
);
$taxClasses     = $db->query(
    'SELECT * FROM tsteuerklasse',
    ReturnType::ARRAY_OF_OBJECTS
);

$packagingCount = (int)$db->query(
    'SELECT COUNT(kVerpackung) AS count
            FROM tverpackung',
    ReturnType::SINGLE_OBJECT
)->count;
$itemsPerPage   = 10;
$pagination     = (new Pagination('standard'))
    ->setItemsPerPageOptions([$itemsPerPage, $itemsPerPage * 2, $itemsPerPage * 5])
    ->setItemCount($packagingCount)
    ->assemble();
$packagings     = $db->query(
    'SELECT * FROM tverpackung 
       ORDER BY cName' .
    ($pagination->getLimitSQL() !== '' ? ' LIMIT ' . $pagination->getLimitSQL() : ''),
    ReturnType::ARRAY_OF_OBJECTS
);

foreach ($packagings as $i => $packaging) {
    $customerGroup                = gibKundengruppeObj($packaging->cKundengruppe);
    $packaging->kKundengruppe_arr = $customerGroup->kKundengruppe_arr;
    $packaging->cKundengruppe_arr = $customerGroup->cKundengruppe_arr;
}

$smarty->assign('oKundengruppe_arr', $customerGroups)
    ->assign('oSteuerklasse_arr', $taxClasses)
    ->assign('oVerpackung_arr', $packagings)
    ->assign('step', $step)
    ->assign('pagination', $pagination)
    ->assign('action', $action)
    ->display('zusatzverpackung.tpl');

/**
 * @param string $groupString
 * @return stdClass|null
 */
function gibKundengruppeObj($groupString)
{
    $customerGroup = new stdClass();
    $tmpIDs        = [];
    $tmpNames      = [];

    if (mb_strlen($groupString) > 0) {
        // Kundengruppen holen
        $data             = Shop::Container()->getDB()->query(
            'SELECT kKundengruppe, cName FROM tkundengruppe',
            ReturnType::ARRAY_OF_OBJECTS
        );
        $customerGroupIDs = explode(';', $groupString);
        if (!in_array('-1', $customerGroupIDs)) {
            foreach ($customerGroupIDs as $id) {
                $id       = (int)$id;
                $tmpIDs[] = $id;
                if (is_array($data) && count($data) > 0) {
                    foreach ($data as $customerGroup) {
                        if ($customerGroup->kKundengruppe == $id) {
                            $tmpNames[] = $customerGroup->cName;
                            break;
                        }
                    }
                }
            }
        } elseif (count($data) > 0) {
            foreach ($data as $customerGroup) {
                $tmpIDs[]   = $customerGroup->kKundengruppe;
                $tmpNames[] = $customerGroup->cName;
            }
        }
    }
    $customerGroup->kKundengruppe_arr = $tmpIDs;
    $customerGroup->cKundengruppe_arr = $tmpNames;

    return $customerGroup;
}

/**
 * @param object $packaging
 * @param array  $customerGroupIDs
 * @param int    $packagingID
 * @param object $smarty
 * @return void
 */
function holdInputOnError($packaging, $customerGroupIDs, $packagingID, &$smarty)
{
    $packaging->oSprach_arr = [];
    foreach ($_POST as $key => $value) {
        if (mb_strpos($key, 'cName') !== false) {
            $cISO                                 = explode('cName_', $key)[1];
            $idx                                  = 'cBeschreibung_' . $cISO;
            $packaging->oSprach_arr[$cISO]        = new stdClass();
            $packaging->oSprach_arr[$cISO]->cName = $value;
            if (isset($_POST[$idx])) {
                $packaging->oSprach_arr[$cISO]->cBeschreibung = $_POST[$idx];
            }
        }
    }

    if ($customerGroupIDs && $customerGroupIDs[0] !== '-1') {
        $packaging->cKundengruppe     = ';' . implode(';', $customerGroupIDs) . ';';
        $customerGroup                = gibKundengruppeObj($packaging->cKundengruppe);
        $packaging->kKundengruppe_arr = $customerGroup->kKundengruppe_arr;
        $packaging->cKundengruppe_arr = $customerGroup->cKundengruppe_arr;
    } else {
        $packaging->cKundengruppe = '-1';
    }

    $smarty->assign('oVerpackungEdit', $packaging)
        ->assign('kVerpackung', $packagingID);
}
