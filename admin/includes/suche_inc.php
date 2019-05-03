<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTL\Shop;

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

    $adminMenuItems = adminMenuSearch($query);
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

    Shop::Smarty()
        ->assign('adminMenuItems', $adminMenuItems)
        ->assign('settings', !empty($settings->oEinstellung_arr) ? $groupedSettings : null)
        ->assign('shippings', count($shippings) > 0 ? $shippings : null)
        ->assign('paymentMethods', count($paymentMethods) > 0 ? $paymentMethods : null);

    return Shop::Smarty()->fetch('suche.tpl');
}

/**
 * @param string $query
 * @return array
 */
function adminMenuSearch($query)
{
    require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'admin_menu.php';

    global $adminMenu;

    $results = [];

    foreach ($adminMenu as $menuName => $menu) {
        foreach ($menu as $subMenuName => $subMenu) {
            if (is_array($subMenu)) {
                foreach ($subMenu as $itemName => $item) {
                    if (strpos($itemName, $query) !== false) {
                        $name      = $itemName;
                        $name      = preg_replace(
                            '/\p{L}*?' . preg_quote($query, '/'). '\p{L}*/ui',
                            '<mark>$0</mark>',
                            $name
                        );
                        $path      = $menuName . ' > ' . $subMenuName . ' > ' . $name;
                        $results[] = (object)[
                            'title' => $itemName,
                            'path'  => $path,
                            'link'  => $item->link,
                        ];
                    }
                }
            }
        }
    }

    return $results;
}
