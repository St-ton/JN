<?php declare(strict_types=1);

class_alias(\JTL\Helpers\Text::class, 'StringHandler');
class_alias(\JTL\Session\Frontend::class, 'Session');
class_alias(\JTL\Session\Backend::class, 'AdminSession');
class_alias(\JTL\Services\JTL\LinkService::class, 'LinkHelper');
class_alias(\PHPMailer\PHPMailer\PHPMailer::class, 'PHPMailer');
class_alias(\JTL\Smarty\JTLSmarty::class, 'JTLSmarty');
class_alias(\JTL\Plugin\LegacyPlugin::class, 'Plugin');
class_alias(\JTL\Plugin\BootstrapperInterface::class, 'IPlugin');
class_alias(\JTL\Plugin\Bootstrapper::class, 'AbstractPlugin');
class_alias(\JTL\Plugin\LicenseInterface::class, 'IPluginLizenz');
class_alias(\JTL\Plugin\LicenseInterface::class, 'PluginLizenz');
class_alias(\JTL\Events\Dispatcher::class, 'EventDispatcher');
class_alias(\JTL\Widgets\AbstractWidget::class, 'WidgetBase');
class_alias(\JTL\Helpers\PHPSettings::class, 'PHPSettingsHelper');
class_alias(\JTL\Helpers\PaymentMethod::class, 'ZahlungsartHelper');
class_alias(\JTL\Plugin\Payment\LegacyMethod::class, 'PaymentMethod');
class_alias(\JTL\Helpers\ShippingMethod::class, 'VersandartHelper');
class_alias(\JTL\Cart\CartHelper::class, 'WarenkorbHelper');
class_alias(\JTL\Helpers\Order::class, 'BestellungHelper');
class_alias(\JTL\Helpers\Category::class, 'KategorieHelper');
class_alias(\JTL\Helpers\Product::class, 'ArtikelHelper');
class_alias(\JTL\Helpers\URL::class, 'UrlHelper');
class_alias(\JTL\Helpers\Manufacturer::class, 'HerstellerHelper');
class_alias(\JTL\Extensions\Upload\Upload::class, 'Upload');
class_alias(\JTL\Extensions\Upload\File::class, 'UploadDatei');
class_alias(\JTL\Extensions\Upload\Scheme::class, 'UploadSchema');
class_alias(\JTL\Extensions\Download\Download::class, 'Download');
class_alias(\JTL\Extensions\Download\History::class, 'DownloadHistory');
class_alias(\JTL\Extensions\Download\Localization::class, 'DownloadSprache');
class_alias(\JTL\Extensions\Config\Group::class, 'Konfiggruppe');
class_alias(\JTL\Extensions\Config\GroupLocalization::class, 'Konfiggruppesprache');
class_alias(\JTL\Extensions\Config\Item::class, 'Konfigitem');
class_alias(\JTL\Extensions\Config\ItemPrice::class, 'Konfigitempreis');
class_alias(\JTL\Extensions\Config\ItemLocalization::class, 'Konfigitemsprache');
class_alias(\JTL\Extensions\Config\Configurator::class, 'Konfigurator');
class_alias(\JTL\Extensions\SelectionWizard\Wizard::class, 'AuswahlAssistent');
class_alias(\JTL\Extensions\SelectionWizard\Question::class, 'AuswahlAssistentFrage');
class_alias(\JTL\Extensions\SelectionWizard\Group::class, 'AuswahlAssistentGruppe');
class_alias(\JTL\Extensions\SelectionWizard\Location::class, 'AuswahlAssistentOrt');
class_alias(\JTL\Backend\Revision::class, 'Revision');
class_alias(\JTL\Backend\Status::class, 'Status');
class_alias(\JTL\SimpleCSS::class, 'SimpleCSS');
class_alias(\JTL\Piechart::class, 'Piechart');
class_alias(\JTL\Slider::class, 'Slider');
class_alias(\JTL\SimpleMail::class, 'SimpleMail');
class_alias(\JTL\ExtensionPoint::class, 'ExtensionPoint');
class_alias(\JTL\PlausiKundenfeld::class, 'PlausiKundenfeld');
class_alias(\JTL\ContentAuthor::class, 'ContentAuthor');
class_alias(\JTL\Plausi::class, 'Plausi');
class_alias(\JTL\Chartdata::class, 'Chartdata');
class_alias(\JTL\Emailhistory::class, 'Emailhistory');
class_alias(\JTL\MainModel::class, 'MainModel');
class_alias(\JTL\Cache\Methods\CacheAdvancedfile::class, 'cache_advancedfile');
class_alias(\JTL\Cache\Methods\CacheRedis::class, 'cache_redis');
class_alias(\JTL\Cache\Methods\CacheMemcached::class, 'cache_memcached');
class_alias(\JTL\Cache\Methods\CacheFile::class, 'cache_file');
class_alias(\JTL\Cache\Methods\CacheRedisCluster::class, 'cache_redisCluster');
class_alias(\JTL\Cache\Methods\CacheMemcache::class, 'cache_memcache');
class_alias(\JTL\Cache\Methods\CacheApc::class, 'cache_apc');
class_alias(\JTL\Cache\Methods\CacheNull::class, 'cache_null');
class_alias(\JTL\Cache\Methods\CacheSession::class, 'cache_session');
class_alias(\JTL\Cache\JTLCacheInterface::class, 'JTLCacheInterface');
class_alias(\JTL\Cache\ICachingMethod::class, 'ICachingMethod');
class_alias(\JTL\Cache\JTLCacheTrait::class, 'JTLCacheTrait');
class_alias(\JTL\Cache\JTLCache::class, 'JTLCache');
class_alias(\JTL\Update\DBManager::class, 'DBManager');
class_alias(\JTL\Update\DBMigrationHelper::class, 'DBMigrationHelper');
class_alias(\JTL\Update\MigrationTrait::class, 'MigrationTrait');
class_alias(\JTL\Update\IMigration::class, 'IMigration');
class_alias(\JTL\Update\MigrationTableTrait::class, 'MigrationTableTrait');
class_alias(\JTL\Update\MigrationManager::class, 'MigrationManager');
class_alias(\JTL\Update\Updater::class, 'Updater');
class_alias(\JTL\Update\MigrationHelper::class, 'MigrationHelper');
class_alias(\JTL\Update\Migration::class, 'Migration');
class_alias(\JTL\LessParser::class, 'LessParser');
class_alias(\JTL\IExtensionPoint::class, 'IExtensionPoint');
class_alias(\JTL\Statistik::class, 'Statistik');
class_alias(\JTL\Catalog\Hersteller::class, 'Hersteller');
class_alias(\JTL\Catalog\Separator::class, 'Trennzeichen');
class_alias(\JTL\Catalog\Category\KategoriePict::class, 'KategoriePict');
class_alias(\JTL\Catalog\Category\Kategorie::class, 'Kategorie');
class_alias(\JTL\Catalog\Category\KategorieArtikel::class, 'KategorieArtikel');
class_alias(\JTL\Catalog\Category\KategorieListe::class, 'KategorieListe');
class_alias(\JTL\Catalog\UnitsOfMeasure::class, 'UnitsOfMeasure');
class_alias(\JTL\Catalog\ComparisonList::class, 'Vergleichsliste');
class_alias(\JTL\Catalog\Product\EigenschaftWert::class, 'EigenschaftWert');
class_alias(\JTL\Catalog\Product\Artikel::class, 'Artikel');
class_alias(\JTL\Catalog\Product\Bewertung::class, 'Bewertung');
class_alias(\JTL\Catalog\Product\MerkmalWert::class, 'MerkmalWert');
class_alias(\JTL\Catalog\Product\Preise::class, 'Preise');
class_alias(\JTL\Catalog\Product\PriceRange::class, 'PriceRange');
class_alias(\JTL\Catalog\Product\ArtikelListe::class, 'ArtikelListe');
class_alias(\JTL\Catalog\Product\Merkmal::class, 'Merkmal');
class_alias(\JTL\Catalog\Product\Preisverlauf::class, 'Preisverlauf');
class_alias(\JTL\Catalog\Product\Bestseller::class, 'Bestseller');
class_alias(\JTL\Catalog\Warehouse::class, 'Warenlager');
class_alias(\JTL\Catalog\NavigationEntry::class, 'NavigationEntry');
class_alias(\JTL\Catalog\Currency::class, 'Currency');
class_alias(\JTL\Catalog\Navigation::class, 'Navigation');
class_alias(\JTL\Catalog\Wishlist\WishlistItem::class, 'WunschlistePos');
class_alias(\JTL\Catalog\Wishlist\WishlistItemProperty::class, 'WunschlistePosEigenschaft');
class_alias(\JTL\Catalog\Wishlist\Wishlist::class, 'Wunschliste');
class_alias(\JTL\Alert\Alert::class, 'Alert');
class_alias(\JTL\Network\Communication::class, 'Communication');
class_alias(\JTL\Network\MultiRequest::class, 'MultiRequest');
class_alias(\JTL\Network\JTLApi::class, 'JTLApi');
class_alias(\JTL\IO\IOFile::class, 'IOFile');
class_alias(\JTL\IO\IO::class, 'IO');
class_alias(\JTL\IO\IOResponse::class, 'IOResponse');
class_alias(\JTL\IO\IOMethods::class, 'IOMethods');
class_alias(\JTL\IO\IOError::class, 'IOError');
class_alias(\JTL\XML::class, 'XML');
class_alias(\JTL\Shop::class, 'Shop');
class_alias(\JTL\Path::class, 'Path');
class_alias(\JTL\Language\LanguageHelper::class, 'Sprache');
class_alias(\JTL\Backend\TwoFA::class, 'TwoFA');
class_alias(\JTL\Backend\DirManager::class, 'DirManager');
class_alias(\JTL\Backend\AdminFavorite::class, 'AdminFavorite');
class_alias(\JTL\Backend\AdminIO::class, 'AdminIO');
class_alias(\JTL\Backend\AdminTemplate::class, 'AdminTemplate');
class_alias(\JTL\Backend\NotificationEntry::class, 'NotificationEntry');
class_alias(\JTL\Backend\TwoFAEmergency::class, 'TwoFAEmergency');
class_alias(\JTL\Backend\JSONAPI::class, 'JSONAPI');
class_alias(\JTL\Backend\Notification::class, 'Notification');
class_alias(\JTL\Backend\AdminAccount::class, 'AdminAccount');
class_alias(\JTL\Smarty\JTLSmartyTemplateClass::class, 'JTLSmartyTemplateClass');
class_alias(\JTL\Smarty\SmartyResourceNiceDB::class, 'SmartyResourceNiceDB');
class_alias(\JTL\Shopsetting::class, 'Shopsetting');
class_alias(\JTL\Checkout\Eigenschaft::class, 'Eigenschaft');
class_alias(\JTL\Checkout\Bestellung::class, 'Bestellung');
class_alias(\JTL\Checkout\Kupon::class, 'Kupon');
class_alias(\JTL\Checkout\Nummern::class, 'Nummern');
class_alias(\JTL\Checkout\Adresse::class, 'Adresse');
class_alias(\JTL\Checkout\ZipValidator::class, 'ZipValidator');
class_alias(\JTL\Checkout\Zahlungsart::class, 'Zahlungsart');
class_alias(\JTL\Checkout\Rechnungsadresse::class, 'Rechnungsadresse');
class_alias(\JTL\Checkout\ZahlungsLog::class, 'ZahlungsLog');
class_alias(\JTL\Checkout\Versandart::class, 'Versandart');
class_alias(\JTL\Checkout\Lieferadresse::class, 'Lieferadresse');
class_alias(\JTL\Checkout\ZahlungsInfo::class, 'ZahlungsInfo');
class_alias(\JTL\Checkout\Lieferscheinpos::class, 'Lieferscheinpos');
class_alias(\JTL\Checkout\Lieferscheinposinfo::class, 'Lieferscheinposinfo');
class_alias(\JTL\Checkout\Versand::class, 'Versand');
class_alias(\JTL\Checkout\Lieferschein::class, 'Lieferschein');
class_alias(\JTL\Checkout\KuponBestellung::class, 'KuponBestellung');
class_alias(\JTL\SingletonTrait::class, 'SingletonTrait');
class_alias(\JTL\Campaign::class, 'Kampagne');
class_alias(\JTL\PlausiTrennzeichen::class, 'PlausiTrennzeichen');
class_alias(\JTL\Firma::class, 'Firma');
class_alias(\JTL\MagicCompatibilityTrait::class, 'MagicCompatibilityTrait');
class_alias(\JTL\Staat::class, 'Staat');
class_alias(\JTL\Cart\PersistentCartItem::class, 'WarenkorbPersPos');
class_alias(\JTL\Cart\Cart::class, 'Warenkorb');
class_alias(\JTL\Cart\CartItemProperty::class, 'WarenkorbPosEigenschaft');
class_alias(\JTL\Cart\CartItem::class, 'WarenkorbPos');
class_alias(\JTL\Cart\PersistentCartItemProperty::class, 'WarenkorbPersPosEigenschaft');
class_alias(\JTL\Cart\PersistentCart::class, 'WarenkorbPers');
class_alias(\JTL\DB\NiceDB::class, 'NiceDB');
class_alias(\JTL\DB\DbInterface::class, 'DbInterface');
class_alias(\JTL\Backend\CustomerFields::class, 'CustomerFields');
class_alias(\JTL\Linechart::class, 'Linechart');
class_alias(\JTL\Cron\JobQueue::class, 'JobQueue');
class_alias(\JTL\Nice::class, 'Nice');
class_alias(\JTL\ImageMap::class, 'ImageMap');
class_alias(\JTL\CheckBox::class, 'CheckBox');
class_alias(\JTL\Redirect::class, 'Redirect');
class_alias(\JTL\Events\Event::class, 'Event');
class_alias(\JTL\Events\Dispatcher::class, 'Dispatcher');
class_alias(\JTL\Statusmail::class, 'Statusmail');
class_alias(\JTL\Slide::class, 'Slide');
class_alias(\JTL\Profiler::class, 'Profiler');
class_alias(\JTL\Jtllog::class, 'Jtllog');
class_alias(\JTL\Customer\CustomerGroup::class, 'Kundengruppe');
class_alias(\JTL\Customer\DataHistory::class, 'Kundendatenhistory');
class_alias(\JTL\Customer\Customer::class, 'Kunde');
class_alias(\JTL\PlausiCMS::class, 'PlausiCMS');
class_alias(\JTL\Media\Image\Product::class, 'MediaImage');
class_alias(\JTL\Media\MediaImageSize::class, 'MediaImageSize');
class_alias(\JTL\Media\Media::class, 'Media');
class_alias(\JTL\Media\Image::class, 'Image');
class_alias(\JTL\Media\IMedia::class, 'IMedia');
class_alias(\JTL\Media\MediaImageRequest::class, 'MediaImageRequest');
class_alias(\JTL\phpQuery\phpQuery::class, 'phpQuery');
class_alias(\JTL\phpQuery\phpQueryObject::class, 'phpQueryObject');
class_alias(\JTL\xtea\XTEA::class, 'XTEA');

if (false) { // trick IDEs into displaying deprecations
    /** @deprecated since 5.0.0 */
    class XTEA{}
    /** @deprecated since 5.0.0 */
    class phpQueryObject{}
    /** @deprecated since 5.0.0 */
    class phpQuery{}
    /** @deprecated since 5.0.0 */
    class StringHandler{}
    /** @deprecated since 5.0.0 */
    class MediaImageRequest{}
    /** @deprecated since 5.0.0 */
    class IMedia{}
    /** @deprecated since 5.0.0 */
    class Image{}
    /** @deprecated since 5.0.0 */
    class Media{}
    /** @deprecated since 5.0.0 */
    class MediaImageSize{}
    /** @deprecated since 5.0.0 */
    class MediaImage{}
    /** @deprecated since 5.0.0 */
    class PlausiCMS{}
    /** @deprecated since 5.0.0 */
    class Kunde{}
    /** @deprecated since 5.0.0 */
    class Kundendatenhistory{}
    /** @deprecated since 5.0.0 */
    class Kundengruppe{}
    /** @deprecated since 5.0.0 */
    class Jtllog{}
    /** @deprecated since 5.0.0 */
    class Profiler{}
    /** @deprecated since 5.0.0 */
    class Slide{}
    /** @deprecated since 5.0.0 */
    class Statusmail{}
    /** @deprecated since 5.0.0 */
    class Dispatcher{}
    /** @deprecated since 5.0.0 */
    class Event{}
    /** @deprecated since 5.0.0 */
    class Redirect{}
    /** @deprecated since 5.0.0 */
    class CheckBox{}
    /** @deprecated since 5.0.0 */
    class ImageMap{}
    /** @deprecated since 5.0.0 */
    class Nice{}
    /** @deprecated since 5.0.0 */
    class JobQueue{}
    /** @deprecated since 5.0.0 */
    class Linechart{}
    /** @deprecated since 5.0.0 */
    class CustomerFields{}
    /** @deprecated since 5.0.0 */
    class DbInterface{}
    /** @deprecated since 5.0.0 */
    class NiceDB{}
    /** @deprecated since 5.0.0 */
    class WarenkorbPers{}
    /** @deprecated since 5.0.0 */
    class WarenkorbPersPosEigenschaft{}
    /** @deprecated since 5.0.0 */
    class WarenkorbPos{}
    /** @deprecated since 5.0.0 */
    class WarenkorbPosEigenschaft{}
    /** @deprecated since 5.0.0 */
    class Warenkorb{}
    /** @deprecated since 5.0.0 */
    class WarenkorbPersPos{}
    /** @deprecated since 5.0.0 */
    class Staat{}
    /** @deprecated since 5.0.0 */
    class MagicCompatibilityTrait{}
    /** @deprecated since 5.0.0 */
    class Firma{}
    /** @deprecated since 5.0.0 */
    class PlausiTrennzeichen{}
    /** @deprecated since 5.0.0 */
    class Kampagne{}
    /** @deprecated since 5.0.0 */
    class SingletonTrait{}
    /** @deprecated since 5.0.0 */
    class KuponBestellung{}
    /** @deprecated since 5.0.0 */
    class Lieferschein{}
    /** @deprecated since 5.0.0 */
    class Versand{}
    /** @deprecated since 5.0.0 */
    class Lieferscheinposinfo{}
    /** @deprecated since 5.0.0 */
    class Lieferscheinpos{}
    /** @deprecated since 5.0.0 */
    class ZahlungsInfo{}
    /** @deprecated since 5.0.0 */
    class Lieferadresse{}
    /** @deprecated since 5.0.0 */
    class Versandart{}
    /** @deprecated since 5.0.0 */
    class ZahlungsLog{}
    /** @deprecated since 5.0.0 */
    class Rechnungsadresse{}
    /** @deprecated since 5.0.0 */
    class Zahlungsart{}
    /** @deprecated since 5.0.0 */
    class ZipValidator{}
    /** @deprecated since 5.0.0 */
    class Adresse{}
    /** @deprecated since 5.0.0 */
    class Nummern{}
    /** @deprecated since 5.0.0 */
    class Kupon{}
    /** @deprecated since 5.0.0 */
    class Bestellung{}
    /** @deprecated since 5.0.0 */
    class Eigenschaft{}
    /** @deprecated since 5.0.0 */
    class Shopsetting{}
    /** @deprecated since 5.0.0 */
    class SmartyResourceNiceDB{}
    /** @deprecated since 5.0.0 */
    class JTLSmartyTemplateClass{}
    /** @deprecated since 5.0.0 */
    class AdminAccount{}
    /** @deprecated since 5.0.0 */
    class Notification{}
    /** @deprecated since 5.0.0 */
    class JSONAPI{}
    /** @deprecated since 5.0.0 */
    class TwoFAEmergency{}
    /** @deprecated since 5.0.0 */
    class NotificationEntry{}
    /** @deprecated since 5.0.0 */
    class AdminTemplate{}
    /** @deprecated since 5.0.0 */
    class AdminIO{}
    /** @deprecated since 5.0.0 */
    class AdminFavorite{}
    /** @deprecated since 5.0.0 */
    class DirManager{}
    /** @deprecated since 5.0.0 */
    class TwoFA{}
    /** @deprecated since 5.0.0 */
    class Sprache{}
    /** @deprecated since 5.0.0 */
    class Path{}
    /** @deprecated since 5.0.0 */
    class Shop{}
    /** @deprecated since 5.0.0 */
    class XML{}
    /** @deprecated since 5.0.0 */
    class IOError{}
    /** @deprecated since 5.0.0 */
    class IOMethods{}
    /** @deprecated since 5.0.0 */
    class IOResponse{}
    /** @deprecated since 5.0.0 */
    class IO{}
    /** @deprecated since 5.0.0 */
    class IOFile{}
    /** @deprecated since 5.0.0 */
    class JTLApi{}
    /** @deprecated since 5.0.0 */
    class MultiRequest{}
    /** @deprecated since 5.0.0 */
    class Communication{}
    /** @deprecated since 5.0.0 */
    class Alert{}
    /** @deprecated since 5.0.0 */
    class Wunschliste{}
    /** @deprecated since 5.0.0 */
    class WunschlistePosEigenschaft{}
    /** @deprecated since 5.0.0 */
    class WunschlistePos{}
    /** @deprecated since 5.0.0 */
    class Navigation{}
    /** @deprecated since 5.0.0 */
    class Currency{}
    /** @deprecated since 5.0.0 */
    class NavigationEntry{}
    /** @deprecated since 5.0.0 */
    class Warenlager{}
    /** @deprecated since 5.0.0 */
    class Bestseller{}
    /** @deprecated since 5.0.0 */
    class Preisverlauf{}
    /** @deprecated since 5.0.0 */
    class Merkmal{}
    /** @deprecated since 5.0.0 */
    class ArtikelListe{}
    /** @deprecated since 5.0.0 */
    class PriceRange{}
    /** @deprecated since 5.0.0 */
    class Preise{}
    /** @deprecated since 5.0.0 */
    class MerkmalWert{}
    /** @deprecated since 5.0.0 */
    class Bewertung{}
    /** @deprecated since 5.0.0 */
    class Artikel{}
    /** @deprecated since 5.0.0 */
    class EigenschaftWert{}
    /** @deprecated since 5.0.0 */
    class Vergleichsliste{}
    /** @deprecated since 5.0.0 */
    class UnitsOfMeasure{}
    /** @deprecated since 5.0.0 */
    class KategorieListe{}
    /** @deprecated since 5.0.0 */
    class KategorieArtikel{}
    /** @deprecated since 5.0.0 */
    class Kategorie{}
    /** @deprecated since 5.0.0 */
    class KategoriePict{}
    /** @deprecated since 5.0.0 */
    class Trennzeichen{}
    /** @deprecated since 5.0.0 */
    class Hersteller{}
    /** @deprecated since 5.0.0 */
    class Statistik{}
    /** @deprecated since 5.0.0 */
    class IExtensionPoint{}
    /** @deprecated since 5.0.0 */
    class LessParser{}
    /** @deprecated since 5.0.0 */
    class Migration{}
    /** @deprecated since 5.0.0 */
    class MigrationHelper{}
    /** @deprecated since 5.0.0 */
    class Updater{}
    /** @deprecated since 5.0.0 */
    class MigrationManager{}
    /** @deprecated since 5.0.0 */
    class MigrationTableTrait{}
    /** @deprecated since 5.0.0 */
    class IMigration{}
    /** @deprecated since 5.0.0 */
    class MigrationTrait{}
    /** @deprecated since 5.0.0 */
    class DBMigrationHelper{}
    /** @deprecated since 5.0.0 */
    class DBManager{}
    /** @deprecated since 5.0.0 */
    class JTLCache{}
    /** @deprecated since 5.0.0 */
    class JTLCacheTrait{}
    /** @deprecated since 5.0.0 */
    class ICachingMethod{}
    /** @deprecated since 5.0.0 */
    class JTLCacheInterface{}
    /** @deprecated since 5.0.0 */
    class cache_session{}
    /** @deprecated since 5.0.0 */
    class cache_null{}
    /** @deprecated since 5.0.0 */
    class cache_apc{}
    /** @deprecated since 5.0.0 */
    class cache_memcache{}
    /** @deprecated since 5.0.0 */
    class cache_redisCluster{}
    /** @deprecated since 5.0.0 */
    class cache_file{}
    /** @deprecated since 5.0.0 */
    class cache_memcached{}
    /** @deprecated since 5.0.0 */
    class cache_redis{}
    /** @deprecated since 5.0.0 */
    class cache_advancedfile{}
    /** @deprecated since 5.0.0 */
    class MainModel{}
    /** @deprecated since 5.0.0 */
    class Emailhistory{}
    /** @deprecated since 5.0.0 */
    class Chartdata{}
    /** @deprecated since 5.0.0 */
    class Plausi{}
    /** @deprecated since 5.0.0 */
    class ContentAuthor{}
    /** @deprecated since 5.0.0 */
    class PlausiKundenfeld{}
    /** @deprecated since 5.0.0 */
    class ExtensionPoint{}
    /** @deprecated since 5.0.0 */
    class SimpleMail{}
    /** @deprecated since 5.0.0 */
    class Slider{}
    /** @deprecated since 5.0.0 */
    class Piechart{}
    /** @deprecated since 5.0.0 */
    class SimpleCSS{}
    /** @deprecated since 5.0.0 */
    class Status{}
    /** @deprecated since 5.0.0 */
    class Revision{}
    /** @deprecated since 5.0.0 */
    class AuswahlAssistentOrt{}
    /** @deprecated since 5.0.0 */
    class AuswahlAssistentGruppe{}
    /** @deprecated since 5.0.0 */
    class AuswahlAssistentFrage{}
    /** @deprecated since 5.0.0 */
    class AuswahlAssistent{}
    /** @deprecated since 5.0.0 */
    class Konfigurator{}
    /** @deprecated since 5.0.0 */
    class Konfigitemsprache{}
    /** @deprecated since 5.0.0 */
    class Konfigitempreis{}
    /** @deprecated since 5.0.0 */
    class Konfigitem{}
    /** @deprecated since 5.0.0 */
    class Konfiggruppesprache{}
    /** @deprecated since 5.0.0 */
    class Konfiggruppe{}
    /** @deprecated since 5.0.0 */
    class DownloadSprache{}
    /** @deprecated since 5.0.0 */
    class DownloadHistory{}
    /** @deprecated since 5.0.0 */
    class Download{}
    /** @deprecated since 5.0.0 */
    class UploadSchema{}
    /** @deprecated since 5.0.0 */
    class UploadDatei{}
    /** @deprecated since 5.0.0 */
    class Upload{}
    /** @deprecated since 5.0.0 */
    class HerstellerHelper{}
    /** @deprecated since 5.0.0 */
    class UrlHelper{}
    /** @deprecated since 5.0.0 */
    class ArtikelHelper{}
    /** @deprecated since 5.0.0 */
    class KategorieHelper{}
    /** @deprecated since 5.0.0 */
    class BestellungHelper{}
    /** @deprecated since 5.0.0 */
    class WarenkorbHelper{}
    /** @deprecated since 5.0.0 */
    class VersandartHelper{}
    /** @deprecated since 5.0.0 */
    class PaymentMethod{}
    /** @deprecated since 5.0.0 */
    class ZahlungsartHelper{}
    /** @deprecated since 5.0.0 */
    class PHPSettingsHelper{}
    /** @deprecated since 5.0.0 */
    class WidgetBase{}
    /** @deprecated since 5.0.0 */
    class EventDispatcher{}
    /** @deprecated since 5.0.0 */
    class IPluginLizenz{}
    /** @deprecated since 5.0.0 */
    class AbstractPlugin{}
    /** @deprecated since 5.0.0 */
    class PluginLizenz{}
    /** @deprecated since 5.0.0 */
    class IPlugin{}
    /** @deprecated since 5.0.0 */
    class Plugin{}
    /** @deprecated since 5.0.0 */
    class PHPMailer{}
    /** @deprecated since 5.0.0 */
    class LinkHelper{}
    /** @deprecated since 5.0.0 */
    class AdminSession{}
    /** @deprecated since 5.0.0 */
    class Session{}
    /** @deprecated since 5.0.0 */
    class StringHandler{}
}
