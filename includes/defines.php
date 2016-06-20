<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

//charset
define('JTL_CHARSET', 'iso-8859-1');
ini_set('default_charset', JTL_CHARSET);
date_default_timezone_set('Europe/Berlin');
//log levels
ifndef('SYNC_LOG_LEVEL', 0);
ifndef('ADMIN_LOG_LEVEL', 0);
ifndef('SHOP_LOG_LEVEL', 0);
ifndef('SMARTY_LOG_LEVEL', 0);
error_reporting(SHOP_LOG_LEVEL);
//if this is set to false, Hersteller, Linkgruppen and oKategorie_arr will not be added to $_SESSION
//this requires changes in templates!
ifndef('TEMPLATE_COMPATIBILITY', true);
// Image compatibility level 0 => disabled, 1 => referenced in history table, 2 => automatic detection
ifndef('IMAGE_COMPATIBILITY_LEVEL', 1);
ifndef('KEEP_SYNC_FILES', false);
ifndef('PROFILE_PLUGINS', false);
ifndef('PROFILE_SHOP', false);
ifndef('PROFILE_QUERIES', false);
ifndef('PROFILE_QUERIES_ECHO', false);
ifndef('SHOW_PAGE_CACHE', false);
//PHP memory_limit work around
if (intval(str_replace('M', '', ini_get('memory_limit'))) < 64) {
    ini_set('memory_limit', '64M');
}
ini_set('session.use_trans_sid', 0);
require_once dirname(__FILE__) . '/defines_inc.php';
//Logging (in logs/) 0 => aus, 1 => nur errors, 2 => errors, notifications, 3 => errors, notifications, debug
ifndef('ES_LOGGING', 1);
ifndef('ES_DB_LOGGING', 0);
// PHP Error Handler
define('PHP_ERROR_HANDLER', false);
define('DEBUG_FRAME', false);
define('SMARTY_DEBUG_CONSOLE', false);
define('SMARTY_SHOW_LANGKEY', false);
ifndef('SMARTY_FORCE_COMPILE', false);
ifndef('JTL_INCLUDE_ONLY_DB', 0);
ifndef('SOCKET_TIMEOUT', 30);
ifndef('ARTICLES_PER_PAGE_HARD_LIMIT', 100);
//Pfade
define('PFAD_CLASSES', 'classes/');
define('PFAD_CONFIG', 'config/');
define('PFAD_TEMPLATES', 'templates/');
define('PFAD_COMPILEDIR', 'templates_c/');
define('PFAD_INCLUDES', 'includes/');
define('PFAD_EMAILPDFS', 'emailpdfs/');
define('PFAD_NEWSLETTERBILDER', 'newsletter/');
define('PFAD_LINKBILDER', 'links/');
define('PFAD_INCLUDES_LIBS', PFAD_INCLUDES . 'libs/');
define('PFAD_MINIFY', PFAD_INCLUDES . 'libs/minify');
define('PFAD_CKEDITOR', PFAD_INCLUDES_LIBS . 'ckeditor/');
define('PFAD_CODEMIRROR', PFAD_INCLUDES_LIBS . 'codemirror-5.8.0/');
define('PFAD_INCLUDES_TOOLS', PFAD_INCLUDES . 'tools/');
define('PFAD_INCLUDES_EXT', PFAD_INCLUDES . 'ext/');
define('PFAD_INCLUDES_MODULES', PFAD_INCLUDES . 'modules/');
ifndef('PFAD_SMARTY', PFAD_INCLUDES_LIBS . 'smarty-3.1.27/libs/');
define('SMARTY_DIR', PFAD_ROOT . PFAD_SMARTY);
define('PFAD_XAJAX', PFAD_INCLUDES_LIBS . 'xajax_0.5_standard/');
define('PFAD_FLASHCHART', PFAD_INCLUDES_LIBS . 'flashchart/');
define('PFAD_FLASHCLOUD', PFAD_INCLUDES_LIBS . 'flashcloud/');
define('PFAD_PHPQUERY', PFAD_INCLUDES_LIBS . 'phpQuery/');
define('PFAD_PCLZIP', PFAD_INCLUDES_LIBS . 'pclzip-2-8-2/');
define('PFAD_PHPMAILER', PFAD_INCLUDES_LIBS . 'PHPMailer-5.2.14/');
define('PFAD_GRAPHCLASS', PFAD_INCLUDES_LIBS . 'graph-2005-08-28/');
define('PFAD_AJAXCHECKOUT', PFAD_INCLUDES_LIBS . 'ajaxcheckout/');
define('PFAD_AJAXSUGGEST', PFAD_INCLUDES_LIBS . 'ajaxsuggest/');
define('PFAD_ART_ABNAHMEINTERVALL', PFAD_INCLUDES_LIBS . 'artikel_abnahmeintervall/');
define('PFAD_BLOWFISH', PFAD_INCLUDES_LIBS . 'xtea/');
define('PFAD_FLASHPLAYER', PFAD_INCLUDES_LIBS . 'flashplayer/');
define('PFAD_IMAGESLIDER', PFAD_INCLUDES_LIBS . 'slideitmoo_image_slider/');
define('PFAD_CLASSES_CORE', PFAD_CLASSES . 'core/');
define('PFAD_OBJECT_CACHING', 'caching/');
define('PFAD_GFX', 'gfx/');
define('PFAD_GFX_AMPEL', PFAD_GFX . 'ampel/');
define('PFAD_GFX_BEWERTUNG_STERNE', PFAD_GFX . 'bewertung_sterne/');
define('PFAD_DBES', 'dbeS/');
define('PFAD_DBES_TMP', PFAD_DBES . 'tmp/');
define('PFAD_BILDER', 'bilder/');
define('PFAD_BILDER_SLIDER', 'bilder/slider/');
define('PFAD_CRON', 'cron/');
define('PFAD_FONTS', PFAD_INCLUDES . 'fonts/');
define('PFAD_BILDER_INTERN', PFAD_BILDER . 'intern/');
define('PFAD_BILDER_BANNER', PFAD_BILDER . 'banner/');
define('PFAD_NEWSBILDER', PFAD_BILDER . 'news/');
define('PFAD_SHOPLOGO', PFAD_BILDER_INTERN . 'shoplogo/');
ifndef('PFAD_ADMIN', 'admin/');
define('PFAD_EMAILVORLAGEN', PFAD_ADMIN . 'mailtemplates/');
define('PFAD_MEDIAFILES', 'mediafiles/');
define('PFAD_GFX_TRUSTEDSHOPS', PFAD_BILDER_INTERN . 'trustedshops/');
define('PFAD_PRODUKTBILDER', PFAD_BILDER . 'produkte/');
define('PFAD_PRODUKTBILDER_MINI', PFAD_PRODUKTBILDER . 'mini/');
define('PFAD_PRODUKTBILDER_KLEIN', PFAD_PRODUKTBILDER . 'klein/');
define('PFAD_PRODUKTBILDER_NORMAL', PFAD_PRODUKTBILDER . 'normal/');
define('PFAD_PRODUKTBILDER_GROSS', PFAD_PRODUKTBILDER . 'gross/');
define('PFAD_KATEGORIEBILDER', PFAD_BILDER . 'kategorien/');
define('PFAD_VARIATIONSBILDER', PFAD_BILDER . 'variationen/');
define('PFAD_VARIATIONSBILDER_MINI', PFAD_VARIATIONSBILDER . 'mini/');
define('PFAD_VARIATIONSBILDER_NORMAL', PFAD_VARIATIONSBILDER . 'normal/');
define('PFAD_VARIATIONSBILDER_GROSS', PFAD_VARIATIONSBILDER . 'gross/');
define('PFAD_HERSTELLERBILDER', PFAD_BILDER . 'hersteller/');
define('PFAD_HERSTELLERBILDER_NORMAL', PFAD_HERSTELLERBILDER . 'normal/');
define('PFAD_HERSTELLERBILDER_KLEIN', PFAD_HERSTELLERBILDER . 'klein/');
define('PFAD_MERKMALBILDER', PFAD_BILDER . 'merkmale/');
define('PFAD_MERKMALBILDER_NORMAL', PFAD_MERKMALBILDER . 'normal/');
define('PFAD_MERKMALBILDER_KLEIN', PFAD_MERKMALBILDER . 'klein/');
define('PFAD_MERKMALWERTBILDER', PFAD_BILDER . 'merkmalwerte/');
define('PFAD_MERKMALWERTBILDER_NORMAL', PFAD_MERKMALWERTBILDER . 'normal/');
define('PFAD_MERKMALWERTBILDER_KLEIN', PFAD_MERKMALWERTBILDER . 'klein/');
define('PFAD_BRANDINGBILDER', PFAD_BILDER . 'brandingbilder/');
define('PFAD_SUCHSPECIALOVERLAY', PFAD_BILDER . 'suchspecialoverlay/');
define('PFAD_SUCHSPECIALOVERLAY_KLEIN', PFAD_BILDER . 'suchspecialoverlay/klein/');
define('PFAD_SUCHSPECIALOVERLAY_NORMAL', PFAD_BILDER . 'suchspecialoverlay/normal/');
define('PFAD_SUCHSPECIALOVERLAY_GROSS', PFAD_BILDER . 'suchspecialoverlay/gross/');
define('PFAD_KONFIGURATOR_KLEIN', PFAD_BILDER . 'konfigurator/klein/');
define('PFAD_LOGFILES', PFAD_ROOT . 'jtllogs/');
define('PFAD_EXPORT', 'export/');
define('PFAD_EXPORT_BACKUP', PFAD_EXPORT . 'backup/');
define('PFAD_EXPORT_YATEGO', PFAD_EXPORT . 'yatego/');
define('PFAD_UPDATE', 'update/');
define('PFAD_WIDGETS', 'widgets/');
define('PFAD_INSTALL', 'install/');
define('PFAD_SHOPMD5', 'shopmd5files/');
define('PFAD_NUSOAP', 'nusoap/');
define('PFAD_UPLOADS', PFAD_ROOT . 'uploads/');
define('PFAD_DOWNLOADS_REL', 'downloads/');
define('PFAD_DOWNLOADS_PREVIEW_REL', 'downloads/vorschau/');
define('PFAD_DOWNLOADS', PFAD_ROOT . PFAD_DOWNLOADS_REL);
define('PFAD_DOWNLOADS_PREVIEW', PFAD_ROOT . PFAD_DOWNLOADS_PREVIEW_REL);
define('PFAD_UPLOADIFY', PFAD_INCLUDES_LIBS . 'uploadify/');
define('PFAD_UPLOAD_CALLBACK', PFAD_INCLUDES_EXT . 'uploads_cb.php');
define('PFAD_IMAGEMAP', PFAD_BILDER . 'banner/');
define('PFAD_KCFINDER', PFAD_INCLUDES_LIBS . 'kcfinder-2.5.4/');
define('PFAD_EMAILTEMPLATES', 'templates_mail/');
define('PFAD_MEDIA_IMAGE', 'media/image/');
define('PFAD_MEDIA_IMAGE_STORAGE', PFAD_MEDIA_IMAGE . 'storage/');
// Plugins
define('PFAD_PLUGIN', PFAD_INCLUDES . 'plugins/');
define('PFAD_PLUGIN_VERSION', 'version/');
define('PFAD_PLUGIN_SQL', 'sql/');
define('PFAD_PLUGIN_FRONTEND', 'frontend/');
define('PFAD_PLUGIN_ADMINMENU', 'adminmenu/');
define('PFAD_PLUGIN_LICENCE', 'licence/');
define('PFAD_PLUGIN_PAYMENTMETHOD', 'paymentmethod/');
define('PFAD_PLUGIN_TEMPLATE', 'template/');
define('PFAD_PLUGIN_BOXEN', 'boxen/');
define('PFAD_PLUGIN_WIDGET', 'widget/');
define('PFAD_PLUGIN_EXPORTFORMAT', 'exportformat/');
define('PFAD_PLUGIN_UNINSTALL', 'uninstall/');
define('PLUGIN_INFO_FILE', 'info.xml');
define('PLUGIN_LICENCE_METHODE', 'checkLicence');
define('PLUGIN_LICENCE_CLASS', 'PluginLicence');
define('PLUGIN_EXPORTFORMAT_CONTENTFILE', 'PluginContentFile_');
define('PFAD_SYNC_TMP', 'tmp/'); //rel zu dbeS
define('PFAD_SYNC_LOGS', PFAD_ROOT . 'dbeS/logs/');
//Dateien
define('FILE_RSS_FEED', 'rss.xml');
define('FILE_PHPFEHLER', PFAD_LOGFILES . 'phperror.log');
//StandardBilder
ifndef('BILD_KEIN_KATEGORIEBILD_VORHANDEN', PFAD_GFX . 'keinBild.gif');
ifndef('BILD_KEIN_ARTIKELBILD_VORHANDEN', PFAD_GFX . 'keinBild.gif');
ifndef('BILD_KEIN_HERSTELLERBILD_VORHANDEN', PFAD_GFX . 'keinBild.gif');
ifndef('BILD_KEIN_MERKMALBILD_VORHANDEN', PFAD_GFX . 'keinBild.gif');
ifndef('BILD_KEIN_MERKMALWERTBILD_VORHANDEN', PFAD_GFX . 'keinBild_kl.gif');
ifndef('BILD_UPLOAD_ZUGRIFF_VERWEIGERT', PFAD_GFX . 'keinBild.gif');
//Suchcache Lebensdauer in Minuten nach letzter Artikeländerung durch JTL-Wawi
define('SUCHCACHE_LEBENSDAUER', 60);
//Sessionspeicherung 1 => DB, sonst => Dateien
define('ES_SESSIONS', 0);
// JTL Support Email
define('JTLSUPPORT_EMAIL', 'support@jtl-software.de');
// JTL URLS
define('JTLURL_BASE', 'https://ext.jtl-software.de/');
define('JTLURL_HP', 'https://www.jtl-software.de/');
define('JTLURL_GET_DUK', JTLURL_BASE . 'json_duk.php');
define('JTLURL_GET_SHOPNEWS', JTLURL_HP . 'news_json.php?notimeline=1&limit=5');
define('JTLURL_GET_SHOPPATCH', JTLURL_BASE . 'json_patch.php');
define('JTLURL_GET_SHOPMARKETPLACE', JTLURL_BASE . 'json_marketplace.php');
define('JTLURL_GET_SHOPHELP', JTLURL_BASE . 'jtlhelp.php');
define('JTLURL_GET_SHOPVERSION', JTLURL_BASE . 'json_version.php');
//Log-Levels
define('LOGLEVEL_ERROR', 1);
define('LOGLEVEL_NOTICE', 2);
define('LOGLEVEL_DEBUG', 3);

define('AUSWAHLASSISTENT_ORT_STARTSEITE', 'kStartseite');
define('AUSWAHLASSISTENT_ORT_KATEGORIE', 'kKategorie');
define('AUSWAHLASSISTENT_ORT_LINK', 'kLink');

define('UPLOAD_ERROR_NEED_UPLOAD', 12);

define('UPLOAD_TYP_KUNDE', 1);
define('UPLOAD_TYP_BESTELLUNG', 2);
define('UPLOAD_TYP_WARENKORBPOS', 3);

define('TEMPLATE_XML', 'template.xml');

define('SHOP_SEO', true);

//helper
function ifndef($constant, $value)
{
    defined($constant) || define($constant, $value);
}
