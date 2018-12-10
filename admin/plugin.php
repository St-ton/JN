<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('PLUGIN_ADMIN_VIEW', true, true);
/** @global Smarty\JTLSmarty $smarty */
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'plugin_inc.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'toolsajax_inc.php';

$cHinweis        = '';
$cFehler         = '';
$step            = 'plugin_uebersicht';
$invalidateCache = false;
$bError          = false;
$updated         = false;
$kPlugin         = RequestHelper::verifyGPCDataInt('kPlugin');
$db              = Shop::Container()->getDB();
$cache           = Shop::Container()->getCache();
$oPlugin         = null;
if ($step === 'plugin_uebersicht' && $kPlugin > 0) {
    if (RequestHelper::verifyGPCDataInt('Setting') === 1) {
        $updated = true;
        if (!FormHelper::validateToken()) {
            $bError = true;
        } else {
            $plgnConf = isset($_POST['kPluginAdminMenu'])
                ? $db->queryPrepared(
                    "SELECT *
                        FROM tplugineinstellungenconf
                        WHERE kPluginAdminMenu != 0
                            AND kPlugin = :plgn
                            AND cConf != 'N'
                            AND kPluginAdminMenu = :kpm",
                    ['plgn' => $kPlugin, 'kpm' => (int)$_POST['kPluginAdminMenu']],
                    \DB\ReturnType::ARRAY_OF_OBJECTS
                )
                : [];
            foreach ($plgnConf as $current) {
                $db->delete(
                    'tplugineinstellungen',
                    ['kPlugin', 'cName'],
                    [$kPlugin, $current->cWertName]
                );
                $upd          = new stdClass();
                $upd->kPlugin = $kPlugin;
                $upd->cName   = $current->cWertName;
                if (isset($_POST[$current->cWertName])) {
                    if (is_array($_POST[$current->cWertName])) {
                        if ($current->cConf === \Plugin\ExtensionData\Config::TYPE_DYNAMIC) {
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
                    $bError = true;
                }
                $invalidateCache = true;
            }
        }
        if ($bError) {
            $cFehler = 'Fehler: Ihre Einstellungen konnten nicht gespeichert werden.';
        } else {
            $cHinweis = 'Ihre Einstellungen wurden erfolgreich gespeichert';
        }
    }
    if (RequestHelper::verifyGPCDataInt('kPluginAdminMenu') > 0) {
        $smarty->assign('defaultTabbertab', RequestHelper::verifyGPCDataInt('kPluginAdminMenu'));
    }
    if (strlen(RequestHelper::verifyGPDataString('cPluginTab')) > 0) {
        $smarty->assign('defaultTabbertab', RequestHelper::verifyGPDataString('cPluginTab'));
    }
    $data = $db->select('tplugin', 'kPlugin', $kPlugin);
    if ($data !== null) {
        $loader  = \Plugin\Helper::getLoader((int)$data->bExtension === 1, $db, $cache);
        $oPlugin = $loader->init($kPlugin, $invalidateCache);
    }
    if ($oPlugin !== null) {
        $smarty->assign('oPlugin', $oPlugin);
        if ($updated === true) {
            executeHook(HOOK_PLUGIN_SAVE_OPTIONS, [
                'plugin'   => $oPlugin,
                'hasError' => &$bError,
                'msg'      => &$cHinweis,
                'error'    => $cFehler,
                'options'  => $oPlugin->getConfig()->getOptions()
            ]);
        }
        foreach ($oPlugin->getAdminMenu()->getItems() as $menu) {
            if ($menu->isMarkdown === true) {
                $parseDown  = new Parsedown();
                $content    = $parseDown->text(StringHandler::convertUTF8(file_get_contents($menu->file)));
                $menu->html = $smarty->assign('content', $content)->fetch($menu->tpl);
            } elseif ($menu->configurable === false
                && $menu->file !== ''
                && file_exists($oPlugin->getPaths()->getAdminPath() . $menu->file)
            ) {
                ob_start();
                require $oPlugin->getPaths()->getAdminPath() . $menu->file;
                $menu->html = ob_get_contents();
                ob_end_clean();
            } elseif ($menu->configurable === true) {
                $smarty->assign('oPluginAdminMenu', $menu);
                $menu->html = $smarty->fetch('tpl_inc/plugin_options.tpl');
            }
        }
    }
}
$smarty->assign('oPlugin', $oPlugin)
       ->assign('hinweis', $cHinweis)
       ->assign('fehler', $cFehler)
       ->assign('step', $step)
       ->display('plugin.tpl');
