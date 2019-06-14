<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTL\Alert\Alert;
use JTL\Backend\CustomerFields;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\PlausiKundenfeld;
use JTL\Shop;

require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('ORDER_CUSTOMERFIELDS_VIEW', true, true);

/** @global \JTL\Smarty\JTLSmarty $smarty */
$cf          = CustomerFields::getInstance((int)$_SESSION['kSprache']);
$step        = 'uebersicht';
$alertHelper = Shop::Container()->getAlertService();

setzeSprache();

$smarty->assign('cTab', $cStep ?? null);
if (mb_strlen(Request::verifyGPDataString('tab')) > 0) {
    $smarty->assign('cTab', Request::verifyGPDataString('tab'));
}

if (isset($_POST['einstellungen']) && (int)$_POST['einstellungen'] > 0) {
    $alertHelper->addAlert(
        Alert::TYPE_SUCCESS,
        saveAdminSectionSettings(CONF_KUNDENFELD, $_POST),
        'saveSettings'
    );
} elseif (isset($_POST['kundenfelder']) && (int)$_POST['kundenfelder'] === 1 && Form::validateToken()) {
    $success = true;
    if (isset($_POST['loeschen'])) {
        $fieldIDs = $_POST['kKundenfeld'];
        if (is_array($fieldIDs) && count($fieldIDs) > 0) {
            foreach ($fieldIDs as $kKundenfeld) {
                $success = $success && $cf->delete((int)$kKundenfeld);
            }
            if ($success) {
                $alertHelper->addAlert(
                    Alert::TYPE_SUCCESS,
                    __('successCustomerFieldDelete'),
                    'successCustomerFieldDelete'
                );
            } else {
                $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorCustomerFieldDelete'), 'errorCustomerFieldDelete');
            }
        } else {
            $alertHelper->addAlert(
                Alert::TYPE_ERROR,
                __('errorAtLeastOneCustomerField'),
                'errorAtLeastOneCustomerField'
            );
        }
    } elseif (isset($_POST['aktualisieren'])) {
        foreach ($cf->getCustomerFields() as $customerField) {
            $customerField->nSort = (int)$_POST['nSort_' . $customerField->kKundenfeld];
            $success              = $success && $cf->save($customerField);
        }
        if ($success) {
            $alertHelper->addAlert(
                Alert::TYPE_SUCCESS,
                __('successCustomerFieldUpdate'),
                'successCustomerFieldUpdate'
            );
        } else {
            $alertHelper->addAlert(
                Alert::TYPE_ERROR,
                __('errorCustomerFieldUpdate'),
                'errorCustomerFieldUpdate'
            );
        }
    } else { // Speichern
        $customerField = (object)[
            'kKundenfeld' => (int)($_POST['kKundenfeld'] ?? 0),
            'kSprache'    => (int)$_SESSION['kSprache'],
            'cName'       => Text::htmlspecialchars(
                Text::filterXSS($_POST['cName']),
                ENT_COMPAT | ENT_HTML401
            ),
            'cWawi'       => Text::filterXSS(str_replace(['"', "'"], '', $_POST['cWawi'])),
            'cTyp'        => Text::filterXSS($_POST['cTyp']),
            'nSort'       => (int)$_POST['nSort'],
            'nPflicht'    => (int)$_POST['nPflicht'],
            'nEditierbar' => (int)$_POST['nEdit'],
        ];

        $cfValues = $_POST['cfValues'] ?? null;
        $check    = new PlausiKundenfeld();
        $check->setPostVar($_POST);
        $check->doPlausi($customerField->cTyp, $customerField->kKundenfeld > 0);

        if (count($check->getPlausiVar()) === 0) {
            if ($cf->save($customerField, $cfValues)) {
                $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successCustomerFieldSave'), 'successCustomerFieldSave');
            } else {
                $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorCustomerFieldSave'), 'errorCustomerFieldSave');
            }
        } else {
            $erroneousFields = $check->getPlausiVar();
            if (isset($erroneousFields['cName']) && $erroneousFields['cName'] === 2) {
                $alertHelper->addAlert(
                    Alert::TYPE_ERROR,
                    __('errorCustomerFieldNameExists'),
                    'errorCustomerFieldNameExists'
                );
            } else {
                $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorFillRequired'), 'errorFillRequired');
            }
            $smarty->assign('xPlausiVar_arr', $check->getPlausiVar())
                   ->assign('xPostVar_arr', $check->getPostVar())
                   ->assign('kKundenfeld', $customerField->kKundenfeld);
        }
    }
} elseif (Request::verifyGPDataString('a') === 'edit') {
    $kKundenfeld = Request::verifyGPCDataInt('kKundenfeld');
    if ($kKundenfeld > 0) {
        $customerField = $cf->getCustomerField($kKundenfeld);

        if ($customerField !== null) {
            $customerField->oKundenfeldWert_arr = $cf->getCustomerFieldValues($customerField);
            $smarty->assign('oKundenfeld', $customerField);
        }
    }
}
$fields = $cf->getCustomerFields();
foreach ($fields as $field) {
    if ($field->cTyp === 'auswahl') {
        $field->oKundenfeldWert_arr = $cf->getCustomerFieldValues($field);
    }
}
// calculate the highest sort-order number (based on the 'ORDER BY' above)
// to recommend the user the next sort-order-value, instead of a placeholder
$lastElement      = end($fields);
$highestSortValue = $lastElement !== false ? $lastElement->nSort : 0;
$preLastElement   = prev($fields);
if ($preLastElement === false) {
    $highestSortDiff = ($lastElement === false || $lastElement->nSort === 0) ? 1 : $lastElement->nSort;
} else {
    $highestSortDiff = $lastElement->nSort - $preLastElement->nSort;
}
reset($fields); // we leave the array in a safe state

$smarty->assign('oKundenfeld_arr', $fields)
       ->assign('nHighestSortValue', $highestSortValue)
       ->assign('nHighestSortDiff', $highestSortDiff)
       ->assign('oConfig_arr', getAdminSectionSettings(CONF_KUNDENFELD))
       ->assign('step', $step)
       ->display('kundenfeld.tpl');
