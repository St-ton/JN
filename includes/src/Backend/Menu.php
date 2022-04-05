<?php declare(strict_types=1);

namespace JTL\Backend;

use JTL\DB\DbInterface;
use JTL\Helpers\Request;
use JTL\L10n\GetText;
use JTL\Plugin\Helper as PluginHelper;
use JTL\Plugin\State;
use JTL\Router\AdminRouter;
use League\Route\Router;
use Shop;

class Menu
{
    private Router $router;
    private DbInterface $db;
    private AdminAccount $account;
    private GetText $getText;

    public function __construct(Router $router, DbInterface $db, AdminAccount $account, GetText $getText)
    {
        $this->router  = $router;
        $this->db      = $db;
        $this->account = $account;
        $this->getText = $getText;
    }

    public function build()
    {
        $baseURL                      = Shop::getURL();
        $adminURL                     = Shop::getAdminURL();
        $curScriptFileNameWithRequest = \basename($_SERVER['REQUEST_URI'] ?? 'index.php');
        /** @var array $adminMenu */
        $adminMenu = [
            \__('Marketing')      => (object)[
                'icon'  => 'marketing',
                'items' => [
                    \__('Orders')     => (object)[
                        'link'        => $baseURL . $this->router->getNamedRoute(AdminRouter::ROUTE_ORDERS)->getPath(),
                        'permissions' => 'ORDER_VIEW',
                    ],
                    \__('Promotions') => [
                        \__('Newsletter') => (object)[
                            'link'           => 'newsletter.php',
                            'permissions'    => 'MODULE_NEWSLETTER_VIEW',
                            'section'        => \CONF_NEWSLETTER,
                            'specialSetting' => true,
                            'settingsAnchor' => '#einstellungen',
                        ],
                        \__('Blog posts') => (object)[
                            'link'           => $baseURL . $this->router->getNamedRoute(AdminRouter::ROUTE_NEWS)->getPath(),
                            'permissions'    => 'CONTENT_NEWS_SYSTEM_VIEW',
                            'section'        => \CONF_NEWS,
                            'specialSetting' => true,
                            'settingsAnchor' => '#einstellungen',
                        ],
                        \__('Coupons')    => (object)[
                            'link'        => $baseURL . $this->router->getNamedRoute(AdminRouter::ROUTE_COUPONS)->getPath(),
                            'permissions' => 'ORDER_COUPON_VIEW',
                        ],
                        \__('Free gifts') => (object)[
                            'link'           => 'gratisgeschenk.php',
                            'permissions'    => 'MODULE_GIFT_VIEW',
                            'section'        => \CONF_SONSTIGES,
                            'specialSetting' => true,
                            'settingsAnchor' => '#einstellungen',
                        ],
                    ],
                    \__('Statistics') => [
                        \__('Sales')             => (object)[
                            'link'        => $baseURL . $this->router->getNamedRoute(AdminRouter::ROUTE_STATS)->getPath() . '?s=4',
                            'permissions' => 'STATS_EXCHANGE_VIEW',
                        ],
                        \__('Campaigns')         => (object)[
                            'link'        => 'kampagne.php#globalestats',
                            'permissions' => 'STATS_CAMPAIGN_VIEW',
                        ],
                        \__('Baskets')           => (object)[
                            'link'        => 'warenkorbpers.php',
                            'permissions' => 'MODULE_SAVED_BASKETS_VIEW',
                        ],
                        \__('Coupon statistics') => (object)[
                            'link'        => 'kuponstatistik.php',
                            'permissions' => 'STATS_COUPON_VIEW',
                        ],
                        \__('Visitors')          => (object)[
                            'link'        => $baseURL . $this->router->getNamedRoute(AdminRouter::ROUTE_STATS)->getPath() . '?s=1',
                            'permissions' => 'STATS_VISITOR_VIEW',
                        ],
                        \__('Referrer pages')    => (object)[
                            'link'        => $baseURL . $this->router->getNamedRoute(AdminRouter::ROUTE_STATS)->getPath() . '?s=2',
                            'permissions' => 'STATS_VISITOR_LOCATION_VIEW',
                        ],
                        \__('Entry pages')       => (object)[
                            'link'        => $baseURL . $this->router->getNamedRoute(AdminRouter::ROUTE_STATS)->getPath() . '?s=5',
                            'permissions' => 'STATS_LANDINGPAGES_VIEW',
                        ],
                        \__('Search engines')    => (object)[
                            'link'        => $baseURL . $this->router->getNamedRoute(AdminRouter::ROUTE_STATS)->getPath() . '?s=3',
                            'permissions' => 'STATS_CRAWLER_VIEW',
                        ],
                        \__('Search queries')    => (object)[
                            'link'        => 'livesuche.php',
                            'permissions' => 'MODULE_LIVESEARCH_VIEW',
                        ],
                    ],
                    \__('Reports')    => (object)[
                        'link'        => $baseURL . $this->router->getNamedRoute(AdminRouter::ROUTE_STATUSMAIL)->getPath(),
                        'permissions' => 'EMAIL_REPORTS_VIEW',
                    ],
                ]
            ],
            \__('Appearance')     => (object)[
                'icon'  => 'styling',
                'items' => [
                    \__('OnPage Composer')  => (object)[
                        'link'        => 'opc-controlcenter.php',
                        'permissions' => 'OPC_VIEW',
                    ],
                    \__('Default views')    => [
                        \__('Home page')        => (object)[
                            'link'        => 'einstellungen.php?kSektion=' . \CONF_STARTSEITE,
                            'permissions' => 'SETTINGS_STARTPAGE_VIEW',
                            'section'     => \CONF_STARTSEITE,
                        ],
                        \__('Item overview')    => (object)[
                            'link'           => 'navigationsfilter.php',
                            'permissions'    => 'SETTINGS_NAVIGATION_FILTER_VIEW',
                            'section'        => \CONF_NAVIGATIONSFILTER,
                            'specialSetting' => true,
                        ],
                        \__('Item detail page') => (object)[
                            'link'        => 'einstellungen.php?kSektion=' . \CONF_ARTIKELDETAILS,
                            'permissions' => 'SETTINGS_ARTICLEDETAILS_VIEW',
                            'section'     => \CONF_ARTIKELDETAILS,
                        ],
                        \__('Checkout')         => (object)[
                            'link'        => 'einstellungen.php?kSektion=' . \CONF_KAUFABWICKLUNG,
                            'permissions' => 'SETTINGS_BASKET_VIEW',
                            'section'     => \CONF_KAUFABWICKLUNG,
                        ],
                        \__('Comparison list')  => (object)[
                            'link'           => $baseURL . $this->router->getNamedRoute(AdminRouter::ROUTE_COMPARELIST)->getPath(),
                            'permissions'    => 'MODULE_COMPARELIST_VIEW',
                            'section'        => \CONF_VERGLEICHSLISTE,
                            'specialSetting' => true,
                            'settingsAnchor' => '#einstellungen',
                        ],
                        \__('Wish list')        => (object)[
                            'link'        => $baseURL . '/wunschliste.php',
                            'permissions' => 'MODULE_WISHLIST_VIEW',
                        ],
                        \__('Contact form')     => (object)[
                            'link'           => $baseURL . $this->router->getNamedRoute(AdminRouter::ROUTE_CONTACT_FORMS)->getPath(),
                            'permissions'    => 'SETTINGS_CONTACTFORM_VIEW',
                            'section'        => \CONF_KONTAKTFORMULAR,
                            'specialSetting' => true,
                            'settingsAnchor' => '#config',
                        ],
                        \__('Registration')     => (object)[
                            'link'        => 'einstellungen.php?kSektion=' . \CONF_KUNDEN,
                            'permissions' => 'SETTINGS_CUSTOMERFORM_VIEW',
                            'section'     => \CONF_KUNDEN,
                        ],
                    ],
                    \__('Default elements') => [
                        \__('Shop logo')                  => (object)[
                            'link'        => $baseURL . $this->router->getNamedRoute(AdminRouter::ROUTE_LOGO)->getPath(),
                            'permissions' => 'DISPLAY_OWN_LOGO_VIEW',
                        ],
                        \__('Search')                     => (object)[
                            'link'           => '@todo!',
                            'permissions'    => 'SETTINGS_ARTICLEOVERVIEW_VIEW',
                            'section'        => \CONF_ARTIKELUEBERSICHT,
                            'specialSetting' => true,
                        ],
                        \__('Price history')              => (object)[
                            'link'           => $baseURL . $this->router->getNamedRoute(AdminRouter::ROUTE_PRICEHISTORY)->getPath(),
                            'permissions'    => 'MODULE_PRICECHART_VIEW',
                            'section'        => \CONF_PREISVERLAUF,
                            'specialSetting' => true,
                        ],
                        \__('Question on item')           => (object)[
                            'link'                  => 'einstellungen.php?kSektion=' . \CONF_ARTIKELDETAILS .
                                '&group=configgroup_5_product_question',
                            'permissions'           => 'SETTINGS_ARTICLEDETAILS_VIEW',
                            'excludeFromAccessView' => true,
                            'section'               => \CONF_ARTIKELDETAILS,
                            'group'                 => 'configgroup_5_product_question',
                        ],
                        \__('Availability notifications') => (object)[
                            'link'                  => 'einstellungen.php?kSektion=' . \CONF_ARTIKELDETAILS .
                                '&group=configgroup_5_product_available',
                            'permissions'           => 'SETTINGS_ARTICLEDETAILS_VIEW',
                            'excludeFromAccessView' => true,
                            'section'               => \CONF_ARTIKELDETAILS,
                            'group'                 => 'configgroup_5_product_available',
                        ],
                        \__('Item badges')                => (object)[
                            'link'        => $baseURL . $this->router->getNamedRoute(AdminRouter::ROUTE_SEARCHSPECIALOVERLAYS)->getPath(),
                            'permissions' => 'DISPLAY_ARTICLEOVERLAYS_VIEW',
                        ],
                        \__('Footer / Boxes')             => (object)[
                            'link'        => 'boxen.php',
                            'permissions' => 'BOXES_VIEW',
                        ],
                        \__('Selection wizard')           => (object)[
                            'link'           => $baseURL . $this->router->getNamedRoute(AdminRouter::ROUTE_SELECTION_WIZARD)->getPath(),
                            'permissions'    => 'EXTENSION_SELECTIONWIZARD_VIEW',
                            'section'        => \CONF_AUSWAHLASSISTENT,
                            'specialSetting' => true,
                            'settingsAnchor' => '#config',
                        ],
                        \__('Warehouse display')          => (object)[
                            'link'        => $baseURL . $this->router->getNamedRoute(AdminRouter::ROUTE_WAREHOUSES)->getPath(),
                            'permissions' => 'WAREHOUSE_VIEW',
                        ],
                        \__('Reviews')                    => (object)[
                            'link'           => $baseURL . $this->router->getNamedRoute(AdminRouter::ROUTE_REVIEWS)->getPath(),
                            'permissions'    => 'MODULE_VOTESYSTEM_VIEW',
                            'section'        => \CONF_BEWERTUNG,
                            'specialSetting' => true,
                            'settingsAnchor' => '#einstellungen',
                        ],
                        \__('Consent manager')            => (object)[
                            'link'           => 'consent.php',
                            'permissions'    => 'CONSENT_MANAGER',
                            'section'        => \CONF_CONSENTMANAGER,
                            'specialSetting' => true,
                            'settingsAnchor' => '#config',
                        ],
                    ],
                    \__('Custom contents')  => [
                        \__('Pages')                  => (object)[
                            'link'        => $baseURL . $this->router->getNamedRoute(AdminRouter::ROUTE_LINKS)->getPath(),
                            'permissions' => 'CONTENT_PAGE_VIEW',
                        ],
                        \__('Terms / Withdrawal')     => (object)[
                            'link'        => $baseURL . $this->router->getNamedRoute(AdminRouter::ROUTE_TAC)->getPath(),
                            'permissions' => 'ORDER_AGB_WRB_VIEW',
                        ],
                        \__('Extended customer data') => (object)[
                            'link'           => $baseURL . $this->router->getNamedRoute(AdminRouter::ROUTE_CUSTOMERFIELDS)->getPath(),
                            'permissions'    => 'ORDER_CUSTOMERFIELDS_VIEW',
                            'section'        => \CONF_KUNDENFELD,
                            'specialSetting' => true,
                            'settingsAnchor' => '#config',
                        ],
                        \__('Check boxes')            => (object)[
                            'link'        => $baseURL . $this->router->getNamedRoute(AdminRouter::ROUTE_CHECKBOX)->getPath(),
                            'permissions' => 'CHECKBOXES_VIEW',
                        ],
                        \__('Banners')                => (object)[
                            'link'        => $baseURL . $this->router->getNamedRoute(AdminRouter::ROUTE_BANNER)->getPath(),
                            'permissions' => 'DISPLAY_BANNER_VIEW',
                        ],
                        \__('Sliders')                => (object)[
                            'link'        => $baseURL . $this->router->getNamedRoute(AdminRouter::ROUTE_SLIDERS)->getPath(),
                            'permissions' => 'SLIDER_VIEW',
                        ],
                    ],
                    \__('Settings')         => [
                        \__('Global')         => (object)[
                            'link'        => 'einstellungen.php?kSektion=' . \CONF_GLOBAL,
                            'permissions' => 'SETTINGS_GLOBAL_VIEW',
                            'section'     => \CONF_GLOBAL,
                        ],
                        \__('Templates')      => (object)[
                            'link'        => 'shoptemplate.php',
                            'permissions' => 'DISPLAY_TEMPLATE_VIEW',
                        ],
                        \__('Images')         => (object)[
                            'link'           => $baseURL . $this->router->getNamedRoute(AdminRouter::ROUTE_IMAGES)->getPath(),
                            'permissions'    => 'SETTINGS_IMAGES_VIEW',
                            'section'        => \CONF_BILDER,
                            'specialSetting' => true,
                        ],
                        \__('Watermark')      => (object)[
                            'link'        => $baseURL . $this->router->getNamedRoute(AdminRouter::ROUTE_BRANDING)->getPath(),
                            'permissions' => 'DISPLAY_BRANDING_VIEW',
                        ],
                        \__('Number formats') => (object)[
                            'link'        => 'trennzeichen.php',
                            'permissions' => 'SETTINGS_SEPARATOR_VIEW',
                        ],
                    ]
                ]
            ],
            \__('Plug-ins')       => (object)[
                'icon'  => 'plugins',
                'items' => [
                    \__('Plug-in manager')     => (object)[
                        'link'        => 'pluginverwaltung.php',
                        'permissions' => 'PLUGIN_ADMIN_VIEW',
                    ],
                    \__('JTL-Extension Store') => (object)[
                        'link'        => 'https://jtl-url.de/exs',
                        'target'      => '_blank',
                        'permissions' => 'LICENSE_MANAGER'
                    ],
                    \__('My purchases')        => (object)[
                        'link'        => 'licenses.php',
                        'permissions' => 'LICENSE_MANAGER',
                    ],
                    \__('Installed plug-ins')  => 'DYNAMIC_PLUGINS',
                ],
            ],
            \__('Administration') => (object)[
                'icon'  => 'administration',
                'items' => [
                    \__('Approvals')       => (object)[
                        'link'        => $baseURL . $this->router->getNamedRoute(AdminRouter::ROUTE_ACTIVATE)->getPath(),
                        'permissions' => 'UNLOCK_CENTRAL_VIEW',
                    ],
                    \__('Import')          => [
                        \__('Newsletters')  => (object)[
                            'link'        => 'newsletterimport.php',
                            'permissions' => 'IMPORT_NEWSLETTER_RECEIVER_VIEW',
                        ],
                        \__('Customers')    => (object)[
                            'link'        => 'kundenimport.php',
                            'permissions' => 'IMPORT_CUSTOMER_VIEW',
                        ],
                        \__('Postal codes') => (object)[
                            'link'        => 'plz_ort_import.php',
                            'permissions' => 'PLZ_ORT_IMPORT_VIEW',
                        ],
                    ],
                    \__('Export')          => [
                        \__('Site map')       => (object)[
                            'link'           => 'sitemapexport.php',
                            'permissions'    => 'EXPORT_SITEMAP_VIEW',
                            'section'        => \CONF_SITEMAP,
                            'specialSetting' => true,
                            'settingsAnchor' => '#einstellungen',
                        ],
                        \__('RSS feed')       => (object)[
                            'link'           => 'rss.php',
                            'permissions'    => 'EXPORT_RSSFEED_VIEW',
                            'section'        => \CONF_RSS,
                            'specialSetting' => true,
                        ],
                        \__('Other formats')  => (object)[
                            'link'        => 'exportformate.php',
                            'permissions' => 'EXPORT_FORMATS_VIEW',
                        ],
                        \__('Export manager') => (object)[
                            'link'        => 'exportformat_queue.php',
                            'permissions' => 'EXPORT_SCHEDULE_VIEW',
                        ],
                    ],
//            __('Payments')        => [
                    \__('Payment methods') => (object)[
                        'link'        => $baseURL . $this->router->getNamedRoute(AdminRouter::ROUTE_PAYMENT_METHODS)->getPath(),
                        'permissions' => 'ORDER_PAYMENT_VIEW',
                    ],
//                __('More payment methods') => (object)[
//                    'link'   => 'zahlungsarten.php',
//                    'permissions' => 'ORDER_PAYMENT_VIEW',
//                ],
//            ],
                    \__('Shipments')       => [
                        \__('Shipping methods')     => (object)[
                            'link'        => $baseURL . $this->router->getNamedRoute(AdminRouter::ROUTE_SHIPPING_METHODS)->getPath(),
                            'permissions' => 'ORDER_SHIPMENT_VIEW',
                        ],
                        \__('Additional packaging') => (object)[
                            'link'        => $baseURL . $this->router->getNamedRoute(AdminRouter::ROUTE_PACKAGINGS)->getPath(),
                            'permissions' => 'ORDER_PACKAGE_VIEW',
                        ],
                        \__('Country manager')      => (object)[
                            'link'        => $baseURL . $this->router->getNamedRoute(AdminRouter::ROUTE_COUNTRIES)->getPath(),
                            'permissions' => 'COUNTRY_VIEW',
                        ],
                    ],
                    \__('Email')           => [
                        \__('Server')          => (object)[
                            'link'        => 'einstellungen.php?kSektion=' . \CONF_EMAILS,
                            'permissions' => 'SETTINGS_EMAILS_VIEW',
                            'section'     => \CONF_EMAILS,
                        ],
                        \__('Email templates') => (object)[
                            'link'        => $baseURL . $this->router->getNamedRoute(AdminRouter::ROUTE_EMAILTEMPLATES)->getPath(),
                            'permissions' => 'CONTENT_EMAIL_TEMPLATE_VIEW',
                        ],
                        \__('Blacklist')       => (object)[
                            'link'           => $baseURL . $this->router->getNamedRoute(AdminRouter::ROUTE_EMAILBLOCKLIST)->getPath(),
                            'permissions'    => 'SETTINGS_EMAIL_BLACKLIST_VIEW',
                            'section'        => \CONF_EMAILBLACKLIST,
                            'specialSetting' => true,
                        ],
                        \__('Log')             => (object)[
                            'link'        => $baseURL . $this->router->getNamedRoute(AdminRouter::ROUTE_EMAILHISTORY)->getPath(),
                            'permissions' => 'EMAILHISTORY_VIEW',
                        ],
                    ],
                    \__('SEO')             => [
                        \__('Meta data')  => (object)[
                            'link'           => $baseURL . $this->router->getNamedRoute(AdminRouter::ROUTE_META)->getPath(),
                            'permissions'    => 'SETTINGS_GLOBAL_META_VIEW',
                            'section'        => \CONF_METAANGABEN,
                            'specialSetting' => true,
                            'settingsAnchor' => '#einstellungen',
                        ],
                        \__('Forwarding') => (object)[
                            'link'        => $baseURL . $this->router->getNamedRoute(AdminRouter::ROUTE_REDIRECT)->getPath(),
                            'permissions' => 'REDIRECT_VIEW',
                        ],
                        \__('Site map')   => (object)[
                            'link'        => $baseURL . $this->router->getNamedRoute(AdminRouter::ROUTE_SITEMAP)->getPath(),
                            'permissions' => 'SETTINGS_SITEMAP_VIEW',
                        ],
                        \__('SEO path')   => (object)[
                            'link'           => 'suchspecials.php',
                            'permissions'    => 'SETTINGS_SPECIALPRODUCTS_VIEW',
                            'section'        => \CONF_SUCHSPECIAL,
                            'specialSetting' => true,
                            'settingsAnchor' => '#einstellungen',
                        ],
                    ],
                    \__('Languages')       => (object)[
                        'link'        => $baseURL . $this->router->getNamedRoute(AdminRouter::ROUTE_LANGUAGE)->getPath(),
                        'permissions' => 'LANGUAGE_VIEW'
                    ],
                    \__('Accounts')        => [
                        \__('Users')                    => (object)[
                            'link'        => $baseURL . $this->router->getNamedRoute(AdminRouter::ROUTE_USERS)->getPath(),
                            'permissions' => 'ACCOUNT_VIEW',
                        ],
                        \__('JTL-Wawi synchronisation') => (object)[
                            'link'        => $baseURL . $this->router->getNamedRoute(AdminRouter::ROUTE_SYNC)->getPath(),
                            'permissions' => 'WAWI_SYNC_VIEW',
                        ],
                    ],
                    \__('Troubleshooting') => [
                        \__('System diagnostics') => (object)[
                            'link'        => $baseURL . $this->router->getNamedRoute(AdminRouter::ROUTE_STATUS)->getPath(),
                            'permissions' => 'DIAGNOSTIC_VIEW',
                        ],
                        \__('Log')                => (object)[
                            'link'        => $baseURL . $this->router->getNamedRoute(AdminRouter::ROUTE_SYSTEMLOG)->getPath(),
                            'permissions' => 'SYSTEMLOG_VIEW',
                        ],
                        \__('Item images')        => (object)[
                            'link'        => $baseURL . $this->router->getNamedRoute(AdminRouter::ROUTE_IMAGE_MANAGEMENT)->getPath(),
                            'permissions' => 'DISPLAY_IMAGES_VIEW',
                        ],
                        \__('Plug-in profiler')   => (object)[
                            'link'        => $baseURL . $this->router->getNamedRoute(AdminRouter::ROUTE_PROFILER)->getPath(),
                            'permissions' => 'PROFILER_VIEW',
                        ],

                    ],
                    \__('System')          => [
                        \__('Cache')      => (object)[
                            'link'           => $baseURL . $this->router->getNamedRoute(AdminRouter::ROUTE_CACHE)->getPath(),
                            'permissions'    => 'OBJECTCACHE_VIEW',
                            'section'        => \CONF_CACHING,
                            'specialSetting' => true,
                            'settingsAnchor' => '#settings',
                        ],
                        \__('Cron')       => (object)[
                            'link'           => $baseURL . $this->router->getNamedRoute(AdminRouter::ROUTE_CRON)->getPath(),
                            'permissions'    => 'CRON_VIEW',
                            'section'        => \CONF_CRON,
                            'specialSetting' => true,
                            'settingsAnchor' => '#config',
                        ],
                        \__('Filesystem') => (object)[
                            'link'           => $baseURL . $this->router->getNamedRoute(AdminRouter::ROUTE_FILESYSTEM)->getPath(),
                            'permissions'    => 'FILESYSTEM_VIEW',
                            'section'        => \CONF_FS,
                            'specialSetting' => true,
                        ],
                        \__('Update')     => (object)[
                            'link'        => $baseURL . $this->router->getNamedRoute(AdminRouter::ROUTE_DBUPDATER)->getPath(),
                            'permissions' => 'SHOP_UPDATE_VIEW',
                        ],
                        \__('Reset')      => (object)[
                            'link'        => $baseURL . $this->router->getNamedRoute(AdminRouter::ROUTE_RESET)->getPath(),
                            'permissions' => 'RESET_SHOP_VIEW',
                        ],
                        \__('Set up')     => (object)[
                            'link'        => 'wizard.php',
                            'permissions' => 'WIZARD_VIEW',
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
            ];

            $secondKey = 0;

            foreach ($rootEntry->items as $secondName => $secondEntry) {
                $linkGruppe = (object)[
                    'cName'     => $secondName,
                    'oLink_arr' => [],
                    'key'       => $rootKey . $secondKey,
                ];

                if ($secondEntry === 'DYNAMIC_PLUGINS') {
                    if (!$this->account->permission('PLUGIN_ADMIN_VIEW') || \SAFE_MODE === true) {
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
                            'cURL'      => $adminURL . '/plugin.php?kPlugin=' . $pluginID,
                            'cRecht'    => 'PLUGIN_ADMIN_VIEW',
                            'key'       => $rootKey . $secondKey . $pluginID,
                        ];

                        $linkGruppe->oLink_arr[] = $link;
                        if (Request::getInt('kPlugin') === $pluginID) {
                            $currentToplevel    = $mainGroup->key;
                            $currentSecondLevel = $linkGruppe->key;
                            $currentThirdLevel  = $link->key;
                        }
                    }
                } else {
                    $thirdKey = 0;

                    if (\is_object($secondEntry)) {
                        if (isset($secondEntry->permissions) && !$this->account->permission($secondEntry->permissions)) {
                            continue;
                        }
                        $linkGruppe->oLink_arr = (object)[
                            'cLinkname' => $secondName,
                            'cURL'      => $secondEntry->link,
                            'cRecht'    => $secondEntry->permissions ?? null,
                            'target'    => $secondEntry->target ?? null,
                        ];
                        if (Request::urlHasEqualRequestParameter($linkGruppe->oLink_arr->cURL, 'kSektion')
                            && \strpos($curScriptFileNameWithRequest, $linkGruppe->oLink_arr->cURL) === 0
                        ) {
                            $currentToplevel    = $mainGroup->key;
                            $currentSecondLevel = $linkGruppe->key;
                        }
                    } else {
                        foreach ($secondEntry as $thirdName => $thirdEntry) {
                            if ($thirdEntry === 'DYNAMIC_JTL_SEARCH' && ($jtlSearch->kPlugin ?? 0) > 0) {
                                $link = (object)[
                                    'cLinkname' => 'JTL Search',
                                    'cURL'      => $adminURL . '/plugin.php?kPlugin=' . $jtlSearch->kPlugin,
                                    'cRecht'    => 'PLUGIN_ADMIN_VIEW',
                                    'key'       => $rootKey . $secondKey . $thirdKey,
                                ];
                            } elseif (\is_object($thirdEntry)) {
                                $link = (object)[
                                    'cLinkname' => $thirdName,
                                    'cURL'      => $thirdEntry->link,
                                    'cRecht'    => $thirdEntry->permissions,
                                    'key'       => $rootKey . $secondKey . $thirdKey,
                                ];
                            } else {
                                continue;
                            }
                            if (!$this->account->permission($link->cRecht)) {
                                continue;
                            }
                            $urlParts             = \parse_url($link->cURL);
                            $urlParts['basename'] = \basename($urlParts['path']);

                            if (empty($urlParts['query'])) {
                                $urlParts['query'] = [];
                            } else {
                                mb_parse_str($urlParts['query'], $urlParts['query']);
                            }

                            if (Request::urlHasEqualRequestParameter($link->cURL, 'kSektion')
                                && \strpos($curScriptFileNameWithRequest, \explode('#', $link->cURL)[0]) === 0
                            ) {
                                $currentToplevel    = $mainGroup->key;
                                $currentSecondLevel = $linkGruppe->key;
                                $currentThirdLevel  = $link->key;
                            }

                            $linkGruppe->oLink_arr[] = $link;
                            $thirdKey++;
                        }
                    }
                }

                if (\is_object($linkGruppe->oLink_arr) || count($linkGruppe->oLink_arr) > 0) {
                    $mainGroup->oLinkGruppe_arr[] = $linkGruppe;
                }
                $secondKey++;
            }

            if (count($mainGroup->oLinkGruppe_arr) > 0) {
                $mainGroups[] = $mainGroup;
            }
            $rootKey++;
        }

        return $mainGroups;
    }
}
