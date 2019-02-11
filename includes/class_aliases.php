<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

class_alias(\DB\NiceDB::class, 'NiceDB', true);
class_alias(\Session\Frontend::class, 'Session', true);
class_alias(\Session\Backend::class, 'AdminSession', true);
class_alias(\Services\JTL\LinkService::class, 'LinkHelper', true);
class_alias(\PHPMailer\PHPMailer\PHPMailer::class, 'PHPMailer', true);
class_alias(\Smarty\JTLSmarty::class, 'JTLSmarty', true);
class_alias(\Plugin\LegacyPlugin::class, 'Plugin', true);
class_alias(\Plugin\BootstrapperInterface::class, 'IPlugin', true);
class_alias(\Plugin\Bootstrapper::class, 'AbstractPlugin', true);
class_alias(\Plugin\LicenseInterface::class, 'IPluginLizenz', true);
class_alias(\Plugin\LicenseInterface::class, 'PluginLizenz', true);
class_alias(\Events\Dispatcher::class, 'EventDispatcher', true);
class_alias(\Widgets\AbstractWidget::class, 'WidgetBase', true);
class_alias(\Helpers\PHPSettings::class, 'PHPSettingsHelper', true);
class_alias(\Helpers\PaymentMethod::class, 'ZahlungsartHelper', true);
class_alias(\Helpers\ShippingMethod::class, 'VersandartHelper', true);
class_alias(\Helpers\Cart::class, 'WarenkorbHelper', true);
class_alias(\Helpers\Order::class, 'BestellungHelper', true);
class_alias(\Helpers\Category::class, 'KategorieHelper', true);
class_alias(\Helpers\Template::class, 'TemplateHelper', true);
class_alias(\Helpers\Product::class, 'ArtikelHelper', true);
class_alias(\Helpers\URL::class, 'UrlHelper', true);
class_alias(\Helpers\Manufacturer::class, 'HerstellerHelper', true);
class_alias(\Extensions\Upload::class, 'Upload', true);
class_alias(\Extensions\UploadDatei::class, 'UploadDatei', true);
class_alias(\Extensions\UploadSchema::class, 'UploadSchema', true);
class_alias(\Extensions\Download::class, 'Download', true);
class_alias(\Extensions\DownloadHistory::class, 'DownloadHistory', true);
class_alias(\Extensions\Konfiggruppe::class, 'Konfiggruppe', true);
class_alias(\Extensions\Konfiggruppesprache::class, 'Konfiggruppesprache', true);
class_alias(\Extensions\Konfigitem::class, 'Konfigitem', true);
class_alias(\Extensions\Konfigitempreis::class, 'Konfigitempreis', true);
class_alias(\Extensions\Konfigitemsprache::class, 'Konfigitemsprache', true);
class_alias(\Extensions\Konfigurator::class, 'Konfigurator', true);
class_alias(\Extensions\AuswahlAssistent::class, 'AuswahlAssistent', true);
class_alias(\Extensions\AuswahlAssistentFrage::class, 'AuswahlAssistentFrage', true);
class_alias(\Extensions\AuswahlAssistentGruppe::class, 'AuswahlAssistentGruppe', true);
class_alias(\Extensions\AuswahlAssistentOrt::class, 'AuswahlAssistentOrt', true);
class_alias(\Backend\Revision::class, 'Revision', true);
class_alias(\Backend\Status::class, 'Status', true);
class_alias(\Backend\Notification::class, 'Notification', true);
class_alias(\Backend\NotificationEntry::class, 'NotificationEntry', true);
class_alias(\Backend\AdminAccount::class, 'AdminAccount', true);
class_alias(\Backend\AdminTemplate::class, 'AdminTemplate', true);
