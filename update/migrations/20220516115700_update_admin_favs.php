<?php declare(strict_types=1);

use JTL\Router\Route;
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
        'agbwrb.php'             => Route::TAC,
        'auswahlassistent.php'   => Route::SELECTION_WIZARD,
        'banner.php'             => Route::BANNER,
        'benutzerverwaltung.php' => Route::USERS,
        'bestellungen.php'       => Route::ORDERS,
        'bewertung.php'          => Route::REVIEWS,
        'bilder.php'             => Route::IMAGES,
        'bilderverwaltung.php'   => Route::IMAGE_MANAGEMENT,
        'boxen.php'              => Route::BOXES,
        'branding.php'           => Route::BRANDING,
        'cache.php'              => Route::CACHE,
        'categorycheck.php'      => Route::CATEGORYCHECK,
        'checkbox.php'           => Route::CHECKBOX,
        'consent.php'            => Route::CONSENT,
        'countrymananger.php'    => Route::COUNTRIES,
        'cron.php'               => Route::CRON,
        'dbcheck.php'            => Route::DBCHECK,
        'dbmanager.php'          => Route::DBMANAGER,
        'dbupdater.php'          => Route::DBUPDATER,
        'einstellungen.php'      => Route::CONFIG,
        'emailblacklist.php'     => Route::EMAILBLOCKLIST,
        'emailhistory.php'       => Route::EMAILHISTORY,
        'emailvorlagen.php'      => Route::EMAILTEMPLATES,
        'exportformate.php'      => Route::EXPORT,
        'favs.php'               => Route::FAVS,
        'filecheck.php'          => Route::FILECHECK,
        'filesystem.php'         => Route::FILESYSTEM,
        'freischalten.php'       => Route::ACTIVATE,
        'globalemetaangaben.php' => Route::META,
        'gratisgeschenk.php'     => Route::GIFTS,
        'kampagne.php'           => Route::CAMPAIGN,
        'kontaktformular.php'    => Route::CONTACT_FORMS,
        'kundenfeld.php'         => Route::CUSTOMERFIELDS,
        'kundenimport.php'       => Route::CUSTOMER_IMPORT,
        'kupons.php'             => Route::COUPONS,
        'kuponstatistik.php'     => Route::COUPON_STATS,
        'licenses.php'           => Route::LICENSE,
        'links.php'              => Route::LINKS,
        'livesuche.php'          => Route::LIVESEARCH,
        'navigationsfilder.php'  => Route::NAVFILTER,
        'news.php'               => Route::NEWS,
        'newsletter.php'         => Route::NEWSLETTER,
        'opc.php'                => Route::OPC,
        'permissioncheck.php'    => Route::PERMISSIONCHECK,
        'pluginverwaltung.php'   => Route::PLUGIN_MANAGER,
        'plz_ort_import.php'     => Route::ZIP_IMPORT,
        'preisverlauf.php'       => Route::PRICEHISTORY,
        'profiler.php'           => Route::PROFILER,
        'redirect.php'           => Route::REDIRECT,
        'rss.php'                => Route::RSS,
        'shopsitemap.php'        => Route::SITEMAP,
        'shoptemplate.php'       => Route::TEMPLATE,
        'shopzuruecksetzen.php'  => Route::RESET,
        'sitemap.php'            => Route::SITEMAP,
        'slider.php'             => Route::SLIDERS,
        'sprache.php'            => Route::LANGUAGE,
        'statistik.php'          => Route::STATS,
        'status.php'             => Route::STATUS,
        'statusemail.php'        => Route::STATUSMAIL,
        'sucheinstellungen.php'  => Route::SEARCHCONFIG,
        'suchspecialoverlay.php' => Route::SEARCHSPECIALOVERLAYS,
        'suchspecials.php'       => Route::SEARCHSPECIAL,
        'systemcheck.php'        => Route::SYSTEMCHECK,
        'systemlog.php'          => Route::SYSTEMLOG,
        'trennzeichen.php'       => Route::SEPARATOR,
        'vergleichsliste.php'    => Route::COMPARELIST,
        'versandarten.php'       => Route::SHIPPING_METHODS,
        'warenkorbpers.php'      => Route::PERSISTENT_CART,
        'warenlager.php'         => Route::WAREHOUSES,
        'wawisync.php'           => Route::SYNC,
        'wizard.php'             => Route::WIZARD,
        'wunschliste.php'        => Route::WISHLIST,
        'zahlungsarten.php'      => Route::PAYMENT_METHODS,
        'zusatzverpackung.php'   => Route::PACKAGINGS,
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
