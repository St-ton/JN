<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/admininclude.php';
$oAccount->permission('DISPLAY_OWN_LOGO_VIEW', true, true);
/** @global Smarty\JTLSmarty $smarty */
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

$cHinweis = '';
$cFehler  = '';
$step     = 'shoplogouploader_uebersicht';
// Upload
if (!empty($_FILES) && FormHelper::validateToken()) {
    $status           = saveShopLogo($_FILES);
    $response         = new stdClass();
    $response->status = ($status === 1) ? 'OK' : 'FAILED';
    echo json_encode($response);
    die();
}
if (RequestHelper::verifyGPCDataInt('upload') === 1 && FormHelper::validateToken()) {
    if (isset($_POST['delete'])) {
        $delete = deleteShopLogo(Shop::getLogo());
        if ($delete === true) {
            $cHinweis .= 'Ihr Logo wurde erfolgreich gelöscht.<br />';
        } else {
            $cFehler .= 'Fehler beim Löschen des Logos.<br />';
        }
    }
    $nReturnValue = saveShopLogo($_FILES);
    if ($nReturnValue === 1) {
        $cHinweis .= 'Ihr Logo wurde erfolgreich hochgeladen.<br />';
    } else {
        // 2 = Dateiname entspricht nicht der Konvention oder fehlt
        // 3 = Dateityp entspricht nicht der (Nur jpg/gif/png/bmp/ Bilder) Konvention oder fehlt
        switch ($nReturnValue) {
            case 2:
                $cFehler .= 'Fehler: Dateiname entspricht nicht der Konvention oder fehlt.';
                break;
            case 3:
                $cFehler .= 'Fehler: Dateityp entspricht nicht der Konvention (nur jpg/gif/png/bmp) oder fehlt.';
                break;
            case 4:
                $cFehler .= 'Fehler beim Verschieben des Logos.';
                break;
            default:
                break;
        }
    }
}

$smarty->assign('cRnd', time())
       ->assign('ShopLogo', Shop::getLogo(false))
       ->assign('ShopLogoURL', Shop::getLogo(true))
       ->assign('hinweis', $cHinweis)
       ->assign('fehler', $cFehler)
       ->assign('step', $step)
       ->display('shoplogouploader.tpl');
