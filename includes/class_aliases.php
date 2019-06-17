<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

class_alias(\JTL\Helpers\Text::class, 'StringHandler', true);
class_alias(\JTL\Smarty\JTLSmarty::class, 'Smarty\JTLSmarty', true);
class_alias(\JTL\Session\Frontend::class, 'Session', true);
class_alias(\JTL\Session\Backend::class, 'AdminSession', true);
class_alias(\JTL\Services\JTL\LinkService::class, 'LinkHelper', true);
class_alias(\PHPMailer\PHPMailer\PHPMailer::class, 'PHPMailer', true);
class_alias(\JTL\Smarty\JTLSmarty::class, 'JTLSmarty', true);
class_alias(\JTL\Plugin\LegacyPlugin::class, 'Plugin', true);
class_alias(\JTL\Plugin\BootstrapperInterface::class, 'IPlugin', true);
class_alias(\JTL\Plugin\Bootstrapper::class, 'AbstractPlugin', true);
class_alias(\JTL\Plugin\LicenseInterface::class, 'IPluginLizenz', true);
class_alias(\JTL\Plugin\LicenseInterface::class, 'PluginLizenz', true);
class_alias(\JTL\Events\Dispatcher::class, 'EventDispatcher', true);
class_alias(\JTL\Widgets\AbstractWidget::class, 'WidgetBase', true);
class_alias(\JTL\Helpers\PHPSettings::class, 'PHPSettingsHelper', true);
class_alias(\JTL\Helpers\PaymentMethod::class, 'ZahlungsartHelper', true);
class_alias(\JTL\Helpers\ShippingMethod::class, 'VersandartHelper', true);
class_alias(\JTL\Helpers\Cart::class, 'WarenkorbHelper', true);
class_alias(\JTL\Helpers\Order::class, 'BestellungHelper', true);
class_alias(\JTL\Helpers\Category::class, 'KategorieHelper', true);
class_alias(\JTL\Helpers\Template::class, 'TemplateHelper', true);
class_alias(\JTL\Helpers\Product::class, 'ArtikelHelper', true);
class_alias(\JTL\Helpers\URL::class, 'UrlHelper', true);
class_alias(\JTL\Helpers\Manufacturer::class, 'HerstellerHelper', true);
class_alias(\JTL\Extensions\Upload::class, 'Upload', true);
class_alias(\JTL\Extensions\UploadDatei::class, 'UploadDatei', true);
class_alias(\JTL\Extensions\UploadSchema::class, 'UploadSchema', true);
class_alias(\JTL\Extensions\Download::class, 'Download', true);
class_alias(\JTL\Extensions\DownloadHistory::class, 'DownloadHistory', true);
class_alias(\JTL\Extensions\Konfiggruppe::class, 'Konfiggruppe', true);
class_alias(\JTL\Extensions\Konfiggruppesprache::class, 'Konfiggruppesprache', true);
class_alias(\JTL\Extensions\Konfigitem::class, 'Konfigitem', true);
class_alias(\JTL\Extensions\Konfigitempreis::class, 'Konfigitempreis', true);
class_alias(\JTL\Extensions\Konfigitemsprache::class, 'Konfigitemsprache', true);
class_alias(\JTL\Extensions\Konfigurator::class, 'Konfigurator', true);
class_alias(\JTL\Extensions\AuswahlAssistent::class, 'AuswahlAssistent', true);
class_alias(\JTL\Extensions\AuswahlAssistentFrage::class, 'AuswahlAssistentFrage', true);
class_alias(\JTL\Extensions\AuswahlAssistentGruppe::class, 'AuswahlAssistentGruppe', true);
class_alias(\JTL\Extensions\AuswahlAssistentOrt::class, 'AuswahlAssistentOrt', true);
class_alias(\JTL\Backend\Revision::class, 'Revision', true);
class_alias(\JTL\Backend\Status::class, 'Status', true);
class_alias(\JTL\SimpleCSS::class, 'SimpleCSS', true);
class_alias(\JTL\Piechart::class, 'Piechart', true);
class_alias(\JTL\Slider::class, 'Slider', true);
class_alias(\JTL\XMLParser::class, 'XMLParser', true);
class_alias(\JTL\Customer\KundenwerbenKunden::class, 'KundenwerbenKunden', true);
class_alias(\JTL\SimpleMail::class, 'SimpleMail', true);
class_alias(\JTL\ExtensionPoint::class, 'ExtensionPoint', true);
class_alias(\JTL\PlausiKundenfeld::class, 'PlausiKundenfeld', true);
class_alias(\JTL\ContentAuthor::class, 'ContentAuthor', true);
class_alias(\JTL\Plausi::class, 'Plausi', true);
class_alias(\JTL\Chartdata::class, 'Chartdata', true);
class_alias(\JTL\Emailhistory::class, 'Emailhistory', true);
class_alias(\JTL\MainModel::class, 'MainModel', true);
class_alias(\JTL\Cache\Methods\CacheAdvancedfile::class, 'cache_advancedfile', true);
class_alias(\JTL\Cache\Methods\CacheRedis::class, 'cache_redis', true);
class_alias(\JTL\Cache\Methods\CacheMemcached::class, 'cache_memcached', true);
class_alias(\JTL\Cache\Methods\CacheFile::class, 'cache_file', true);
class_alias(\JTL\Cache\Methods\CacheRedisCluster::class, 'cache_redisCluster', true);
class_alias(\JTL\Cache\Methods\CacheXcache::class, 'cache_xcache', true);
class_alias(\JTL\Cache\Methods\CacheMemcache::class, 'cache_memcache', true);
class_alias(\JTL\Cache\Methods\CacheApc::class, 'cache_apc', true);
class_alias(\JTL\Cache\Methods\CacheNull::class, 'cache_null', true);
class_alias(\JTL\Cache\Methods\CacheSession::class, 'cache_session', true);
class_alias(\JTL\Cache\JTLCacheInterface::class, 'JTLCacheInterface', true);
class_alias(\JTL\Cache\ICachingMethod::class, 'ICachingMethod', true);
class_alias(\JTL\Cache\JTLCacheTrait::class, 'JTLCacheTrait', true);
class_alias(\JTL\Cache\JTLCache::class, 'JTLCache', true);
class_alias(\JTL\Link\LegacyLink::class, 'Link', true);
class_alias(\JTL\Update\DBManager::class, 'DBManager', true);
class_alias(\JTL\Update\DBMigrationHelper::class, 'DBMigrationHelper', true);
class_alias(\JTL\Update\MigrationTrait::class, 'MigrationTrait', true);
class_alias(\JTL\Update\IMigration::class, 'IMigration', true);
class_alias(\JTL\Update\MigrationTableTrait::class, 'MigrationTableTrait', true);
class_alias(\JTL\Update\MigrationManager::class, 'MigrationManager', true);
class_alias(\JTL\Update\Updater::class, 'Updater', true);
class_alias(\JTL\Update\MigrationHelper::class, 'MigrationHelper', true);
class_alias(\JTL\Update\Migration::class, 'Migration', true);
class_alias(\JTL\LessParser::class, 'LessParser', true);
class_alias(\JTL\IExtensionPoint::class, 'IExtensionPoint', true);
class_alias(\JTL\Emailvorlage::class, 'Emailvorlage', true);
class_alias(\JTL\Statistik::class, 'Statistik', true);
class_alias(\JTL\Catalog\Hersteller::class, 'Hersteller', true);
class_alias(\JTL\Catalog\Trennzeichen::class, 'Trennzeichen', true);
class_alias(\JTL\Catalog\Category\KategoriePict::class, 'KategoriePict', true);
class_alias(\JTL\Catalog\Category\Kategorie::class, 'Kategorie', true);
class_alias(\JTL\Catalog\Category\KategorieArtikel::class, 'KategorieArtikel', true);
class_alias(\JTL\Catalog\Category\KategorieListe::class, 'KategorieListe', true);
class_alias(\JTL\Catalog\UnitsOfMeasure::class, 'UnitsOfMeasure', true);
class_alias(\JTL\Catalog\Tag::class, 'Tag', true);
class_alias(\JTL\Catalog\Vergleichsliste::class, 'Vergleichsliste', true);
class_alias(\JTL\Catalog\Product\EigenschaftWert::class, 'EigenschaftWert', true);
class_alias(\JTL\Catalog\Product\Artikel::class, 'Artikel', true);
class_alias(\JTL\Catalog\Product\Bewertung::class, 'Bewertung', true);
class_alias(\JTL\Catalog\Product\MerkmalWert::class, 'MerkmalWert', true);
class_alias(\JTL\Catalog\Product\Preise::class, 'Preise', true);
class_alias(\JTL\Catalog\Product\PriceRange::class, 'PriceRange', true);
class_alias(\JTL\Catalog\Product\ArtikelListe::class, 'ArtikelListe', true);
class_alias(\JTL\Catalog\Product\Merkmal::class, 'Merkmal', true);
class_alias(\JTL\Catalog\Product\PreisverlaufGraph::class, 'PreisverlaufGraph', true);
class_alias(\JTL\Catalog\Product\Preisverlauf::class, 'Preisverlauf', true);
class_alias(\JTL\Catalog\Product\Bestseller::class, 'Bestseller', true);
class_alias(\JTL\Catalog\TagArticle::class, 'TagArticle', true);
class_alias(\JTL\Catalog\Warenlager::class, 'Warenlager', true);
class_alias(\JTL\Catalog\NavigationEntry::class, 'NavigationEntry', true);
class_alias(\JTL\Catalog\Currency::class, 'Currency', true);
class_alias(\JTL\Catalog\Navigation::class, 'Navigation', true);
class_alias(\JTL\Catalog\Wishlist\WunschlistePos::class, 'WunschlistePos', true);
class_alias(\JTL\Catalog\Wishlist\WunschlistePosEigenschaft::class, 'WunschlistePosEigenschaft', true);
class_alias(\JTL\Catalog\Wishlist\Wunschliste::class, 'Wunschliste', true);
class_alias(\JTL\Alert\Alert::class, 'Alert', true);
class_alias(\JTL\Network\Communication::class, 'Communication', true);
class_alias(\JTL\Network\MultiRequest::class, 'MultiRequest', true);
class_alias(\JTL\Network\JTLApi::class, 'JTLApi', true);
class_alias(\JTL\IO\IOFile::class, 'IOFile', true);
class_alias(\JTL\IO\IO::class, 'IO', true);
class_alias(\JTL\IO\IOResponse::class, 'IOResponse', true);
class_alias(\JTL\IO\IOMethods::class, 'IOMethods', true);
class_alias(\JTL\IO\IOError::class, 'IOError', true);
class_alias(\JTL\Exportformat::class, 'Exportformat', true);
class_alias(\JTL\XML::class, 'XML', true);
class_alias(\JTL\Shop::class, 'Shop', true);
class_alias(\JTL\Path::class, 'Path', true);
class_alias(\JTL\Language\LanguageHelper::class, 'Sprache', true);
class_alias(\JTL\Backend\TwoFA::class, 'TwoFA', true);
class_alias(\JTL\Backend\DirManager::class, 'DirManager', true);
class_alias(\JTL\Backend\AdminFavorite::class, 'AdminFavorite', true);
class_alias(\JTL\Backend\AdminIO::class, 'AdminIO', true);
class_alias(\JTL\Backend\AdminTemplate::class, 'AdminTemplate', true);
class_alias(\JTL\Backend\NotificationEntry::class, 'NotificationEntry', true);
class_alias(\JTL\Backend\TwoFAEmergency::class, 'TwoFAEmergency', true);
class_alias(\JTL\Backend\JSONAPI::class, 'JSONAPI', true);
class_alias(\JTL\Backend\Notification::class, 'Notification', true);
class_alias(\JTL\Backend\AdminAccount::class, 'AdminAccount', true);
class_alias(\JTL\Smarty\JTLSmartyTemplateClass::class, 'JTLSmartyTemplateClass', true);
class_alias(\JTL\Smarty\SmartyResourceNiceDB::class, 'SmartyResourceNiceDB', true);
class_alias(\JTL\Shopsetting::class, 'Shopsetting', true);
class_alias(\JTL\Checkout\Eigenschaft::class, 'Eigenschaft', true);
class_alias(\JTL\Checkout\Bestellung::class, 'Bestellung', true);
class_alias(\JTL\Checkout\Kupon::class, 'Kupon', true);
class_alias(\JTL\Checkout\Nummern::class, 'Nummern', true);
class_alias(\JTL\Checkout\Adresse::class, 'Adresse', true);
class_alias(\JTL\Checkout\ZipValidator::class, 'ZipValidator', true);
class_alias(\JTL\Checkout\Zahlungsart::class, 'Zahlungsart', true);
class_alias(\JTL\Checkout\Rechnungsadresse::class, 'Rechnungsadresse', true);
class_alias(\JTL\Checkout\ZahlungsLog::class, 'ZahlungsLog', true);
class_alias(\JTL\Checkout\Versandart::class, 'Versandart', true);
class_alias(\JTL\Checkout\Lieferadresse::class, 'Lieferadresse', true);
class_alias(\JTL\Checkout\ZahlungsInfo::class, 'ZahlungsInfo', true);
class_alias(\JTL\Checkout\Lieferscheinpos::class, 'Lieferscheinpos', true);
class_alias(\JTL\Checkout\Lieferscheinposinfo::class, 'Lieferscheinposinfo', true);
class_alias(\JTL\Checkout\Versand::class, 'Versand', true);
class_alias(\JTL\Checkout\Lieferschein::class, 'Lieferschein', true);
class_alias(\JTL\Checkout\KuponBestellung::class, 'KuponBestellung', true);
class_alias(\JTL\SingletonTrait::class, 'SingletonTrait', true);
class_alias(\JTL\Kampagne::class, 'Kampagne', true);
class_alias(\JTL\PlausiTrennzeichen::class, 'PlausiTrennzeichen', true);
class_alias(\JTL\Firma::class, 'Firma', true);
class_alias(\JTL\MagicCompatibilityTrait::class, 'MagicCompatibilityTrait', true);
class_alias(\JTL\Staat::class, 'Staat', true);
class_alias(\JTL\Cart\WarenkorbPersPos::class, 'WarenkorbPersPos', true);
class_alias(\JTL\Cart\Warenkorb::class, 'Warenkorb', true);
class_alias(\JTL\Cart\WarenkorbPosEigenschaft::class, 'WarenkorbPosEigenschaft', true);
class_alias(\JTL\Cart\WarenkorbPos::class, 'WarenkorbPos', true);
class_alias(\JTL\Cart\WarenkorbPersPosEigenschaft::class, 'WarenkorbPersPosEigenschaft', true);
class_alias(\JTL\Cart\WarenkorbPers::class, 'WarenkorbPers', true);
class_alias(\JTL\DB\NiceDB::class, 'NiceDB', true);
class_alias(\JTL\DB\DbInterface::class, 'DbInterface', true);
class_alias(\JTL\Backend\CustomerFields::class, 'CustomerFields', true);
class_alias(\JTL\Boxes\LegacyBoxes::class, 'Boxen', true);
class_alias(\JTL\Linechart::class, 'Linechart', true);
class_alias(\JTL\Cron\JobQueue::class, 'JobQueue', true);
class_alias(\JTL\Nice::class, 'Nice', true);
class_alias(\JTL\ImageMap::class, 'ImageMap', true);
class_alias(\JTL\CheckBox::class, 'CheckBox', true);
class_alias(\JTL\Redirect::class, 'Redirect', true);
class_alias(\JTL\Template::class, 'Template', true);
class_alias(\JTL\Events\Event::class, 'Event', true);
class_alias(\JTL\Events\Dispatcher::class, 'Dispatcher', true);
class_alias(\JTL\Statusmail::class, 'Statusmail', true);
class_alias(\JTL\Slide::class, 'Slide', true);
class_alias(\JTL\Profiler::class, 'Profiler', true);
class_alias(\JTL\Jtllog::class, 'Jtllog', true);
class_alias(\JTL\Customer\Kundengruppe::class, 'Kundengruppe', true);
class_alias(\JTL\Customer\Kundendatenhistory::class, 'Kundendatenhistory', true);
class_alias(\JTL\Customer\Kunde::class, 'Kunde', true);
class_alias(\JTL\PlausiCMS::class, 'PlausiCMS', true);
class_alias(\JTL\Media\MediaImageCompatibility::class, 'MediaImageCompatibility', true);
class_alias(\JTL\Media\MediaImage::class, 'MediaImage', true);
class_alias(\JTL\Media\MediaImageSize::class, 'MediaImageSize', true);
class_alias(\JTL\Media\Media::class, 'Media', true);
class_alias(\JTL\Media\Image::class, 'Image', true);
class_alias(\JTL\Media\IMedia::class, 'IMedia', true);
class_alias(\JTL\Media\MediaImageRequest::class, 'MediaImageRequest', true);
