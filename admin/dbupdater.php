<?php declare(strict_types=1);

use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use JTL\Update\MigrationManager;
use JTL\Update\Updater;
use JTLShop\SemVer\Version;

/**
 * @global JTLSmarty                 $smarty
 * @global \JTL\Backend\AdminAccount $oAccount
 */

require_once __DIR__ . '/includes/admininclude.php';
$oAccount->permission('SHOP_UPDATE_VIEW', true, true);

$smarty->clearCompiledTemplate();
$db          = Shop::Container()->getDB();
$updater     = new Updater($db);
$template    = Shop::Container()->getTemplateService()->getActiveTemplate();
$fileVersion = $updater->getCurrentFileVersion();

$smarty->assign('updatesAvailable', $updater->hasPendingUpdates())
    ->assign('manager', ADMIN_MIGRATION ? new MigrationManager($db) : null)
    ->assign('isPluginManager', false)
    ->assign('migrationURL', 'dbupdater.php')
    ->assign('currentFileVersion', $fileVersion)
    ->assign('currentDatabaseVersion', $updater->getCurrentDatabaseVersion())
    ->assign('hasDifferentVersions', !Version::parse($fileVersion)->equals(Version::parse($fileVersion)))
    ->assign('version', $updater->getVersion())
    ->assign('updateError', $updater->error())
    ->assign('currentTemplateFileVersion', $template->getFileVersion())
    ->assign('currentTemplateDatabaseVersion', $template->getVersion())
    ->display('dbupdater.tpl');
