<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTL\Helpers\Form;
use JTL\Shop;
use JTL\Sprache;
use JTL\Catalog\Warenlager;
use JTL\DB\ReturnType;

require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('WAREHOUSE_VIEW', true, true);
/** @global \JTL\Smarty\JTLSmarty $smarty */
$cStep       = 'uebersicht';
$cAction     = (isset($_POST['a']) && Form::validateToken()) ? $_POST['a'] : null;
$alertHelper = Shop::Container()->getAlertService();

if ($cAction === 'update') {
    Shop::Container()->getDB()->query('UPDATE twarenlager SET nAktiv = 0', ReturnType::AFFECTED_ROWS);
    if (isset($_REQUEST['kWarenlager'])
        && is_array($_REQUEST['kWarenlager'])
        && count($_REQUEST['kWarenlager']) > 0
    ) {
        $wl = [];
        foreach ($_REQUEST['kWarenlager'] as $_wl) {
            $wl[] = (int)$_wl;
        }
        Shop::Container()->getDB()->query(
            'UPDATE twarenlager SET nAktiv = 1 WHERE kWarenlager IN (' . implode(', ', $wl) . ')',
            ReturnType::AFFECTED_ROWS
        );
    }
    if (is_array($_REQUEST['cNameSprache']) && count($_REQUEST['cNameSprache']) > 0) {
        foreach ($_REQUEST['cNameSprache'] as $kWarenlager => $cSpracheAssoc_arr) {
            Shop::Container()->getDB()->delete('twarenlagersprache', 'kWarenlager', (int)$kWarenlager);

            foreach ($cSpracheAssoc_arr as $kSprache => $cName) {
                if (mb_strlen(trim($cName)) > 1) {
                    $oObj              = new stdClass();
                    $oObj->kWarenlager = (int)$kWarenlager;
                    $oObj->kSprache    = (int)$kSprache;
                    $oObj->cName       = htmlspecialchars(trim($cName), ENT_COMPAT | ENT_HTML401, JTL_CHARSET);

                    Shop::Container()->getDB()->insert('twarenlagersprache', $oObj);
                }
            }
        }
    }
    Shop::Container()->getCache()->flushTags([CACHING_GROUP_ARTICLE]);
    $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successStoreRefresh'), 'successStoreRefresh');
}

if ($cStep === 'uebersicht') {
    $smarty->assign('oWarenlager_arr', Warenlager::getAll(false, true))
           ->assign('oSprache_arr', Sprache::getAllLanguages());
}

$smarty->assign('cStep', $cStep)
       ->display('warenlager.tpl');
