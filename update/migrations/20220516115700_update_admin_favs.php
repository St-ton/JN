<?php declare(strict_types=1);

use JTL\Router\BackendRouter;
use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20220516115700
 */
class Migration_20220516115700 extends Migration implements IMigration
{
    protected $author      = 'fm';
    protected $description = 'Update admin favs';

    private static array $mapping = [
        'agbwrb.php'             => BackendRouter::ROUTE_TAC,
        'auswahlassistent.php'   => BackendRouter::ROUTE_SELECTION_WIZARD,
        'banner.php'             => BackendRouter::ROUTE_BANNER,
        'benutzerverwaltung.php' => BackendRouter::ROUTE_USERS,
        'bestellungen.php'       => BackendRouter::ROUTE_ORDERS,
        'bewertung.php'          => BackendRouter::ROUTE_REVIEWS,
        'bilder.php'             => BackendRouter::ROUTE_IMAGES,
        'bilderverwaltung.php'   => BackendRouter::ROUTE_IMAGE_MANAGEMENT,
        'boxen.php'              => BackendRouter::ROUTE_BOXES,
        'branding.php'           => BackendRouter::ROUTE_BRANDING,
        'cache.php'              => BackendRouter::ROUTE_CACHE,
        'categorycheck.php'      => BackendRouter::ROUTE_CATEGORYCHECK,
        'checkbox.php'           => BackendRouter::ROUTE_CHECKBOX,
        'consent.php'            => BackendRouter::ROUTE_CONSENT,
        'countrymananger.php'    => BackendRouter::ROUTE_COUNTRIES,
        'cron.php'               => BackendRouter::ROUTE_CRON,
        'dbcheck.php'            => BackendRouter::ROUTE_DBCHECK,
        'dbmanager.php'          => BackendRouter::ROUTE_DBMANAGER,
        'dbupdater.php'          => BackendRouter::ROUTE_DBUPDATER,
        'einstellungen.php'      => BackendRouter::ROUTE_CONFIG,
        'emailblacklist.php'     => BackendRouter::ROUTE_EMAILBLOCKLIST,
        'emailhistory.php'       => BackendRouter::ROUTE_EMAILHISTORY,
        'emailvorlagen.php'      => BackendRouter::ROUTE_EMAILTEMPLATES,
        'exportformate.php'      => BackendRouter::ROUTE_EXPORT,
        'favs.php'               => BackendRouter::ROUTE_FAVS,
        'filecheck.php'          => BackendRouter::ROUTE_FILECHECK,
        'filesystem.php'         => BackendRouter::ROUTE_FILESYSTEM,
        'freischalten.php'       => BackendRouter::ROUTE_ACTIVATE,
        'globalemetaangaben.php' => BackendRouter::ROUTE_META,
        'gratisgeschenk.php'     => BackendRouter::ROUTE_GIFTS,
        'kampagne.php'           => BackendRouter::ROUTE_CAMPAIGN,
        'kontaktformular.php'    => BackendRouter::ROUTE_CONTACT_FORMS,
        'kundenfeld.php'         => BackendRouter::ROUTE_CUSTOMERFIELDS,
        'kundenimport.php'       => BackendRouter::ROUTE_CUSTOMER_IMPORT,
        'kupons.php'             => BackendRouter::ROUTE_COUPONS,
        'kuponstatistik.php'     => BackendRouter::ROUTE_COUPON_STATS,
        'licenses.php'           => BackendRouter::ROUTE_LICENSE,
        'links.php'              => BackendRouter::ROUTE_LINKS,
        'livesuche.php'          => BackendRouter::ROUTE_LIVESEARCH,
        'navigationsfilder.php'  => BackendRouter::ROUTE_NAVFILTER,
        'news.php'               => BackendRouter::ROUTE_NEWS,
        'newsletter.php'         => BackendRouter::ROUTE_NEWSLETTER,
        'opc.php'                => BackendRouter::ROUTE_OPC,
        'permissioncheck.php'    => BackendRouter::ROUTE_PERMISSIONCHECK,
        'pluginverwaltung.php'   => BackendRouter::ROUTE_PLUGIN_MANAGER,
        'plz_ort_import.php'     => BackendRouter::ROUTE_ZIP_IMPORT,
        'preisverlauf.php'       => BackendRouter::ROUTE_PRICEHISTORY,
        'profiler.php'           => BackendRouter::ROUTE_PROFILER,
        'redirect.php'           => BackendRouter::ROUTE_REDIRECT,
        'rss.php'                => BackendRouter::ROUTE_RSS,
        'shopsitemap.php'        => BackendRouter::ROUTE_SITEMAP,
        'shoptemplate.php'       => BackendRouter::ROUTE_TEMPLATE,
        'shopzuruecksetzen.php'  => BackendRouter::ROUTE_RESET,
        'sitemap.php'            => BackendRouter::ROUTE_SITEMAP,
        'slider.php'             => BackendRouter::ROUTE_SLIDERS,
        'sprache.php'            => BackendRouter::ROUTE_LANGUAGE,
        'statistik.php'          => BackendRouter::ROUTE_STATS,
        'status.php'             => BackendRouter::ROUTE_STATUS,
        'statusemail.php'        => BackendRouter::ROUTE_STATUSMAIL,
        'sucheinstellungen.php'  => BackendRouter::ROUTE_SEARCHCONFIG,
        'suchspecialoverlay.php' => BackendRouter::ROUTE_SEARCHSPECIALOVERLAYS,
        'suchspecials.php'       => BackendRouter::ROUTE_SEARCHSPECIAL,
        'systemcheck.php'        => BackendRouter::ROUTE_SYSTEMCHECK,
        'systemlog.php'          => BackendRouter::ROUTE_SYSTEMLOG,
        'trennzeichen.php'       => BackendRouter::ROUTE_SEPARATOR,
        'vergleichsliste.php'    => BackendRouter::ROUTE_COMPARELIST,
        'versandarten.php'       => BackendRouter::ROUTE_SHIPPING_METHODS,
        'warenkorbpers.php'      => BackendRouter::ROUTE_PERSISTENT_CART,
        'warenlager.php'         => BackendRouter::ROUTE_WAREHOUSES,
        'wawisync.php'           => BackendRouter::ROUTE_SYNC,
        'wizard.php'             => BackendRouter::ROUTE_WIZARD,
        'wunschliste.php'        => BackendRouter::ROUTE_WISHLIST,
        'zahlungsarten.php'      => BackendRouter::ROUTE_PAYMENT_METHODS,
        'zusatzverpackung.php'   => BackendRouter::ROUTE_PACKAGINGS,
    ];

    /**
     * @inheritdoc
     */
    public function up()
    {
        foreach ($this->getDB()->getObjects('SELECT * FROM tadminfavs') as $fav) {
            foreach (self::$mapping as $old => $new) {
                $fav->cUrl = str_replace($old, $new, $fav->cUrl);
            }
            $this->getDB()->update('tadminfavs', 'kAdminfav', (int)$fav->kAdminfav, $fav);
        }
    }

    /**
     * @return mixed|void
     * @throws Exception
     */
    public function down()
    {
    }
}
