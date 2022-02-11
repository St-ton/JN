<?php declare(strict_types=1);

use Illuminate\Support\Collection;
use JTL\Backend\Settings\Manager as SettingsManager;
use JTL\Backend\Settings\Search;
use JTL\Backend\Settings\Sections\SectionInterface;
use JTL\Helpers\Text;
use JTL\Plugin\Admin\Listing;
use JTL\Plugin\Admin\ListingItem;
use JTL\Plugin\Admin\Validation\LegacyPluginValidator;
use JTL\Plugin\Admin\Validation\PluginValidator;
use JTL\Shop;
use JTL\XMLParser;

/**
 * Search for backend settings
 *
 * @param string $query - search string
 * @param bool   $standalonePage - render as standalone page
 * @return string|null
 */
function adminSearch(string $query, bool $standalonePage = false): ?string
{
    require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'einstellungen_inc.php';
    require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'versandarten_inc.php';
    require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'zahlungsarten_inc.php';

    $adminMenuItems = adminMenuSearch($query);
    $settings       = configSearch($query);
    $shippings      = getShippingByName($query);
    $paymentMethods = getPaymentMethodsByName($query);
    foreach ($shippings as $shipping) {
        $shipping->cName = highlightSearchTerm($shipping->cName, $query);
    }
    foreach ($paymentMethods as $paymentMethod) {
        $paymentMethod->cName = highlightSearchTerm($paymentMethod->cName, $query);
    }
    $smarty = Shop::Smarty();
    $smarty->assign('standalonePage', $standalonePage)
        ->assign('query', Text::filterXSS($query))
        ->assign('adminMenuItems', $adminMenuItems)
        ->assign('settings', $settings)
        ->assign('shippings', count($shippings) > 0 ? $shippings : null)
        ->assign('paymentMethods', count($paymentMethods) > 0 ? $paymentMethods : null)
        ->assign('plugins', getPlugins($query));

    if ($standalonePage) {
        $smarty->display('suche.tpl');
        return null;
    }

    return $smarty->fetch('suche.tpl');
}

/**
 * @param string $query
 * @return SectionInterface[]
 */
function configSearch(string $query): array
{
    $db      = Shop::Container()->getDB();
    $gettext = Shop::Container()->getGetText();
    $manager = new SettingsManager(
        $db,
        Shop::Smarty(),
        Shop::Container()->getAdminAccount(),
        $gettext,
        Shop::Container()->getAlertService()
    );

    return (new Search($db, $gettext, $manager))->getResultSections($query);
}

/**
 * @param string $query
 * @return array
 */
function adminMenuSearch(string $query): array
{
    require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'admin_menu.php';

    global $adminMenu;

    $results = [];

    foreach ($adminMenu as $menuName => $menu) {
        foreach ($menu->items as $subMenuName => $subMenu) {
            if (is_array($subMenu)) {
                foreach ($subMenu as $itemName => $item) {
                    if (is_object($item) && (
                            stripos($itemName, $query) !== false
                            || stripos($subMenuName, $query) !== false
                            || stripos($menuName, $query) !== false
                        )
                    ) {
                        $name      = $itemName;
                        $path      = $menuName . ' > ' . $subMenuName . ' > ' . $name;
                        $path      = highlightSearchTerm($path, $query);
                        $results[] = (object)[
                            'title' => $itemName,
                            'path'  => $path,
                            'link'  => $item->link,
                            'icon'  => $menu->icon
                        ];
                    }
                }
            } elseif (is_object($subMenu)
                && (stripos($subMenuName, $query) !== false || stripos($menuName, $query) !== false)
            ) {
                $results[] = (object)[
                    'title' => $subMenuName,
                    'path'  => highlightSearchTerm($menuName . ' > ' . $subMenuName, $query),
                    'link'  => $subMenu->link,
                    'icon'  => $menu->icon
                ];
            }
        }
    }

    return $results;
}

/**
 * @param string $haystack
 * @param string $needle
 * @return string
 */
function highlightSearchTerm(string $haystack, string $needle): string
{
    return preg_replace(
        '/\p{L}*?' . preg_quote($needle, '/') . '\p{L}*/ui',
        '<mark>$0</mark>',
        $haystack
    );
}

/**
 * @param string $query
 * @return Collection
 */
function getPlugins(string $query): Collection
{
    if (mb_strlen($query) <= 2) {
        return new Collection();
    }
    $db              = Shop::Container()->getDB();
    $cache           = Shop::Container()->getCache();
    $parser          = new XMLParser();
    $legacyValidator = new LegacyPluginValidator($db, $parser);
    $pluginValidator = new PluginValidator($db, $parser);
    $listing         = new Listing($db, $cache, $legacyValidator, $pluginValidator);

    return $listing->getInstalled()->filter(static function (ListingItem $e) use ($query) {
        if (stripos($e->getName(), $query) !== false) {
            $e->setName(highlightSearchTerm($e->getName(), $query));
            return true;
        }

        return false;
    });
}
