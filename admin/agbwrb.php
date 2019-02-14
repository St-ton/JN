<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Shop;
use JTL\Sprache;

require_once __DIR__ . '/includes/admininclude.php';
require_once PFAD_ROOT . PFAD_DBES . 'seo.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'agbwrb_inc.php';
/** @global \JTL\Smarty\JTLSmarty $smarty */
$oAccount->permission('ORDER_AGB_WRB_VIEW', true, true);
$cHinweis = '';
$cFehler  = '';
$step     = 'agbwrb_uebersicht';

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
            $cFehler .= __('errorInvalidCustomerGroup') . '<br />';
        }
    } elseif (Request::verifyGPCDataInt('agbwrb_editieren_speichern') === 1) {
        if (speicherAGBWRB(
            Request::verifyGPCDataInt('kKundengruppe'),
            $_SESSION['kSprache'],
            $_POST,
            Request::verifyGPCDataInt('kText')
        )) {
            $cHinweis .= __('successSave') . '<br />';
        } else {
            $cFehler .= __('errorSave') . '<br />';
        }
    }
}

if ($step === 'agbwrb_uebersicht') {
    // Kundengruppen holen
    $oKundengruppe_arr = Shop::Container()->getDB()->selectAll(
        'tkundengruppe',
        [],
        [],
        'kKundengruppe, cName',
        'cStandard DESC'
    );
    // AGB fuer jeweilige Sprache holen
    $oAGBWRB_arr    = [];
    $oAGBWRBTMP_arr = Shop::Container()->getDB()->selectAll('ttext', 'kSprache', (int)$_SESSION['kSprache']);
    // Assoc Array mit kKundengruppe machen
    foreach ($oAGBWRBTMP_arr as $i => $oAGBWRBTMP) {
        $oAGBWRB_arr[$oAGBWRBTMP->kKundengruppe] = $oAGBWRBTMP;
    }
    $smarty->assign('oKundengruppe_arr', $oKundengruppe_arr)
           ->assign('oAGBWRB_arr', $oAGBWRB_arr);
}

$smarty->assign('hinweis', $cHinweis)
       ->assign('fehler', $cFehler)
       ->assign('step', $step)
       ->assign('Sprachen', Sprache::getAllLanguages())
       ->assign('kSprache', $_SESSION['kSprache'])
       ->display('agbwrb.tpl');
