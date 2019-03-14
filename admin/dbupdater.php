<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTL\Shop;
use JTL\Smarty\ContextType;
use JTL\Smarty\JTLSmarty;
use JTL\Template;
use JTL\Update\MigrationManager;
use JTL\Update\Updater;
use JTLShop\SemVer\Version;

/**
 * @global JTLSmarty                 $smarty
 * @global \JTL\Backend\AdminAccount $oAccount
 */

require_once __DIR__ . '/includes/admininclude.php';
$oAccount->permission('SHOP_UPDATE_VIEW', true, true);

$updater  = new Updater();
$template = Template::getInstance();
$feSmarty = new JTLSmarty(true, ContextType::FRONTEND);
$feSmarty->clearCompiledTemplate();
$smarty->clearCompiledTemplate();
Shop::Container()->getCache()->flushAll();

$fileVersion      = $updater->getCurrentFileVersion();
$dbVersion        = $updater->getCurrentDatabaseVersion();
$version          = $updater->getVersion();
$updatesAvailable = $updater->hasPendingUpdates();
$updateError      = $updater->error();

$smarty->assign('updatesAvailable', $updatesAvailable)
       ->assign('manager', ADMIN_MIGRATION ? new MigrationManager() : null)
       ->assign('isPluginManager', false)
       ->assign('migrationURL', 'dbupdater.php')
       ->assign('currentFileVersion', $fileVersion)
       ->assign('currentDatabaseVersion', $dbVersion)
       ->assign('hasDifferentVersions', !Version::parse($fileVersion)->equals(Version::parse($fileVersion)))
       ->assign('version', $version)
       ->assign('updateError', $updateError)
       ->assign('currentTemplateFileVersion', $template->xmlData->cVersion)
       ->assign('currentTemplateDatabaseVersion', $template->version)
       ->display('dbupdater.tpl');
