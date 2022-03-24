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
$db                  = Shop::Container()->getDB();
$updater             = new Updater($db);
$template            = Shop::Container()->getTemplateService()->getActiveTemplate(false);
$fileVersion         = $updater->getCurrentFileVersion();
$hasMinUpdateVersion = true;
if (!$updater->hasMinUpdateVersion()) {
    Shop::Container()->getAlertService()->addWarning(
        $updater->getMinUpdateVersionError(),
        'errorMinShopVersionRequired'
    );
    $hasMinUpdateVersion = false;
}
if ((int)($_SESSION['disabledPlugins'] ?? 0) > 0) {
    Shop::Container()->getAlertService()->addWarning(
        sprintf(
            __('%d plugins were disabled for compatibility reasons. Please check your installed plugins manually.'),
            (int)$_SESSION['disabledPlugins']
        ),
        'errorMinShopVersionRequired'
    );
    unset($_SESSION['disabledPlugins']);
}
if (($_SESSION['maintenance_forced'] ?? false) === true) {
    $db->update('teinstellungen', 'cName', 'wartungsmodus_aktiviert', (object)['cWert' => 'N']);
    Shop::Container()->getCache()->flushTags([CACHING_GROUP_OPTION]);
}
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
    ->assign('hasMinUpdateVersion', $hasMinUpdateVersion)
    ->display('dbupdater.tpl');
