<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTL\Alert\Alert;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Shop;

require_once __DIR__ . '/includes/admininclude.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'agbwrb_inc.php';
/** @global \JTL\Smarty\JTLSmarty $smarty */
$oAccount->permission('ORDER_AGB_WRB_VIEW', true, true);
$step        = 'agbwrb_uebersicht';
$alertHelper = Shop::Container()->getAlertService();

setzeSprache();

if (Request::verifyGPCDataInt('agbwrb') === 1 && Form::validateToken()) {
    // Editieren
    if (Request::verifyGPCDataInt('agbwrb_edit') === 1) {
        if (Request::verifyGPCDataInt('kKundengruppe') > 0) {
            $step    = 'agbwrb_editieren';
            $oAGBWRB = Shop::Container()->getDB()->select(
                'ttext',
                'kSprache',
                (int)$_SESSION['kSprache'],
                'kKundengruppe',
                Request::verifyGPCDataInt('kKundengruppe')
            );
            $smarty->assign('kKundengruppe', Request::verifyGPCDataInt('kKundengruppe'))
                   ->assign('oAGBWRB', $oAGBWRB);
        } else {
            $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorInvalidCustomerGroup'), 'errorInvalidCustomerGroup');
        }
    } elseif (Request::verifyGPCDataInt('agbwrb_editieren_speichern') === 1) {
        if (speicherAGBWRB(
            Request::verifyGPCDataInt('kKundengruppe'),
            $_SESSION['kSprache'],
            $_POST,
            Request::verifyGPCDataInt('kText')
        )) {
            $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successSave'), 'agbWrbSuccessSave');
        } else {
            $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorSave'), 'agbWrbErrorSave');
        }
    }
}

if ($step === 'agbwrb_uebersicht') {
    // Kundengruppen holen
    $customerGroups = Shop::Container()->getDB()->selectAll(
        'tkundengruppe',
        [],
        [],
        'kKundengruppe, cName',
        'cStandard DESC'
    );
    // AGB fuer jeweilige Sprache holen
    $agbWrb = [];
    $data   = Shop::Container()->getDB()->selectAll('ttext', 'kSprache', (int)$_SESSION['kSprache']);
    // Assoc Array mit kKundengruppe machen
    foreach ($data as $item) {
        $agbWrb[(int)$item->kKundengruppe] = $item;
    }
    $smarty->assign('oKundengruppe_arr', $customerGroups)
           ->assign('oAGBWRB_arr', $agbWrb);
}

$smarty->assign('step', $step)
       ->assign('kSprache', $_SESSION['kSprache'])
       ->display('agbwrb.tpl');
