<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Shop;

require_once __DIR__ . '/includes/admininclude.php';
$oAccount->permission('DISPLAY_OWN_LOGO_VIEW', true, true);
/** @global \JTL\Smarty\JTLSmarty $smarty */
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'shoplogouploader_inc.php';

if (isset($_POST['key'], $_POST['logo'])) {
    $currentLogo = Shop::getLogo();
    $response    = new stdClass();
    if ($currentLogo === $_POST['logo']) {
        $delete                        = deleteShopLogo($currentLogo);
        $response->status              = ($delete === true) ? 'OK' : 'FAILED';
        $option                        = new stdClass();
        $option->kEinstellungenSektion = CONF_LOGO;
        $option->cName                 = 'shop_logo';
        $option->cWert                 = null;
        Shop::Container()->getDB()->update('teinstellungen', 'cName', 'shop_logo', $option);
        Shop::Container()->getCache()->flushTags([CACHING_GROUP_OPTION]);
    } else {
        $response->status = 'FAILED';
    }
    die(json_encode($response));
}

$alertHelper = Shop::Container()->getAlertService();
$step        = 'shoplogouploader_uebersicht';
// Upload
if (!empty($_FILES) && Form::validateToken()) {
    $status           = saveShopLogo($_FILES);
    $response         = new stdClass();
    $response->status = ($status === 1) ? 'OK' : 'FAILED';
    echo json_encode($response);
    die();
}
if (Request::verifyGPCDataInt('upload') === 1 && Form::validateToken()) {
    if (isset($_POST['delete'])) {
        $delete = deleteShopLogo(Shop::getLogo());
        if ($delete === true) {
            $alertHelper->addAlert(Alert::TYPE_NOTE, __('successLogoDelete'), 'successLogoDelete');
        } else {
            $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorLogoDelete'), 'errorLogoDelete');
        }
    }
    $nReturnValue = saveShopLogo($_FILES);
    if ($nReturnValue === 1) {
        $alertHelper->addAlert(Alert::TYPE_NOTE, __('successLogoUpload'), 'successLogoUpload');
    } else {
        // 2 = Dateiname entspricht nicht der Konvention oder fehlt
        // 3 = Dateityp entspricht nicht der (Nur jpg/gif/png/bmp/ Bilder) Konvention oder fehlt
        switch ($nReturnValue) {
            case 2:
                $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorFileName'), 'errorFileName');
                break;
            case 3:
                $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorFileType'), 'errorFileType');
                break;
            case 4:
                $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorFileMove'), 'errorFileMove');
                break;
            default:
                break;
        }
    }
}

$smarty->assign('cRnd', time())
       ->assign('ShopLogo', Shop::getLogo(false))
       ->assign('ShopLogoURL', Shop::getLogo(true))
       ->assign('step', $step)
       ->display('shoplogouploader.tpl');
