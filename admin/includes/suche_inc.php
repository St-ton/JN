<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Search for backend settings
 *
 * @param string $query - search string
 * @return string
 */
function adminSearch($query)
{
    require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'einstellungen_inc.php';
    require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'versandarten_inc.php';
    require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'zahlungsarten_inc.php';

    $settings       = bearbeiteEinstellungsSuche($query);
    $shippings      = getShippingByName($query);
    $paymentMethods = getPaymentMethodsByName($query);

    $groupedSettings = [];
    $currentGroup    = null;

    foreach ($settings->oEinstellung_arr as $setting) {
        if ($setting->cConf === 'N') {
            $currentGroup                   = $setting;
            $currentGroup->oEinstellung_arr = [];
            $groupedSettings[]              = $currentGroup;
        } elseif ($currentGroup !== null) {
            $currentGroup->oEinstellung_arr[] = $setting;
        }
    }

    Shop::Smarty()->assign('settings', !empty($settings->oEinstellung_arr) ? $groupedSettings : null)
           ->assign('shippings', count($shippings) > 0 ? $shippings : null)
           ->assign('paymentMethods', count($paymentMethods) > 0 ? $paymentMethods : null);

    return Shop::Smarty()->fetch('suche.tpl');
}
