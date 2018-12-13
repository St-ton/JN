<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

class_alias(\DB\NiceDB::class, 'NiceDB', true);
class_alias(\Session\Session::class, 'Session', true);
class_alias(\Session\AdminSession::class, 'AdminSession', true);
class_alias(\Services\JTL\LinkService::class, 'LinkHelper', true);
class_alias(\PHPMailer\PHPMailer\PHPMailer::class, 'PHPMailer', true);
class_alias(\Smarty\JTLSmarty::class, 'JTLSmarty', true);
class_alias(\Plugin\Plugin::class, 'Plugin', true);
class_alias(\Plugin\PluginInterface::class, 'IPlugin', true);
class_alias(\Plugin\AbstractPlugin::class, 'AbstractPlugin', true);
class_alias(\Plugin\LicenseInterface::class, 'IPluginLizenz', true);
class_alias(\Plugin\LicenseInterface::class, 'PluginLizenz', true);
class_alias(\Events\Dispatcher::class, 'EventDispatcher', true);
class_alias(\Widgets\AbstractWidget::class, 'WidgetBase', true);
class_alias(\Helpers\PHPSettingsHelper::class, 'PHPSettingsHelper', true);
class_alias(\Helpers\ZahlungsartHelper::class, 'ZahlungsartHelper', true);
class_alias(\Helpers\VersandartHelper::class, 'VersandartHelper', true);
class_alias(\Helpers\WarenkorbHelper::class, 'WarenkorbHelper', true);
class_alias(\Helpers\BestellungHelper::class, 'BestellungHelper', true);
class_alias(\Helpers\KategorieHelper::class, 'KategorieHelper', true);
class_alias(\Helpers\TemplateHelper::class, 'TemplateHelper', true);
class_alias(\Helpers\ArtikelHelper::class, 'ArtikelHelper', true);
class_alias(\Helpers\UrlHelper::class, 'UrlHelper', true);
