<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTL\Minify\MinifyService;
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

$db       = Shop::Container()->getDB();
$updater  = new Updater($db);
$template = Template::getInstance();
$feSmarty = new JTLSmarty(true, ContextType::FRONTEND);
$feSmarty->clearCompiledTemplate();
$smarty->clearCompiledTemplate();
Shop::Container()->getCache()->flushAll();
$ms = new MinifyService();
$ms->flushCache();

$fileVersion      = $updater->getCurrentFileVersion();
$dbVersion        = $updater->getCurrentDatabaseVersion();
$version          = $updater->getVersion();
$updatesAvailable = $updater->hasPendingUpdates();
$updateError      = $updater->error();

$smarty->assign('updatesAvailable', $updatesAvailable)
       ->assign('manager', ADMIN_MIGRATION ? new MigrationManager($db) : null)
       ->assign('isPluginManager', false)
       ->assign('migrationURL', 'dbupdater.php')
       ->assign('currentFileVersion', $fileVersion)
       ->assign('currentDatabaseVersion', $dbVersion)
       ->assign('hasDifferentVersions', !Version::parse($fileVersion)->equals(Version::parse($fileVersion)))
       ->assign('version', $version)
       ->assign('updateError', $updateError)
       ->assign('currentTemplateFileVersion', $template->xmlData->cVersion ?? '1.0.0')
       ->assign('currentTemplateDatabaseVersion', $template->version)
       ->display('dbupdater.tpl');
