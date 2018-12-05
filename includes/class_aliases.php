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
class_alias(\Widgets\WidgetBase::class, 'WidgetBase', true);
