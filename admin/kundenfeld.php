<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use Helpers\Form;
use Helpers\Request;

require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('ORDER_CUSTOMERFIELDS_VIEW', true, true);

/** @global \Smarty\JTLSmarty $smarty */
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
        Alert::TYPE_NOTE,
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
                    Alert::TYPE_NOTE,
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
            $alertHelper->addAlert(Alert::TYPE_NOTE, __('successCustomerFieldRefresh'), 'successCustomerFieldRefresh');
        } else {
            $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorCustomerFieldRefresh'), 'errorCustomerFieldRefresh');
        }
    } else { // Speichern
        $customerField = (object)[
            'kKundenfeld' => (int)($_POST['kKundenfeld'] ?? 0),
            'kSprache'    => (int)$_SESSION['kSprache'],
            'cName'       => StringHandler::htmlspecialchars(
                StringHandler::filterXSS($_POST['cName']),
                ENT_COMPAT | ENT_HTML401
            ),
            'cWawi'       => StringHandler::filterXSS(str_replace(['"',"'"], '', $_POST['cWawi'])),
            'cTyp'        => StringHandler::filterXSS($_POST['cTyp']),
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
                $alertHelper->addAlert(Alert::TYPE_NOTE, __('successCustomerFieldSave'), 'successCustomerFieldSave');
            } else {
                $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorCustomerFieldSave'), 'errorCustomerFieldSave');
            }
        } else {
            $erroneousFields = $check->getPlausiVar();
            if (isset($erroneousFields['cName']) && 2 === $erroneousFields['cName']) {
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
       ->assign('Sprachen', Sprache::getAllLanguages())
       ->assign('step', $step)
       ->display('kundenfeld.tpl');
