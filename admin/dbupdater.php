<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTLShop\SemVer\Compare;
use JTLShop\SemVer\Parser;

/**
 * @global JTLSmarty    $smarty
 * @global AdminAccount $oAccount
 */

require_once __DIR__ . '/includes/admininclude.php';
$oAccount->permission('SHOP_UPDATE_VIEW', true, true);

$updater  = new Updater();
$template = Template::getInstance();
$_smarty  = new JTLSmarty(true, true);
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
       ->assign('hasDifferentVersions', !Compare::equals(Parser::parse($currentFileVersion), Parser::parse($currentFileVersion)))
       ->assign('version', $version)
       ->assign('updateError', $updateError)
       ->assign('currentTemplateFileVersion', $template->xmlData->cVersion)
       ->assign('currentTemplateDatabaseVersion', $template->version)
       ->display('dbupdater.tpl');
