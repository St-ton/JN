<?php

use JTL\Alert\Alert;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Shop;
use JTL\Reset\Reset;
use JTL\Reset\ResetContentType;

require_once __DIR__ . '/includes/admininclude.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'news_inc.php';
/** @global \JTL\Backend\AdminAccount $oAccount */
/** @global \JTL\Smarty\JTLSmarty $smarty */

$oAccount->permission('RESET_SHOP_VIEW', true, true);
$alertHelper = Shop::Container()->getAlertService();
$db          = Shop::Container()->getDB();
if (Request::postInt('zuruecksetzen') === 1 && Form::validateToken()) {
    $options = $_POST['cOption_arr'];
    if (is_array($options) && count($options) > 0) {
        $reset = new Reset($db);
        foreach ($options as $option) {
            $reset->doReset(ResetContentType::from($option));
        }
        Shop::Container()->getCache()->flushAll();
        $db->query('UPDATE tglobals SET dLetzteAenderung = NOW()');
        $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successShopReturn'), 'successShopReturn');
    } else {
        $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorChooseOption'), 'errorChooseOption');
    }

    executeHook(HOOK_BACKEND_SHOP_RESET_AFTER);
}

$smarty->display('shopzuruecksetzen.tpl');
