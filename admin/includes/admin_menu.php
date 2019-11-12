<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

\JTL\Shop::Container()->getGetText()->loadAdminLocale('menu');

/** @var array $adminMenu */
$adminMenu = [
    __('Marketing')      => (object)[
        'icon'  => 'marketing',
        'items' => [
            __('Order history')        => (object)[
                'link'   => 'bestellungen.php',
                'permissions' => 'ORDER_VIEW',
            ],
            __('Actions') => [
                __('Newsletter')              => (object)[
                    'link'   => 'newsletter.php',
                    'permissions' => 'MODULE_NEWSLETTER_VIEW',
                    'section' => CONF_NEWSLETTER,
                ],
                __('Blog posts')                    => (object)[
                    'link'   => 'news.php',
                    'permissions' => 'CONTENT_NEWS_SYSTEM_VIEW',
                    'section' => CONF_NEWS,
                ],
                __('Surveys')                 => (object)[
                    'link'   => 'umfrage.php',
                    'permissions' => 'EXTENSION_VOTE_VIEW',
                    'section' => CONF_UMFRAGE,
                ],
                __('Coupons')                 => (object)[
                    'link'   => 'kupons.php',
                    'permissions' => 'ORDER_COUPON_VIEW',
                ],
                __('Free gift')              => (object)[
                    'link'   => 'gratisgeschenk.php',
                    'permissions' => 'MODULE_GIFT_VIEW',
                ],
                __('Customers win customers') => (object)[
                    'link'   => 'kundenwerbenkunden.php',
                    'permissions' => 'MODULE_CAC_VIEW',
                    'section' => CONF_KUNDENWERBENKUNDEN,
                ],
            ],
            __('Statistics')       => [
                __('Sales revenues')    => (object)[
                    'link'   => 'statistik.php?s=4',
                    'permissions' => 'STATS_EXCHANGE_VIEW',
                ],
                __('Campaigns') => (object)[
                    'link'   => 'kampagne.php#globalestats',
                    'permissions' => 'STATS_CAMPAIGN_VIEW',
                ],
                __('Baskets')          => (object)[
                    'link'   => 'warenkorbpers.php',
                    'permissions' => 'MODULE_SAVED_BASKETS_VIEW',
                ],
                __('Coupon statistics') => (object)[
                    'link'   => 'kuponstatistik.php',
                    'permissions' => 'STATS_COUPON_VIEW',
                ],
                __('Visitors')          => (object)[
                    'link'   => 'statistik.php?s=1',
                    'permissions' => 'STATS_VISITOR_VIEW',
                ],
                __('Customer origin')   => (object)[
                    'link'   => 'statistik.php?s=2',
                    'permissions' => 'STATS_VISITOR_LOCATION_VIEW',
                ],
                __('Search queries')         => (object)[
                    'link'   => 'livesuche.php',
                    'permissions' => 'MODULE_LIVESEARCH_VIEW',
                ],
                __('Search query activation')    => (object)[
                    'link'   => 'freischalten.php#livesearch',
                    'permissions' => 'UNLOCK_CENTRAL_VIEW',
                ],
                __('Start pages')       => (object)[
                    'link'   => 'statistik.php?s=5',
                    'permissions' => 'STATS_LANDINGPAGES_VIEW',
                ],
                __('Search engines')    => (object)[
                    'link'   => 'statistik.php?s=3',
                    'permissions' => 'STATS_CRAWLER_VIEW',
                ],
            ],
            __('Reports')    => (object)[
                'link'   => 'statusemail.php',
                'permissions' => 'EMAIL_REPORTS_VIEW',
            ],
        ]
    ],
    __('Styling')      => (object)[
        'icon'  => 'styling',
        'items' => [
            __('OnPage Composer')         => (object)[
                'link'   => 'opc-controlcenter.php',
                'permissions' => 'CONTENT_PAGE_VIEW',
            ],
            __('Default views')         => [
                __('Front page')              => (object)[
                    'link'   => 'einstellungen.php?kSektion=' . CONF_STARTSEITE,
                    'permissions' => 'SETTINGS_STARTPAGE_VIEW',
                    'section' => CONF_STARTSEITE,
                ],
                __('Item overview')          => (object)[
                    'link'   => 'navigationsfilter.php',
                    'permissions' => 'SETTINGS_NAVIGATION_FILTER_VIEW',
                    'section' => CONF_NAVIGATIONSFILTER,
                ],
                __('Item details')     => (object)[
                    'link'   => 'einstellungen.php?kSektion=' . CONF_ARTIKELDETAILS,
                    'permissions' => 'SETTINGS_ARTICLEDETAILS_VIEW',
                    'section' => CONF_ARTIKELDETAILS,
                ],
                __('Question on item')     => (object)[
                    'link'   => 'einstellungen.php?kSektion=' . CONF_ARTIKELDETAILS .
                        '&group=configgroup_5_product_question#configgroup_5_product_question',
                    'permissions' => 'SETTINGS_ARTICLEDETAILS_VIEW',
                    'section' => CONF_ARTIKELDETAILS,
                ],
                __('Comparison list')         => (object)[
                    'link'   => 'vergleichsliste.php',
                    'permissions' => 'MODULE_COMPARELIST_VIEW',
                    'section' => CONF_VERGLEICHSLISTE,
                ],
                __('Wish list')               => (object)[
                    'link'   => 'wunschliste.php',
                    'permissions' => 'MODULE_WISHLIST_VIEW',
                ],
                __('Contact form')             => (object)[
                    'link'   => 'kontaktformular.php',
                    'permissions' => 'SETTINGS_CONTACTFORM_VIEW',
                    'section' => CONF_KONTAKTFORMULAR,
                ],
                __('Registration')            => (object)[
                    'link'   => 'einstellungen.php?kSektion=' . CONF_KUNDEN,
                    'permissions' => 'SETTINGS_CUSTOMERFORM_VIEW',
                    'section' => CONF_KUNDEN,
                ],
            ],
            __('Default elements')         => [
                __('Shop logo')     => (object)[
                    'link'   => 'shoplogouploader.php',
                    'permissions' => 'ORDER_AGB_WRB_VIEW',
                ],
                __('Search settings') => (object)[
                    'link'   => 'sucheinstellungen.php',
                    'permissions' => 'SETTINGS_ARTICLEOVERVIEW_VIEW',
                    'section' => CONF_ARTIKELUEBERSICHT,
                ],
                __('Price history')    => (object)[
                    'link'   => 'preisverlauf.php',
                    'permissions' => 'MODULE_PRICECHART_VIEW',
                    'section' => CONF_PREISVERLAUF,
                ],
                __('Item sticker') => (object)[
                    'link'   => 'suchspecialoverlay.php',
                    'permissions' => 'ORDER_AGB_WRB_VIEW',
                ],
                __('Footer / Boxes')                   => (object)[
                    'link'   => 'boxen.php',
                    'permissions' => 'BOXES_VIEW',
                ],
                __('Selection wizard') => (object)[
                    'link'   => 'auswahlassistent.php',
                    'permissions' => 'EXTENSION_SELECTIONWIZARD_VIEW',
                    'section' => CONF_AUSWAHLASSISTENT,
                ],
                __('Recommendation settings')            => (object)[
                    'link'   => 'einstellungen.php?kSektion=' . CONF_BOXEN,
                    'permissions' => 'SETTINGS_BOXES_VIEW',
                    'section' => CONF_BOXEN,
                ],
                __('Warehouse settings')        => (object)[
                    'link'   => 'warenlager.php',
                    'permissions' => 'WAREHOUSE_VIEW',
                ],
                __('Checkout') => (object)[
                    'link'   => 'einstellungen.php?kSektion=' . CONF_KAUFABWICKLUNG,
                    'permissions' => 'SETTINGS_BASKET_VIEW',
                    'section' => CONF_KAUFABWICKLUNG,
                ],
                __('Item reviews') => (object)[
                    'link'   => 'bewertung.php',
                    'permissions' => 'MODULE_VOTESYSTEM_VIEW',
                    'section' => CONF_BEWERTUNG,
                ],
                __('Number formats')                => (object)[
                    'link'   => 'trennzeichen.php',
                    'permissions' => 'SETTINGS_SEPARATOR_VIEW',
                ],
            ],
            __('Custom content')         => [
                __('GTC/cancellation policy') => (object)[
                    'link'   => 'agbwrb.php',
                    'permissions' => 'ORDER_AGB_WRB_VIEW',
                ],
                __('Own pages')               => (object)[
                    'link'   => 'links.php',
                    'permissions' => 'CONTENT_PAGE_VIEW',
                ],
                __('Customer fields')       => (object)[
                    'link'   => 'kundenfeld.php',
                    'permissions' => 'ORDER_CUSTOMERFIELDS_VIEW',
                    'section' => CONF_KUNDENFELD,
                ],
                __('Checkboxes') => (object)[
                    'link'   => 'checkbox.php',
                    'permissions' => 'CHECKBOXES_VIEW',
                ],
                __('Banner')    => (object)[
                    'link'   => 'banner.php',
                    'permissions' => 'DISPLAY_BANNER_VIEW',
                ],
                __('Slider')    => (object)[
                    'link'   => 'slider.php',
                    'permissions' => 'SLIDER_VIEW',
                ],
            ],
            __('Settings')    => [
                __('System')               => (object)[
                    'link'   => 'einstellungen.php?kSektion=' . CONF_GLOBAL,
                    'permissions' => 'SETTINGS_GLOBAL_VIEW',
                    'section' => CONF_GLOBAL,
                ],
                __('Template settings') => (object)[
                    'link'   => 'shoptemplate.php',
                    'permissions' => 'DISPLAY_TEMPLATE_VIEW',
                ],
                __('Images')      => (object)[
                    'link'   => 'bilder.php',
                    'permissions' => 'SETTINGS_IMAGES_VIEW',
                    'section' => CONF_BILDER,
                ],
                __('Watermark')     => (object)[
                    'link'   => 'branding.php',
                    'permissions' => 'DISPLAY_BRANDING_VIEW',
                ],
            ]
        ]
    ],
    __('Extensions')        => (object)[
        'icon'  => 'plugins',
        'items' => [
            __('Plug-in administration') => (object)[
                'link'   => 'pluginverwaltung.php',
                'permissions' => 'PLUGIN_ADMIN_VIEW',
            ],
            //        __('Plug-in marketplace') => (object)[
            //            'link' => 'marktplatz.php',
            //            'permissions' => 'PLUGIN_ADMIN_VIEW',
            //        ],
            //        __('My Purchases') => (object)[
            //            'link' => 'dummy.php',
            //            'permissions' => 'PLUGIN_ADMIN_VIEW',
            //        ],
            __('My Plugins')  => 'DYNAMIC_PLUGINS',
        ],
    ],
    __('Administration') => (object)[
        'icon'  => 'administration',
        'items' => [
            __('Activation centre')    => (object)[
                'link'   => 'freischalten.php',
                'permissions' => 'UNLOCK_CENTRAL_VIEW',
            ],
            __('Import') => [
                __('Newsletter recipients') => (object)[
                    'link'   => 'newsletterimport.php',
                    'permissions' => 'IMPORT_NEWSLETTER_RECEIVER_VIEW',
                ],
                __('Customer data import')        => (object)[
                    'link'   => 'kundenimport.php',
                    'permissions' => 'IMPORT_CUSTOMER_VIEW',
                ],
                __('Zip code import')             => (object)[
                    'link'   => 'plz_ort_import.php',
                    'permissions' => 'PLZ_ORT_IMPORT_VIEW',
                ],
            ],
            __('Export') => [
                __('Sitemap export') => (object)[
                    'link'   => 'sitemapexport.php',
                    'permissions' => 'EXPORT_SITEMAP_VIEW',
                    'section' => CONF_SITEMAP,
                ],
                __('RSS feed')       => (object)[
                    'link'   => 'rss.php',
                    'permissions' => 'EXPORT_RSSFEED_VIEW',
                    'section' => CONF_RSS,
                ],
                __('Custom formats') => (object)[
                    'link'   => 'exportformate.php',
                    'permissions' => 'EXPORT_FORMATS_VIEW',
                ],
                __('Export manager') => (object)[
                    'link'   => 'exportformat_queue.php',
                    'permissions' => 'EXPORT_SCHEDULE_VIEW',
                ],
            ],
            __('Payments') => [
                __('Method of payment') => (object)[
                    'link'   => 'zahlungsarten.php',
                    'permissions' => 'ORDER_PAYMENT_VIEW',
                ],
//                __('More payment methods') => (object)[
//                    'link'   => 'zahlungsarten.php',
//                    'permissions' => 'ORDER_PAYMENT_VIEW',
//                ],
            ],
            __('Delivery')      => [
                __('Shipping methods')     => (object)[
                    'link'   => 'versandarten.php',
                    'permissions' => 'ORDER_SHIPMENT_VIEW',
                ],
                __('Additional packaging') => (object)[
                    'link'   => 'zusatzverpackung.php',
                    'permissions' => 'ORDER_PACKAGE_VIEW',
                ],
            ],
            __('E-Mails') => [
                __('E-Mail server')   => (object)[
                    'link'   => 'einstellungen.php?kSektion=' . CONF_EMAILS,
                    'permissions' => 'SETTINGS_EMAILS_VIEW',
                    'section' => CONF_EMAILS,
                ],
                __('Templates')  => (object)[
                    'link'   => 'emailvorlagen.php',
                    'permissions' => 'CONTENT_EMAIL_TEMPLATE_VIEW',
                ],
                __('Blacklist')  => (object)[
                    'link'   => 'emailblacklist.php',
                    'permissions' => 'SETTINGS_EMAIL_BLACKLIST_VIEW',
                    'section' => CONF_EMAILBLACKLIST,
                ],
                __('E-mail log') => (object)[
                    'link'   => 'emailhistory.php',
                    'permissions' => 'EMAILHISTORY_VIEW',
                ],
            ],
            __('SEO') => [
                __('Global meta data')              => (object)[
                    'link'   => 'globalemetaangaben.php',
                    'permissions' => 'SETTINGS_GLOBAL_META_VIEW',
                    'section' => CONF_METAANGABEN,
                ],
                __('Re-directions')        => (object)[
                    'link'   => 'redirect.php',
                    'permissions' => 'REDIRECT_VIEW',
                ],
                __('Sitemap structure')             => (object)[
                    'link'   => 'shopsitemap.php',
                    'permissions' => 'SETTINGS_SITEMAP_VIEW',
                ],
                __('Special items')                 => (object)[
                    'link'   => 'suchspecials.php',
                    'permissions' => 'SETTINGS_SPECIALPRODUCTS_VIEW',
                    'section' => CONF_SUCHSPECIAL,
                ],
            ],
            __('Language administration') => (object)[
                'link'   => 'sprache.php',
                'permissions' => 'LANGUAGE_VIEW',
            ],
            __('Access') => [
                __('Back end user')                 => (object)[
                    'link'   => 'benutzerverwaltung.php',
                    'permissions' => 'ACCOUNT_VIEW',
                ],
                __('Synchronisation with JTL-Wawi') => (object)[
                    'link'   => 'wawisync.php',
                    'permissions' => 'WAWI_SYNC_VIEW',
                ],
            ],
            __('Troubleshooting') => [
                __('Status')               => (object)[
                    'link'   => 'status.php',
                    'permissions' => 'FILECHECK_VIEW|DBCHECK_VIEW|PERMISSIONCHECK_VIEW',
                ],
                __('System log')           => (object)[
                    'link'   => 'systemlog.php',
                    'permissions' => 'SYSTEMLOG_VIEW',
                ],
                __('Image administration') => (object)[
                    'link'   => 'bilderverwaltung.php',
                    'permissions' => 'DISPLAY_IMAGES_VIEW',
                ],
                __('Plug-in profiler')       => (object)[
                    'link'   => 'profiler.php',
                    'permissions' => 'PROFILER_VIEW',
                ],
            ],
            __('System') => [
                __('Cache')                  => (object)[
                    'link'   => 'cache.php',
                    'permissions' => 'OBJECTCACHE_VIEW',
                ],
                __('Cron')           => (object)[
                    'link'   => 'cron.php',
                    'permissions' => 'EXPORT_SCHEDULE_VIEW',
                ],
                __('Update')               => (object)[
                    'link'   => 'dbupdater.php',
                    'permissions' => 'SHOP_UPDATE_VIEW',
                ],
                __('Reset shop')           => (object)[
                    'link'   => 'shopzuruecksetzen.php',
                    'permissions' => 'RESET_SHOP_VIEW',
                ],
            ],
        ]
    ],
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
