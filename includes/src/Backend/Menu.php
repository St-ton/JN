<?php declare(strict_types=1);

namespace JTL\Backend;

use JTL\DB\DbInterface;
use JTL\L10n\GetText;
use JTL\Plugin\Helper as PluginHelper;
use JTL\Plugin\State;
use JTL\Router\BackendRouter;
use JTL\Shop;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class Menu
 * @package JTL\Backend
 */
class Menu
{
    /**
     * @param DbInterface  $db
     * @param AdminAccount $account
     * @param GetText      $getText
     */
    public function __construct(private DbInterface $db, private AdminAccount $account, private GetText $getText)
    {
        $getText->loadAdminLocale('menu');
    }

    /**
     * @param ServerRequestInterface $request
     * @return array
     */
    public function build(ServerRequestInterface $request): array
    {
        $adminURL                     = Shop::getAdminURL() . '/';
        $curScriptFileNameWithRequest = $request->getUri()->getPath();
        $requestedPath                = \parse_url($request->getUri()->getPath(), \PHP_URL_PATH);
        $mainGroups                   = [];
        $configLink                   = $adminURL . BackendRouter::ROUTE_CONFIG;
        $adminMenu                    = [
            \__('Marketing')      => (object)[
                'icon'  => 'marketing',
                'items' => [
                    \__('Orders')     => (object)[
                        'link'        => $adminURL . BackendRouter::ROUTE_ORDERS,
                        'permissions' => Permissions::ORDER_VIEW,
                    ],
                    \__('Promotions') => [
                        \__('Newsletter') => (object)[
                            'link'           => $adminURL . BackendRouter::ROUTE_NEWSLETTER,
                            'permissions'    => 'MODULE_NEWSLETTER_VIEW',
                            'section'        => \CONF_NEWSLETTER,
                            'specialSetting' => true,
                            'settingsAnchor' => '#einstellungen',
                        ],
                        \__('Blog posts') => (object)[
                            'link'           => $adminURL . BackendRouter::ROUTE_NEWS,
                            'permissions'    => Permissions::CONTENT_NEWS_SYSTEM_VIEW,
                            'section'        => \CONF_NEWS,
                            'specialSetting' => true,
                            'settingsAnchor' => '#einstellungen',
                        ],
                        \__('Coupons')    => (object)[
                            'link'        => $adminURL . BackendRouter::ROUTE_COUPONS,
                            'permissions' => Permissions::ORDER_COUPON_VIEW,
                        ],
                        \__('Free gifts') => (object)[
                            'link'           => $adminURL . BackendRouter::ROUTE_GIFTS,
                            'permissions'    => Permissions::MODULE_GIFT_VIEW,
                            'section'        => \CONF_SONSTIGES,
                            'specialSetting' => true,
                            'settingsAnchor' => '#einstellungen',
                        ],
                    ],
                    \__('Statistics') => [
                        \__('Sales')             => (object)[
                            'link'        => $adminURL . BackendRouter::ROUTE_STATS . '/4',
                            'permissions' => Permissions::STATS_EXCHANGE_VIEW,
                        ],
                        \__('Campaigns')         => (object)[
                            'link'        => $adminURL . BackendRouter::ROUTE_CAMPAIGN . '#globalestats',
                            'permissions' => Permissions::STATS_CAMPAIGN_VIEW,
                        ],
                        \__('Baskets')           => (object)[
                            'link'        => $adminURL . BackendRouter::ROUTE_PERSISTENT_CART,
                            'permissions' => Permissions::MODULE_SAVED_BASKETS_VIEW,
                        ],
                        \__('Coupon statistics') => (object)[
                            'link'        => $adminURL . BackendRouter::ROUTE_COUPON_STATS,
                            'permissions' => Permissions::STATS_COUPON_VIEW,
                        ],
                        \__('Visitors')          => (object)[
                            'link'        => $adminURL . BackendRouter::ROUTE_STATS . '/1',
                            'permissions' => Permissions::STATS_VISITOR_VIEW,
                        ],
                        \__('Referrer pages')    => (object)[
                            'link'        => $adminURL . BackendRouter::ROUTE_STATS . '/2',
                            'permissions' => Permissions::STATS_VISITOR_LOCATION_VIEW,
                        ],
                        \__('Entry pages')       => (object)[
                            'link'        => $adminURL . BackendRouter::ROUTE_STATS . '/5',
                            'permissions' => Permissions::STATS_LANDINGPAGES_VIEW,
                        ],
                        \__('Search engines')    => (object)[
                            'link'        => $adminURL . BackendRouter::ROUTE_STATS . '/3',
                            'permissions' => Permissions::STATS_CRAWLER_VIEW,
                        ],
                        \__('Search queries')    => (object)[
                            'link'        => $adminURL . BackendRouter::ROUTE_LIVESEARCH,
                            'permissions' => Permissions::MODULE_LIVESEARCH_VIEW,
                        ],
                    ],
                    \__('Reports')    => (object)[
                        'link'        => $adminURL . BackendRouter::ROUTE_STATUSMAIL,
                        'permissions' => Permissions::EMAIL_REPORTS_VIEW,
                    ],
                ]
            ],
            \__('Appearance')     => (object)[
                'icon'  => 'styling',
                'items' => [
                    \__('OnPage Composer')  => (object)[
                        'link'        => $adminURL . BackendRouter::ROUTE_OPCCC,
                        'permissions' => Permissions::OPC_VIEW,
                    ],
                    \__('Default views')    => [
                        \__('Home page')        => (object)[
                            'link'        => $configLink . '/' . \CONF_STARTSEITE,
                            'permissions' => Permissions::SETTINGS_STARTPAGE_VIEW,
                            'section'     => \CONF_STARTSEITE,
                        ],
                        \__('Item overview')    => (object)[
                            'link'           => $adminURL . BackendRouter::ROUTE_NAVFILTER,
                            'permissions'    => Permissions::SETTINGS_NAVIGATION_FILTER_VIEW,
                            'section'        => \CONF_NAVIGATIONSFILTER,
                            'specialSetting' => true,
                        ],
                        \__('Item detail page') => (object)[
                            'link'        => $configLink . '/' . \CONF_ARTIKELDETAILS,
                            'permissions' => Permissions::SETTINGS_ARTICLEDETAILS_VIEW,
                            'section'     => \CONF_ARTIKELDETAILS,
                        ],
                        \__('Checkout')         => (object)[
                            'link'        => $configLink . '/' . \CONF_KAUFABWICKLUNG,
                            'permissions' => Permissions::SETTINGS_BASKET_VIEW,
                            'section'     => \CONF_KAUFABWICKLUNG,
                        ],
                        \__('Comparison list')  => (object)[
                            'link'           => $adminURL . BackendRouter::ROUTE_COMPARELIST,
                            'permissions'    => Permissions::MODULE_COMPARELIST_VIEW,
                            'section'        => \CONF_VERGLEICHSLISTE,
                            'specialSetting' => true,
                            'settingsAnchor' => '#einstellungen',
                        ],
                        \__('Wish list')        => (object)[
                            'link'        => $adminURL . BackendRouter::ROUTE_WISHLIST,
                            'permissions' => Permissions::MODULE_WISHLIST_VIEW,
                        ],
                        \__('Contact form')     => (object)[
                            'link'           => $adminURL . BackendRouter::ROUTE_CONTACT_FORMS,
                            'permissions'    => Permissions::SETTINGS_CONTACTFORM_VIEW,
                            'section'        => \CONF_KONTAKTFORMULAR,
                            'specialSetting' => true,
                            'settingsAnchor' => '#config',
                        ],
                        \__('Registration')     => (object)[
                            'link'        => $configLink . '/' . \CONF_KUNDEN,
                            'permissions' => Permissions::SETTINGS_CUSTOMERFORM_VIEW,
                            'section'     => \CONF_KUNDEN,
                        ],
                    ],
                    \__('Default elements') => [
                        \__('Shop logo')                  => (object)[
                            'link'        => $adminURL . BackendRouter::ROUTE_LOGO,
                            'permissions' => Permissions::DISPLAY_OWN_LOGO_VIEW,
                        ],
                        \__('Search')                     => (object)[
                            'link'           => $adminURL . BackendRouter::ROUTE_SEARCHCONFIG,
                            'permissions'    => Permissions::SETTINGS_ARTICLEOVERVIEW_VIEW,
                            'section'        => \CONF_ARTIKELUEBERSICHT,
                            'specialSetting' => true,
                        ],
                        \__('Price history')              => (object)[
                            'link'           => $adminURL . BackendRouter::ROUTE_PRICEHISTORY,
                            'permissions'    => Permissions::MODULE_PRICECHART_VIEW,
                            'section'        => \CONF_PREISVERLAUF,
                            'specialSetting' => true,
                        ],
                        \__('Question on item')           => (object)[
                            'link'                  => $configLink . '/' . \CONF_ARTIKELDETAILS .
                                '?group=configgroup_5_product_question',
                            'permissions'           => Permissions::SETTINGS_ARTICLEDETAILS_VIEW,
                            'excludeFromAccessView' => true,
                            'section'               => \CONF_ARTIKELDETAILS,
                            'group'                 => 'configgroup_5_product_question',
                        ],
                        \__('Availability notifications') => (object)[
                            'link'                  => $configLink . '/' . \CONF_ARTIKELDETAILS .
                                '?group=configgroup_5_product_available',
                            'permissions'           => Permissions::SETTINGS_ARTICLEDETAILS_VIEW,
                            'excludeFromAccessView' => true,
                            'section'               => \CONF_ARTIKELDETAILS,
                            'group'                 => 'configgroup_5_product_available',
                        ],
                        \__('Item badges')                => (object)[
                            'link'        => $adminURL . BackendRouter::ROUTE_SEARCHSPECIALOVERLAYS,
                            'permissions' => Permissions::DISPLAY_ARTICLEOVERLAYS_VIEW,
                        ],
                        \__('Footer / Boxes')             => (object)[
                            'link'        => $adminURL . BackendRouter::ROUTE_BOXES,
                            'permissions' => Permissions::BOXES_VIEW,
                        ],
                        \__('Selection wizard')           => (object)[
                            'link'           => $adminURL . BackendRouter::ROUTE_SELECTION_WIZARD,
                            'permissions'    => Permissions::EXTENSION_SELECTIONWIZARD_VIEW,
                            'section'        => \CONF_AUSWAHLASSISTENT,
                            'specialSetting' => true,
                            'settingsAnchor' => '#config',
                        ],
                        \__('Warehouse display')          => (object)[
                            'link'        => $adminURL . BackendRouter::ROUTE_WAREHOUSES,
                            'permissions' => Permissions::WAREHOUSE_VIEW,
                        ],
                        \__('Reviews')                    => (object)[
                            'link'           => $adminURL . BackendRouter::ROUTE_REVIEWS,
                            'permissions'    => Permissions::MODULE_VOTESYSTEM_VIEW,
                            'section'        => \CONF_BEWERTUNG,
                            'specialSetting' => true,
                            'settingsAnchor' => '#einstellungen',
                        ],
                        \__('Consent manager')            => (object)[
                            'link'           => $adminURL . BackendRouter::ROUTE_CONSENT,
                            'permissions'    => Permissions::CONSENT_MANAGER,
                            'section'        => \CONF_CONSENTMANAGER,
                            'specialSetting' => true,
                            'settingsAnchor' => '#config',
                        ],
                    ],
                    \__('Custom contents')  => [
                        \__('Pages')                  => (object)[
                            'link'        => $adminURL . BackendRouter::ROUTE_LINKS,
                            'permissions' => Permissions::CONTENT_PAGE_VIEW,
                        ],
                        \__('Terms / Withdrawal')     => (object)[
                            'link'        => $adminURL . BackendRouter::ROUTE_TAC,
                            'permissions' => Permissions::ORDER_AGB_WRB_VIEW,
                        ],
                        \__('Extended customer data') => (object)[
                            'link'           => $adminURL . BackendRouter::ROUTE_CUSTOMERFIELDS,
                            'permissions'    => Permissions::ORDER_CUSTOMERFIELDS_VIEW,
                            'section'        => \CONF_KUNDENFELD,
                            'specialSetting' => true,
                            'settingsAnchor' => '#config',
                        ],
                        \__('Check boxes')            => (object)[
                            'link'        => $adminURL . BackendRouter::ROUTE_CHECKBOX,
                            'permissions' => Permissions::CHECKBOXES_VIEW,
                        ],
                        \__('Banners')                => (object)[
                            'link'        => $adminURL . BackendRouter::ROUTE_BANNER,
                            'permissions' => Permissions::DISPLAY_BANNER_VIEW,
                        ],
                        \__('Sliders')                => (object)[
                            'link'        => $adminURL . BackendRouter::ROUTE_SLIDERS,
                            'permissions' => Permissions::SLIDER_VIEW,
                        ],
                    ],
                    \__('Settings')         => [
                        \__('Global')         => (object)[
                            'link'        => $configLink . '/' . \CONF_GLOBAL,
                            'permissions' => Permissions::SETTINGS_GLOBAL_VIEW,
                            'section'     => \CONF_GLOBAL,
                        ],
                        \__('Templates')      => (object)[
                            'link'        => $adminURL . BackendRouter::ROUTE_TEMPLATE,
                            'permissions' => Permissions::DISPLAY_TEMPLATE_VIEW,
                        ],
                        \__('Images')         => (object)[
                            'link'           => $adminURL . BackendRouter::ROUTE_IMAGES,
                            'permissions'    => Permissions::SETTINGS_IMAGES_VIEW,
                            'section'        => \CONF_BILDER,
                            'specialSetting' => true,
                        ],
                        \__('Watermark')      => (object)[
                            'link'        => $adminURL . BackendRouter::ROUTE_BRANDING,
                            'permissions' => Permissions::DISPLAY_BRANDING_VIEW,
                        ],
                        \__('Number formats') => (object)[
                            'link'        => $adminURL . BackendRouter::ROUTE_SEPARATOR,
                            'permissions' => Permissions::SETTINGS_SEPARATOR_VIEW,
                        ],
                    ]
                ]
            ],
            \__('Plug-ins')       => (object)[
                'icon'  => 'plugins',
                'items' => [
                    \__('Plug-in manager')     => (object)[
                        'link'        => $adminURL . BackendRouter::ROUTE_PLUGIN_MANAGER,
                        'permissions' => Permissions::PLUGIN_ADMIN_VIEW,
                    ],
                    \__('JTL-Extension Store') => (object)[
                        'link'        => 'https://jtl-url.de/exs',
                        'target'      => '_blank',
                        'permissions' => Permissions::LICENSE_MANAGER
                    ],
                    \__('My purchases')        => (object)[
                        'link'        => $adminURL . BackendRouter::ROUTE_LICENSE,
                        'permissions' => Permissions::LICENSE_MANAGER,
                    ],
                    \__('Installed plug-ins')  => 'DYNAMIC_PLUGINS',
                ],
            ],
            \__('Administration') => (object)[
                'icon'  => 'administration',
                'items' => [
                    \__('Approvals')       => (object)[
                        'link'        => $adminURL . BackendRouter::ROUTE_ACTIVATE,
                        'permissions' => Permissions::UNLOCK_CENTRAL_VIEW,
                    ],
                    \__('Import')          => [
                        \__('Newsletters')  => (object)[
                            'link'        => $adminURL . BackendRouter::ROUTE_NEWSLETTER_IMPORT,
                            'permissions' => Permissions::IMPORT_NEWSLETTER_RECEIVER_VIEW,
                        ],
                        \__('Customers')    => (object)[
                            'link'        => $adminURL . BackendRouter::ROUTE_CUSTOMER_IMPORT,
                            'permissions' => Permissions::IMPORT_CUSTOMER_VIEW,
                        ],
                        \__('Postal codes') => (object)[
                            'link'        => $adminURL . BackendRouter::ROUTE_ZIP_IMPORT,
                            'permissions' => Permissions::PLZ_ORT_IMPORT_VIEW,
                        ],
                    ],
                    \__('Export')          => [
                        \__('Site map')       => (object)[
                            'link'           => $adminURL . BackendRouter::ROUTE_SITEMAP_EXPORT,
                            'permissions'    => Permissions::EXPORT_SITEMAP_VIEW,
                            'section'        => \CONF_SITEMAP,
                            'specialSetting' => true,
                            'settingsAnchor' => '#einstellungen',
                        ],
                        \__('RSS feed')       => (object)[
                            'link'           => $adminURL . BackendRouter::ROUTE_RSS,
                            'permissions'    => Permissions::EXPORT_RSSFEED_VIEW,
                            'section'        => \CONF_RSS,
                            'specialSetting' => true,
                        ],
                        \__('Other formats')  => (object)[
                            'link'        => $adminURL . BackendRouter::ROUTE_EXPORT,
                            'permissions' => Permissions::EXPORT_FORMATS_VIEW,
                        ],
                        \__('Export manager') => (object)[
                            'link'        => $adminURL . BackendRouter::ROUTE_EXPORT_QUEUE,
                            'permissions' => Permissions::EXPORT_SCHEDULE_VIEW,
                        ],
                    ],
                    \__('Payment methods') => (object)[
                        'link'        => $adminURL . BackendRouter::ROUTE_PAYMENT_METHODS,
                        'permissions' => Permissions::ORDER_PAYMENT_VIEW,
                    ],
                    \__('Shipments')       => [
                        \__('Shipping methods')     => (object)[
                            'link'        => $adminURL . BackendRouter::ROUTE_SHIPPING_METHODS,
                            'permissions' => Permissions::ORDER_SHIPMENT_VIEW,
                        ],
                        \__('Additional packaging') => (object)[
                            'link'        => $adminURL . BackendRouter::ROUTE_PACKAGINGS,
                            'permissions' => Permissions::ORDER_PACKAGE_VIEW,
                        ],
                        \__('Country manager')      => (object)[
                            'link'        => $adminURL . BackendRouter::ROUTE_COUNTRIES,
                            'permissions' => Permissions::COUNTRY_VIEW,
                        ],
                    ],
                    \__('Email')           => [
                        \__('Server')          => (object)[
                            'link'        => $configLink . '/' . \CONF_EMAILS,
                            'permissions' => Permissions::SETTINGS_EMAILS_VIEW,
                            'section'     => \CONF_EMAILS,
                        ],
                        \__('Email templates') => (object)[
                            'link'        => $adminURL . BackendRouter::ROUTE_EMAILTEMPLATES,
                            'permissions' => Permissions::CONTENT_EMAIL_TEMPLATE_VIEW,
                        ],
                        \__('Blacklist')       => (object)[
                            'link'           => $adminURL . BackendRouter::ROUTE_EMAILBLOCKLIST,
                            'permissions'    => Permissions::SETTINGS_EMAIL_BLACKLIST_VIEW,
                            'section'        => \CONF_EMAILBLACKLIST,
                            'specialSetting' => true,
                        ],
                        \__('Log')             => (object)[
                            'link'        => $adminURL . BackendRouter::ROUTE_EMAILHISTORY,
                            'permissions' => Permissions::EMAILHISTORY_VIEW,
                        ],
                    ],
                    \__('SEO')             => [
                        \__('Meta data')  => (object)[
                            'link'           => $adminURL . BackendRouter::ROUTE_META,
                            'permissions'    => Permissions::SETTINGS_GLOBAL_META_VIEW,
                            'section'        => \CONF_METAANGABEN,
                            'specialSetting' => true,
                            'settingsAnchor' => '#einstellungen',
                        ],
                        \__('Forwarding') => (object)[
                            'link'        => $adminURL . BackendRouter::ROUTE_REDIRECT,
                            'permissions' => Permissions::REDIRECT_VIEW,
                        ],
                        \__('Site map')   => (object)[
                            'link'        => $adminURL . BackendRouter::ROUTE_SITEMAP,
                            'permissions' => Permissions::SETTINGS_SITEMAP_VIEW,
                        ],
                        \__('SEO path')   => (object)[
                            'link'           => $adminURL . BackendRouter::ROUTE_SEARCHSPECIAL,
                            'permissions'    => Permissions::SETTINGS_SPECIALPRODUCTS_VIEW,
                            'section'        => \CONF_SUCHSPECIAL,
                            'specialSetting' => true,
                            'settingsAnchor' => '#einstellungen',
                        ],
                    ],
                    \__('Languages')       => (object)[
                        'link'        => $adminURL . BackendRouter::ROUTE_LANGUAGE,
                        'permissions' => Permissions::LANGUAGE_VIEW
                    ],
                    \__('Accounts')        => [
                        \__('Users')                    => (object)[
                            'link'        => $adminURL . BackendRouter::ROUTE_USERS,
                            'permissions' => Permissions::ACCOUNT_VIEW,
                        ],
                        \__('JTL-Wawi synchronisation') => (object)[
                            'link'        => $adminURL . BackendRouter::ROUTE_SYNC,
                            'permissions' => Permissions::WAWI_SYNC_VIEW,
                        ],
                    ],
                    \__('Troubleshooting') => [
                        \__('System diagnostics') => (object)[
                            'link'        => $adminURL . BackendRouter::ROUTE_STATUS,
                            'permissions' => Permissions::DIAGNOSTIC_VIEW,
                        ],
                        \__('Log')                => (object)[
                            'link'        => $adminURL . BackendRouter::ROUTE_SYSTEMLOG,
                            'permissions' => Permissions::SYSTEMLOG_VIEW,
                        ],
                        \__('Item images')        => (object)[
                            'link'        => $adminURL . BackendRouter::ROUTE_IMAGE_MANAGEMENT,
                            'permissions' => Permissions::DISPLAY_IMAGES_VIEW,
                        ],
                        \__('Plug-in profiler')   => (object)[
                            'link'        => $adminURL . BackendRouter::ROUTE_PROFILER,
                            'permissions' => Permissions::PROFILER_VIEW,
                        ],

                    ],
                    \__('System')          => [
                        \__('Cache')      => (object)[
                            'link'           => $adminURL . BackendRouter::ROUTE_CACHE,
                            'permissions'    => Permissions::OBJECTCACHE_VIEW,
                            'section'        => \CONF_CACHING,
                            'specialSetting' => true,
                            'settingsAnchor' => '#settings',
                        ],
                        \__('Cron')       => (object)[
                            'link'           => $adminURL . BackendRouter::ROUTE_CRON,
                            'permissions'    => Permissions::CRON_VIEW,
                            'section'        => \CONF_CRON,
                            'specialSetting' => true,
                            'settingsAnchor' => '#config',
                        ],
                        \__('Filesystem') => (object)[
                            'link'           => $adminURL . BackendRouter::ROUTE_FILESYSTEM,
                            'permissions'    => Permissions::FILESYSTEM_VIEW,
                            'section'        => \CONF_FS,
                            'specialSetting' => true,
                        ],
                        \__('Update')     => (object)[
                            'link'        => $adminURL . BackendRouter::ROUTE_DBUPDATER,
                            'permissions' => Permissions::SHOP_UPDATE_VIEW,
                        ],
                        \__('Reset')      => (object)[
                            'link'        => $adminURL . BackendRouter::ROUTE_RESET,
                            'permissions' => Permissions::RESET_SHOP_VIEW,
                        ],
                        \__('Set up')     => (object)[
                            'link'        => $adminURL . BackendRouter::ROUTE_WIZARD,
                            'permissions' => Permissions::WIZARD_VIEW,
                        ],
                    ],
                ]
            ],
        ];

        $sectionMenuMapping = [];
        $rootKey            = 0;
        foreach ($adminMenu as $menuName => $menu) {
            foreach ($menu->items as $subMenuName => $subMenu) {
                if (!\is_array($subMenu)) {
                    continue;
                }
                foreach ($subMenu as $itemName => $item) {
                    if (!isset($item->section)) {
                        continue;
                    }
                    if (!isset($sectionMenuMapping[$item->section])) {
                        $sectionMenuMapping[$item->section] = [];
                    }

                    $groupName = $item->group ?? 'all';

                    $sectionMenuMapping[$item->section][$groupName] = (object)[
                        'path'           => $menuName . ' -&gt; ' . $subMenuName . ' -&gt; ' . $itemName,
                        'url'            => $item->link,
                        'specialSetting' => $item->specialSetting ?? false,
                        'settingsAnchor' => $item->settingsAnchor ?? '',
                    ];
                }
            }
        }
        foreach ($adminMenu as $rootName => $rootEntry) {
            $rootKey   = (string)$rootKey;
            $mainGroup = (object)[
                'cName'           => $rootName,
                'icon'            => $rootEntry->icon,
                'oLink_arr'       => [],
                'oLinkGruppe_arr' => [],
                'key'             => $rootKey,
                'active'          => false
            ];

            $secondKey = 0;
            foreach ($rootEntry->items as $secondName => $secondEntry) {
                $linkGruppe = (object)[
                    'cName'     => $secondName,
                    'oLink_arr' => [],
                    'key'       => $rootKey . $secondKey,
                    'active'    => false
                ];

                if ($secondEntry === 'DYNAMIC_PLUGINS') {
                    if (\SAFE_MODE === true || !$this->account->permission(Permissions::PLUGIN_ADMIN_VIEW)) {
                        continue;
                    }
                    $pluginLinks = $this->db->getObjects(
                        'SELECT DISTINCT p.kPlugin, p.cName, p.nPrio
                            FROM tplugin AS p INNER JOIN tpluginadminmenu AS pam
                                ON p.kPlugin = pam.kPlugin
                            WHERE p.nStatus = :state
                            ORDER BY p.nPrio, p.cName',
                        ['state' => State::ACTIVATED]
                    );

                    foreach ($pluginLinks as $pluginLink) {
                        $pluginID = (int)$pluginLink->kPlugin;
                        $this->getText->loadPluginLocale(
                            'base',
                            PluginHelper::getLoaderByPluginID($pluginID)->init($pluginID)
                        );

                        $link = (object)[
                            'cLinkname' => \__($pluginLink->cName),
                            'cURL'      => $adminURL . BackendRouter::ROUTE_PLUGIN . '/' . $pluginID,
                            'cRecht'    => 'PLUGIN_ADMIN_VIEW',
                            'key'       => $rootKey . $secondKey . $pluginID,
                            'active'    => false
                        ];

                        $linkGruppe->oLink_arr[] = $link;
                        if (\str_ends_with($requestedPath, BackendRouter::ROUTE_PLUGIN . '/' . $pluginID)) {
                            $mainGroup->active  = true;
                            $linkGruppe->active = true;
                            $link->active       = true;
                        }
                    }
                } else {
                    $thirdKey = 0;

                    if (\is_object($secondEntry)) {
                        if (isset($secondEntry->permissions)
                            && !$this->account->permission($secondEntry->permissions)
                        ) {
                            continue;
                        }
                        $linkGruppe->oLink_arr = (object)[
                            'cLinkname' => $secondName,
                            'cURL'      => $secondEntry->link,
                            'cRecht'    => $secondEntry->permissions ?? null,
                            'target'    => $secondEntry->target ?? null,
                            'active'    => false
                        ];
                        if (str_ends_with($linkGruppe->oLink_arr->cURL, $requestedPath)) {
                            $mainGroup->active  = true;
                            $linkGruppe->active = true;
                        }
                    } else {
                        foreach ($secondEntry as $thirdName => $thirdEntry) {
                            if (\is_object($thirdEntry)) {
                                $link = (object)[
                                    'cLinkname' => $thirdName,
                                    'cURL'      => $thirdEntry->link,
                                    'cRecht'    => $thirdEntry->permissions,
                                    'key'       => $rootKey . $secondKey . $thirdKey,
                                    'active'    => false
                                ];
                            } else {
                                continue;
                            }
                            if (!$this->account->permission($link->cRecht)) {
                                continue;
                            }
                            $urlPath = \parse_url($link->cURL, \PHP_URL_PATH) ?? '';
                            if ($requestedPath === $urlPath) {
                                $hash = \mb_strpos($link->cURL, '#');
                                $url  = $link->cURL;
                                if ($hash !== false) {
                                    $url = \mb_substr($link->cURL, 0, $hash);
                                }
                                $linkPath = \str_replace(Shop::getURL(), '', $url);
                                if (\str_contains($curScriptFileNameWithRequest, $linkPath)) {
                                    $mainGroup->active  = true;
                                    $linkGruppe->active = true;
                                    $link->active       = true;
                                }
                            }
                            $linkGruppe->oLink_arr[] = $link;
                            $thirdKey++;
                        }
                    }
                }
                if (\is_object($linkGruppe->oLink_arr) || \count($linkGruppe->oLink_arr) > 0) {
                    $mainGroup->oLinkGruppe_arr[] = $linkGruppe;
                }
                $secondKey++;
            }

            if (\count($mainGroup->oLinkGruppe_arr) > 0) {
                $mainGroups[] = $mainGroup;
            }
            $rootKey++;
        }

        return $mainGroups;
    }
}
