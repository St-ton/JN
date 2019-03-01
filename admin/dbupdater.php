<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTL\Update\MigrationManager;
use JTL\Shop;
use JTL\Template;
use JTL\Update\Updater;
use JTLShop\SemVer\Version;

/**
 * @global \JTL\Smarty\JTLSmarty     $smarty
 * @global \JTL\Backend\AdminAccount $oAccount
 */

require_once __DIR__ . '/includes/admininclude.php';
$oAccount->permission('SHOP_UPDATE_VIEW', true, true);

$updater  = new Updater();
$template = Template::getInstance();
$feSmarty = new \JTL\Smarty\JTLSmarty(true, \JTL\Smarty\ContextType::FRONTEND);
$feSmarty->clearCompiledTemplate();
$smarty->clearCompiledTemplate();
Shop::Container()->getCache()->flushAll();

$fileVersion      = $updater->getCurrentFileVersion();
$dbVersion        = $updater->getCurrentDatabaseVersion();
$version          = $updater->getVersion();
$updatesAvailable = $updater->hasPendingUpdates();
$updateError      = $updater->error();

if (defined('ADMIN_MIGRATION') && ADMIN_MIGRATION) {
    $smarty->assign('manager', new MigrationManager());
}

$smarty->assign('updatesAvailable', $updatesAvailable)
       ->assign('currentFileVersion', $fileVersion)
       ->assign('currentDatabaseVersion', $dbVersion)
       ->assign('hasDifferentVersions', !Version::parse($fileVersion)->equals(Version::parse($fileVersion)))
       ->assign('version', $version)
       ->assign('updateError', $updateError)
       ->assign('currentTemplateFileVersion', $template->xmlData->cVersion)
       ->assign('currentTemplateDatabaseVersion', $template->version)
       ->display('dbupdater.tpl');
