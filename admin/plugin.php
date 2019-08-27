<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTL\Alert\Alert;
use JTL\DB\ReturnType;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Plugin\Admin\Installation\MigrationManager;
use JTL\Plugin\Data\Config;
use JTL\Plugin\Helper;
use JTL\Plugin\Plugin;
use JTL\Shop;

require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('PLUGIN_ADMIN_VIEW', true, true);
/** @global \JTL\Smarty\JTLSmarty $smarty */
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'plugin_inc.php';

$notice          = '';
$errorMsg        = '';
$step            = 'plugin_uebersicht';
$invalidateCache = false;
$hasError        = false;
$updated         = false;
$pluginID        = Request::verifyGPCDataInt('kPlugin');
$db              = Shop::Container()->getDB();
$cache           = Shop::Container()->getCache();
$plugin          = null;
$alertHelper     = Shop::Container()->getAlertService();
if ($step === 'plugin_uebersicht' && $pluginID > 0) {
    if (Request::verifyGPCDataInt('Setting') === 1) {
        $updated = true;
        if (!Form::validateToken()) {
            $hasError = true;
        } else {
            $plgnConf = isset($_POST['kPluginAdminMenu'])
                ? $db->queryPrepared(
                    "SELECT *
                        FROM tplugineinstellungenconf
                        WHERE kPluginAdminMenu != 0
                            AND kPlugin = :plgn
                            AND cConf != 'N'
                            AND kPluginAdminMenu = :kpm",
                    ['plgn' => $pluginID, 'kpm' => Request::postInt('kPluginAdminMenu')],
                    ReturnType::ARRAY_OF_OBJECTS
                )
                : [];
            foreach ($plgnConf as $current) {
                $db->delete(
                    'tplugineinstellungen',
                    ['kPlugin', 'cName'],
                    [$pluginID, $current->cWertName]
                );
                $upd          = new stdClass();
                $upd->kPlugin = $pluginID;
                $upd->cName   = $current->cWertName;
                if (isset($_POST[$current->cWertName])) {
                    if (is_array($_POST[$current->cWertName])) {
                        if ($current->cConf === Config::TYPE_DYNAMIC) {
                            // selectbox with "multiple" attribute
                            $upd->cWert = serialize($_POST[$current->cWertName]);
                        } else {
                            // radio buttons
                            $upd->cWert = $_POST[$current->cWertName][0];
                        }
                    } else {
                        // textarea/text
                        $upd->cWert = $_POST[$current->cWertName];
                    }
                } else {
                    // checkboxes that are not checked
                    $upd->cWert = null;
                }
                if (!$db->insert('tplugineinstellungen', $upd)) {
                    $hasError = true;
                }
                $invalidateCache = true;
            }
        }
        if ($hasError) {
            $errorMsg = __('errorConfigSave');
        } else {
            $notice = __('successConfigSave');
        }
    }
    if (Request::verifyGPCDataInt('kPluginAdminMenu') > 0) {
        $smarty->assign('defaultTabbertab', Request::verifyGPCDataInt('kPluginAdminMenu'));
    }
    if (mb_strlen(Request::verifyGPDataString('cPluginTab')) > 0) {
        $smarty->assign('defaultTabbertab', Request::verifyGPDataString('cPluginTab'));
    }
    $data = $db->select('tplugin', 'kPlugin', $pluginID);
    if ($data !== null) {
        $loader = Helper::getLoader((int)$data->bExtension === 1, $db, $cache);
        $plugin = $loader->init($pluginID, $invalidateCache);
    }
    if ($plugin !== null) {
        $oPlugin = $plugin;
        if (ADMIN_MIGRATION && $plugin instanceof Plugin) {
            Shop::Container()->getGetText()->loadAdminLocale('pages/dbupdater');
            $manager    = new MigrationManager(
                $db,
                $plugin->getPaths()->getBasePath() . PFAD_PLUGIN_MIGRATIONS,
                $plugin->getPluginID(),
                $plugin->getMeta()->getSemVer()
            );
            $migrations = count($manager->getMigrations());
            $smarty->assign('manager', $manager)
                   ->assign('updatesAvailable', $migrations > count($manager->getExecutedMigrations()));
        }
        $smarty->assign('oPlugin', $plugin);
        if ($updated === true) {
            executeHook(HOOK_PLUGIN_SAVE_OPTIONS, [
                'plugin'   => $plugin,
                'hasError' => &$hasError,
                'msg'      => &$notice,
                'error'    => $errorMsg,
                'options'  => $plugin->getConfig()->getOptions()
            ]);
        }
        foreach ($plugin->getAdminMenu()->getItems() as $menu) {
            if ($menu->isMarkdown === true) {
                $parseDown  = new Parsedown();
                $content    = $parseDown->text(Text::convertUTF8(file_get_contents($menu->file)));
                $menu->html = $smarty->assign('content', $content)->fetch($menu->tpl);
            } elseif ($menu->configurable === false
                && $menu->file !== ''
                && file_exists($plugin->getPaths()->getAdminPath() . $menu->file)
            ) {
                ob_start();
                require $plugin->getPaths()->getAdminPath() . $menu->file;
                $menu->html = ob_get_contents();
                ob_end_clean();
            } elseif ($menu->configurable === true) {
                $smarty->assign('oPluginAdminMenu', $menu);
                $menu->html = $smarty->fetch('tpl_inc/plugin_options.tpl');
            }
        }
    }
}
$alertHelper->addAlert(Alert::TYPE_NOTE, $notice, 'pluginNotice');
$alertHelper->addAlert(Alert::TYPE_ERROR, $errorMsg, 'pluginError');

$smarty->assign('oPlugin', $plugin)
       ->assign('step', $step)
       ->assign('hasDifferentVersions', false)
       ->assign('currentDatabaseVersion', 0)
       ->assign('currentFileVersion', 0)
       ->display('plugin.tpl');
