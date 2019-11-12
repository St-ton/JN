<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

\JTL\Shop::Container()->getGetText()->loadAdminLocale('menu');

/** @var array $adminMenu */
$adminMenu = [
    __('Presentation')   => (object)[
        'icon'  => 'display',
        'items' => [
            __('Template settings') => (object)[
                'link'   => 'shoptemplate.php',
                'rights' => 'DISPLAY_TEMPLATE_VIEW',
            ],
            __('Images')            => [
                __('Settings')             => (object)[
                    'link'    => 'bilder.php',
                    'rights'  => 'SETTINGS_IMAGES_VIEW',
                    'section' => CONF_BILDER,
                ],
                __('Watermark')            => (object)[
                    'link'   => 'branding.php',
                    'rights' => 'DISPLAY_BRANDING_VIEW',
                ],
                __('Shop logo')            => (object)[
                    'link'   => 'shoplogouploader.php',
                    'rights' => 'ORDER_AGB_WRB_VIEW',
                ],
                __('Item overlays')        => (object)[
                    'link'   => 'suchspecialoverlay.php',
                    'rights' => 'ORDER_AGB_WRB_VIEW',
                ],
                __('Image administration') => (object)[
                    'link'   => 'bilderverwaltung.php',
                    'rights' => 'DISPLAY_IMAGES_VIEW',
                ],
            ],
        ]
    ],
    __('Contents')       => (object)[
        'icon'  => 'content',
        'items' => [
            __('Item')    => [
                __('Selection wizard') => (object)[
                    'link'    => 'auswahlassistent.php',
                    'rights'  => 'EXTENSION_SELECTIONWIZARD_VIEW',
                    'section' => CONF_AUSWAHLASSISTENT,
                ],
                __('Item details')     => (object)[
                    'link'    => 'einstellungen.php?kSektion=' . CONF_ARTIKELDETAILS,
                    'rights'  => 'SETTINGS_ARTICLEDETAILS_VIEW',
                    'section' => CONF_ARTIKELDETAILS,
                ],
                __('Customer reviews') => (object)[
                    'link'    => 'bewertung.php',
                    'rights'  => 'MODULE_VOTESYSTEM_VIEW',
                    'section' => CONF_BEWERTUNG,
                ],
                __('Price history')    => (object)[
                    'link'    => 'preisverlauf.php',
                    'rights'  => 'MODULE_PRICECHART_VIEW',
                    'section' => CONF_PREISVERLAUF,
                ],
                __('Warehouse')        => (object)[
                    'link'   => 'warenlager.php',
                    'rights' => 'WAREHOUSE_VIEW',
                ],
            ],
            __('Pages')   => [
                __('OnPage Composer')         => (object)[
                    'link'   => 'opc-controlcenter.php',
                    'rights' => 'CONTENT_PAGE_VIEW',
                ],
                __('Front page')              => (object)[
                    'link'    => 'einstellungen.php?kSektion=' . CONF_STARTSEITE,
                    'rights'  => 'SETTINGS_STARTPAGE_VIEW',
                    'section' => CONF_STARTSEITE,
                ],
                __('GTC/cancellation policy') => (object)[
                    'link'   => 'agbwrb.php',
                    'rights' => 'ORDER_AGB_WRB_VIEW',
                ],
                __('Own pages')               => (object)[
                    'link'   => 'links.php',
                    'rights' => 'CONTENT_PAGE_VIEW',
                ],
                __('News')                    => (object)[
                    'link'    => 'news.php',
                    'rights'  => 'CONTENT_NEWS_SYSTEM_VIEW',
                    'section' => CONF_NEWS,
                ],
                __('Box settings')            => (object)[
                    'link'    => 'einstellungen.php?kSektion=' . CONF_BOXEN,
                    'rights'  => 'SETTINGS_BOXES_VIEW',
                    'section' => CONF_BOXEN,
                ],
                __('Boxes')                   => (object)[
                    'link'   => 'boxen.php',
                    'rights' => 'BOXES_VIEW',
                ],
                //            __('IT-Recht Kanzlei') => (object)[
                //                // TODO: correct link and rights
                //                'link' => 'sprache.php',
                //                'rights' => 'LANGUAGE_VIEW',
                //                'partner' => true
                //            ],
                //            __('Trustbadge Reviews') => (object)[
                //                'link' => 'premiumplugin.php?plugin_id=agws_ts_features',
                //                'rights' => 'PLUGIN_ADMIN_VIEW',
                //                'partner' => true
                //            ],
            ],
            __('Forms')   => [
                __('Form settings')            => (object)[
                    'link'    => 'einstellungen.php?kSektion=' . CONF_KUNDEN,
                    'rights'  => 'SETTINGS_CUSTOMERFORM_VIEW',
                    'section' => CONF_KUNDEN,
                ],
                __('Contact form')             => (object)[
                    'link'    => 'kontaktformular.php',
                    'rights'  => 'SETTINGS_CONTACTFORM_VIEW',
                    'section' => CONF_KONTAKTFORMULAR,
                ],
                __('Custom form fields')       => (object)[
                    'link'    => 'kundenfeld.php',
                    'rights'  => 'ORDER_CUSTOMERFIELDS_VIEW',
                    'section' => CONF_KUNDENFELD,
                ],
                __('Check box administration') => (object)[
                    'link'   => 'checkbox.php',
                    'rights' => 'CHECKBOXES_VIEW',
                ],
            ],
            __('E-Mails') => [
                __('Settings')   => (object)[
                    'link'    => 'einstellungen.php?kSektion=' . CONF_EMAILS,
                    'rights'  => 'SETTINGS_EMAILS_VIEW',
                    'section' => CONF_EMAILS,
                ],
                __('Templates')  => (object)[
                    'link'   => 'emailvorlagen.php',
                    'rights' => 'CONTENT_EMAIL_TEMPLATE_VIEW',
                ],
                __('Reports')    => (object)[
                    'link'   => 'statusemail.php',
                    'rights' => 'EMAIL_REPORTS_VIEW',
                ],
                __('Blacklist')  => (object)[
                    'link'    => 'emailblacklist.php',
                    'rights'  => 'SETTINGS_EMAIL_BLACKLIST_VIEW',
                    'section' => CONF_EMAILBLACKLIST,
                ],
                __('E-mail log') => (object)[
                    'link'   => 'emailhistory.php',
                    'rights' => 'EMAILHISTORY_VIEW',
                ],
            ],
        ]
    ],
    __('Purchases')      => (object)[
        'icon'  => 'purchase',
        'items' => [
            __('Shopping cart')     => [
                __('Shopping cart settings') => (object)[
                    'link'    => 'einstellungen.php?kSektion=' . CONF_KAUFABWICKLUNG,
                    'rights'  => 'SETTINGS_BASKET_VIEW',
                    'section' => CONF_KAUFABWICKLUNG,
                ],
                __('Saved baskets')          => (object)[
                    'link'   => 'warenkorbpers.php',
                    'rights' => 'MODULE_SAVED_BASKETS_VIEW',
                ],
            ],
            __('Method of payment') => (object)[
                'link'   => 'zahlungsarten.php',
                'rights' => 'ORDER_PAYMENT_VIEW',
            ],
            __('Delivery')          => [
                __('Shipping methods')     => (object)[
                    'link'   => 'versandarten.php',
                    'rights' => 'ORDER_SHIPMENT_VIEW',
                ],
                __('Additional packaging') => (object)[
                    'link'   => 'zusatzverpackung.php',
                    'rights' => 'ORDER_PACKAGE_VIEW',
                ],
            ],
        ]
    ],
    __('Marketing')      => (object)[
        'icon'  => 'marketing',
        'items' => [
            __('Customer loyalty') => [
                __('Surveys')                 => (object)[
                    'link'    => 'umfrage.php',
                    'rights'  => 'EXTENSION_VOTE_VIEW',
                    'section' => CONF_UMFRAGE,
                ],
                __('Customers win customers') => (object)[
                    'link'    => 'kundenwerbenkunden.php',
                    'rights'  => 'MODULE_CAC_VIEW',
                    'section' => CONF_KUNDENWERBENKUNDEN,
                ],
                __('Coupons')                 => (object)[
                    'link'   => 'kupons.php',
                    'rights' => 'ORDER_COUPON_VIEW',
                ],
                __('Free gift')               => (object)[
                    'link'   => 'gratisgeschenk.php',
                    'rights' => 'MODULE_GIFT_VIEW',
                ],
                __('Wish list')               => (object)[
                    'link'   => 'wunschliste.php',
                    'rights' => 'MODULE_WISHLIST_VIEW',
                ],
                __('Comparison list')         => (object)[
                    'link'    => 'vergleichsliste.php',
                    'rights'  => 'MODULE_COMPARELIST_VIEW',
                    'section' => CONF_VERGLEICHSLISTE,
                ],
                __('Newsletter')              => (object)[
                    'link'    => 'newsletter.php',
                    'rights'  => 'MODULE_NEWSLETTER_VIEW',
                    'section' => CONF_NEWSLETTER,
                ],
            ],
            __('Publicity')        => [
                __('Banner')    => (object)[
                    'link'   => 'banner.php',
                    'rights' => 'DISPLAY_BANNER_VIEW',
                ],
                __('Slider')    => (object)[
                    'link'   => 'slider.php',
                    'rights' => 'SLIDER_VIEW',
                ],
                __('Campaigns') => (object)[
                    'link'   => 'kampagne.php',
                    'rights' => 'STATS_CAMPAIGN_VIEW',
                ],
            ],
            __('Statistics')       => [
                __('Sales revenues')    => (object)[
                    'link'   => 'statistik.php?s=4',
                    'rights' => 'STATS_EXCHANGE_VIEW',
                ],
                __('Visitors')          => (object)[
                    'link'   => 'statistik.php?s=1',
                    'rights' => 'STATS_VISITOR_VIEW',
                ],
                __('Customer origin')   => (object)[
                    'link'   => 'statistik.php?s=2',
                    'rights' => 'STATS_VISITOR_LOCATION_VIEW',
                ],
                __('Search engines')    => (object)[
                    'link'   => 'statistik.php?s=3',
                    'rights' => 'STATS_CRAWLER_VIEW',
                ],
                __('Start pages')       => (object)[
                    'link'   => 'statistik.php?s=5',
                    'rights' => 'STATS_LANDINGPAGES_VIEW',
                ],
                __('Coupon statistics') => (object)[
                    'link'   => 'kuponstatistik.php',
                    'rights' => 'STATS_COUPON_VIEW',
                ],
            ],
        ]
    ],
    __('Import/Export')  => (object)[
        'icon'  => 'import-export',
        'items' => [
            __('Import') => [
                __('Customer data import')        => (object)[
                    'link'   => 'kundenimport.php',
                    'rights' => 'IMPORT_CUSTOMER_VIEW',
                ],
                __('Import newsletter recipient') => (object)[
                    'link'   => 'newsletterimport.php',
                    'rights' => 'IMPORT_NEWSLETTER_RECEIVER_VIEW',
                ],
                __('Zip code import')             => (object)[
                    'link'   => 'plz_ort_import.php',
                    'rights' => 'PLZ_ORT_IMPORT_VIEW',
                ],
            ],
            __('Export') => [
                __('Task scheduler') => (object)[
                    'link'   => 'exportformat_queue.php',
                    'rights' => 'EXPORT_SCHEDULE_VIEW',
                ],
                __('Export formats') => (object)[
                    'link'   => 'exportformate.php',
                    'rights' => 'EXPORT_FORMATS_VIEW',
                ],
                __('Sitemap export') => (object)[
                    'link'    => 'sitemapexport.php',
                    'rights'  => 'EXPORT_SITEMAP_VIEW',
                    'section' => CONF_SITEMAP,
                ],
                __('RSS feed')       => (object)[
                    'link'    => 'rss.php',
                    'rights'  => 'EXPORT_RSSFEED_VIEW',
                    'section' => CONF_RSS,
                ],
            ],
        ]
    ],
    __('Administration') => (object)[
        'icon'  => 'administration',
        'items' => [
            __('Settings')    => [
                __('Global settings')               => (object)[
                    'link'    => 'einstellungen.php?kSektion=' . CONF_GLOBAL,
                    'rights'  => 'SETTINGS_GLOBAL_VIEW',
                    'section' => CONF_GLOBAL,
                ],
                __('Language administration')       => (object)[
                    'link'   => 'sprache.php',
                    'rights' => 'LANGUAGE_VIEW',
                ],
                __('Back end user')                 => (object)[
                    'link'   => 'benutzerverwaltung.php',
                    'rights' => 'ACCOUNT_VIEW',
                ],
                __('Synchronisation with JTL-Wawi') => (object)[
                    'link'   => 'wawisync.php',
                    'rights' => 'WAWI_SYNC_VIEW',
                ],
                __('Global meta data')              => (object)[
                    'link'    => 'globalemetaangaben.php',
                    'rights'  => 'SETTINGS_GLOBAL_META_VIEW',
                    'section' => CONF_METAANGABEN,
                ],
                __('Special items')                 => (object)[
                    'link'    => 'suchspecials.php',
                    'rights'  => 'SETTINGS_SPECIALPRODUCTS_VIEW',
                    'section' => CONF_SUCHSPECIAL,
                ],
                __('Sitemap structure')             => (object)[
                    'link'   => 'shopsitemap.php',
                    'rights' => 'SETTINGS_SITEMAP_VIEW',
                ],
                __('Number formats')                => (object)[
                    'link'   => 'trennzeichen.php',
                    'rights' => 'SETTINGS_SEPARATOR_VIEW',
                ],
                __('Cache')                         => (object)[
                    'link'   => 'cache.php',
                    'rights' => 'OBJECTCACHE_VIEW',
                ],
                __('Filesystem')                    => (object)[
                    'link'   => 'filesystem.php',
                    'rights' => 'SETTINGS_GLOBAL_VIEW',
                ],
            ],
            __('Maintenance') => [
                __('Order history')     => (object)[
                    'link'   => 'bestellungen.php',
                    'rights' => 'ORDER_VIEW',
                ],
                __('Activation centre') => (object)[
                    'link'   => 'freischalten.php',
                    'rights' => 'UNLOCK_CENTRAL_VIEW',
                ],
                __('System log')        => (object)[
                    'link'   => 'systemlog.php',
                    'rights' => 'SYSTEMLOG_VIEW',
                ],
                __('Status')            => (object)[
                    'link'   => 'status.php',
                    'rights' => 'FILECHECK_VIEW|DBCHECK_VIEW|PERMISSIONCHECK_VIEW',
                ],
                __('Update')            => (object)[
                    'link'   => 'dbupdater.php',
                    'rights' => 'SHOP_UPDATE_VIEW',
                ],
                __('Re-directions')     => (object)[
                    'link'   => 'redirect.php',
                    'rights' => 'REDIRECT_VIEW',
                ],
                __('Reset shop')        => (object)[
                    'link'   => 'shopzuruecksetzen.php',
                    'rights' => 'RESET_SHOP_VIEW',
                ],
                __('Cron')              => (object)[
                    'link'   => 'cron.php',
                    'rights' => 'EXPORT_SCHEDULE_VIEW',
                ],
            ],
            __('Search')      => [
                __('Search settings') => (object)[
                    'link'    => 'sucheinstellungen.php',
                    'rights'  => 'SETTINGS_ARTICLEOVERVIEW_VIEW',
                    'section' => CONF_ARTIKELUEBERSICHT,
                ],
                __('Filter')          => (object)[
                    'link'    => 'navigationsfilter.php',
                    'rights'  => 'SETTINGS_NAVIGATION_FILTER_VIEW',
                    'section' => CONF_NAVIGATIONSFILTER,
                ],
                __('Queries')         => (object)[
                    'link'   => 'livesuche.php',
                    'rights' => 'MODULE_LIVESEARCH_VIEW',
                ],
                __('JTL Search')      => 'DYNAMIC_JTL_SEARCH',
            ],
        ]
    ],
    __('Plugins')        => (object)[
        'icon'  => 'plugins',
        'items' => [
            //        __('Plug-in marketplace') => (object)[
            //            'link' => 'marktplatz.php',
            //            'rights' => 'PLUGIN_ADMIN_VIEW',
            //        ],
            __('Plug-in administration') => (object)[
                'link'   => 'pluginverwaltung.php',
                'rights' => 'PLUGIN_ADMIN_VIEW',
            ],
            __('Plug-in profiler')       => (object)[
                'link'   => 'profiler.php',
                'rights' => 'PROFILER_VIEW',
            ],
            __('My Plugins')             => 'DYNAMIC_PLUGINS',
        ],
    ]
];

$sectionMenuMapping = [];

foreach ($adminMenu as $menuName => $menu) {
    foreach ($menu->items as $subMenuName => $subMenu) {
        if (!is_array($subMenu)) {
            continue;
        }
        foreach ($subMenu as $itemName => $item) {
            if (isset($item->section)) {
                $sectionMenuMapping[$item->section] = (object)[
                    'path' => $menuName . ' -&gt; ' . $subMenuName . ' -&gt; ' . $itemName,
                    'url'  => $item->link,
                ];
            }
        }
    }
}
