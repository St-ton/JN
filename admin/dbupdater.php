<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * @global JTLSmarty    $smarty
 * @global AdminAccount $oAccount
 */

require_once __DIR__ . '/includes/admininclude.php';
$oAccount->permission('SHOP_UPDATE_VIEW', true, true);

$updater  = new Updater();
$template = Template::getInstance();
$_smarty  = new \Smarty\JTLSmarty(true, true);
$_smarty->clearCompiledTemplate();
Shop::Cache()->flushAll();

$currentFileVersion     = $updater->getCurrentFileVersion();
$currentDatabaseVersion = $updater->getCurrentDatabaseVersion();
$version                = $updater->getVersion();
$updatesAvailable       = $updater->hasPendingUpdates();
$updateError            = $updater->error();

if (defined('ADMIN_MIGRATION') && ADMIN_MIGRATION) {
    $smarty->assign('manager', new MigrationManager());
}

$smarty->assign('updatesAvailable', $updatesAvailable)
       ->assign('currentFileVersion', $currentFileVersion)
       ->assign('currentDatabaseVersion', $currentDatabaseVersion)
       ->assign('version', $version)
       ->assign('updateError', $updateError)
       ->assign('currentTemplateFileVersion', $template->xmlData->cShopVersion)
       ->assign('currentTemplateDatabaseVersion', $template->shopVersion)
       ->display('dbupdater.tpl');
