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
$customTabs      = [];
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
            $oPluginEinstellungConf_arr = isset($_POST['kPluginAdminMenu'])
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
            foreach ($oPluginEinstellungConf_arr as $oPluginEinstellungConf) {
                $db->delete(
                    'tplugineinstellungen',
                    ['kPlugin', 'cName'],
                    [$kPlugin, $oPluginEinstellungConf->cWertName]
                );
                $oPluginEinstellung          = new stdClass();
                $oPluginEinstellung->kPlugin = $kPlugin;
                $oPluginEinstellung->cName   = $oPluginEinstellungConf->cWertName;
                if (isset($_POST[$oPluginEinstellungConf->cWertName])) {
                    if (is_array($_POST[$oPluginEinstellungConf->cWertName])) {
                        if ($oPluginEinstellungConf->cConf === 'M') {
                            //selectbox with "multiple" attribute
                            $oPluginEinstellung->cWert = serialize($_POST[$oPluginEinstellungConf->cWertName]);
                        } else {
                            //radio buttons
                            $oPluginEinstellung->cWert = $_POST[$oPluginEinstellungConf->cWertName][0];
                        }
                    } else {
                        //textarea/text
                        $oPluginEinstellung->cWert = $_POST[$oPluginEinstellungConf->cWertName];
                    }
                } else {
                    //checkboxes that are not checked
                    $oPluginEinstellung->cWert = null;
                }
                $kKey = $db->insert('tplugineinstellungen', $oPluginEinstellung);

                if (!$kKey) {
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
    if ($data === null) {
        $cFehler = 'nix';
    } else {
        $loader  = \Plugin\Helper::getLoader((int)$data->bExtension === 1, $db, $cache);
        $oPlugin = $loader->init($kPlugin, $invalidateCache);
    }
    if ($oPlugin !== null) {
        $smarty->assign('oPlugin', $oPlugin);
//        if (!$invalidateCache) { //make sure dynamic options are reloaded
//            foreach ($oPlugin->oPluginEinstellungConf_arr as $option) {
//                if (!empty($option->cSourceFile)) {
//                    $option->oPluginEinstellungenConfWerte_arr = $oPlugin->getDynamicOptions($option);
//                }
//            }
//        }
        if ($updated === true) {
            executeHook(HOOK_PLUGIN_SAVE_OPTIONS, [
                'plugin'   => $oPlugin,
                'hasError' => &$bError,
                'msg'      => &$cHinweis,
                'error'    => $cFehler,
                'options'  => $oPlugin->oPluginEinstellungAssoc_arr
            ]);
        }
        $i                  = 0;
        $j                  = 0;
        $fAddAsDocTab       = false;
        $fAddAsLicenseTab   = false;
        $fAddAsChangelogTab = false;
        $oParseDown         = new Parsedown();
        $adminMenu          = $oPlugin->getAdminMenu();
        $readmeMD           = $oPlugin->getMeta()->getReadmeMD();
        $licenseMD          = $oPlugin->getMeta()->getLicenseMD();
        $changelogMD        = $oPlugin->getMeta()->getChangelogMD();
        if (!empty($readmeMD)) {
            $szReadmeContent = $oParseDown->text(StringHandler::convertUTF8(file_get_contents($readmeMD)));

            $smarty->assign('szReadmeContent', $szReadmeContent);

            $oUnnamedTab                   = new stdClass();
            $oUnnamedTab->kPluginAdminMenu = $adminMenu->getItems()->count() + 1;
            $oUnnamedTab->kPlugin          = $oPlugin->getID();
            $oUnnamedTab->cName            = 'Dokumentation';
            $oUnnamedTab->cDateiname       = '';
            $oUnnamedTab->nSort            = $oUnnamedTab->kPluginAdminMenu;
            $oUnnamedTab->nConf            = 0;
            $adminMenu->addItem($oUnnamedTab);

            $fAddAsDocTab = true;
        }
        if (!empty($licenseMD)) {
            $licenseContent = $oParseDown->text(StringHandler::convertUTF8(file_get_contents($licenseMD)));

            $smarty->assign('szLicenseContent', $licenseContent);

            $oUnnamedTab                   = new stdClass();
            $oUnnamedTab->kPluginAdminMenu = $adminMenu->getItems()->count() + 1;
            $oUnnamedTab->kPlugin          = $oPlugin->getID();
            $oUnnamedTab->cName            = 'Lizenzvereinbarung';
            $oUnnamedTab->cDateiname       = '';
            $oUnnamedTab->nSort            = $oUnnamedTab->kPluginAdminMenu;
            $oUnnamedTab->nConf            = 0;
            $adminMenu->addItem($oUnnamedTab);

            $fAddAsLicenseTab = true;
        }
        if (!empty($changelogMD)) {
            $changelogContent = $oParseDown->text(StringHandler::convertUTF8(file_get_contents($changelogMD)));

            $smarty->assign('szChangelogContent', $changelogContent);

            $oUnnamedTab                   = new stdClass();
            $oUnnamedTab->kPluginAdminMenu = $adminMenu->getItems()->count() + 1;
            $oUnnamedTab->kPlugin          = $oPlugin->getID();
            $oUnnamedTab->cName            = 'Changelog';
            $oUnnamedTab->cDateiname       = '';
            $oUnnamedTab->nSort            = $oUnnamedTab->kPluginAdminMenu;
            $oUnnamedTab->nConf            = 0;
            $adminMenu->addItem($oUnnamedTab);

            $fAddAsChangelogTab = true;
        }
        // build the tabs
        foreach ($adminMenu->getItems() as $_adminMenu) {
            if ((int)$_adminMenu->nConf === 0
                && $_adminMenu->cDateiname !== ''
                && file_exists($oPlugin->getPaths()->getAdminPath() . $_adminMenu->cDateiname)
            ) {
                ob_start();
                require $oPlugin->getPaths()->getAdminPath() . $_adminMenu->cDateiname;

                $tab                   = new stdClass();
                $tab->file             = $oPlugin->getPaths()->getAdminPath() . $_adminMenu->cDateiname;
                $tab->idx              = $i;
                $tab->id               = str_replace('.php', '', $_adminMenu->cDateiname);
                $tab->kPluginAdminMenu = $_adminMenu->kPluginAdminMenu;
                $tab->cName            = $_adminMenu->cName;
                $tab->html             = ob_get_contents();
                $customTabs[]          = $tab;
                ob_end_clean();
                ++$i;
            } elseif ((int)$_adminMenu->nConf === 1) {
                $smarty->assign('oPluginAdminMenu', $_adminMenu);
                $tab                   = new stdClass();
                $tab->file             = $oPlugin->getPaths()->getAdminPath() . $_adminMenu->cDateiname;
                $tab->idx              = $i;
                $tab->id               = 'settings-' . $j;
                $tab->kPluginAdminMenu = $_adminMenu->kPluginAdminMenu;
                $tab->cName            = $_adminMenu->cName;
                $tab->html             = $smarty->fetch('tpl_inc/plugin_options.tpl');
                $customTabs[]          = $tab;
                ++$j;
            } elseif ($fAddAsDocTab === true) {
                $tab                   = new stdClass();
                $tab->file             = '';
                $tab->idx              = $i;
                $tab->id               = 'addon-' . $j;
                $tab->kPluginAdminMenu = $_adminMenu->kPluginAdminMenu;
                $tab->cName            = $_adminMenu->cName;
                $tab->html             = $smarty->fetch('tpl_inc/plugin_documentation.tpl');
                $customTabs[]          = $tab;
                ++$j;
                $fAddAsDocTab = false; // prevent another appending!
            } elseif ($fAddAsLicenseTab === true) {
                $tab                   = new stdClass();
                $tab->file             = '';
                $tab->idx              = $i;
                $tab->id               = 'addon-' . $j;
                $tab->kPluginAdminMenu = $_adminMenu->kPluginAdminMenu;
                $tab->cName            = $_adminMenu->cName;
                $tab->html             = $smarty->fetch('tpl_inc/plugin_license.tpl');
                $customTabs[]          = $tab;
                ++$j;
                $fAddAsLicenseTab = false; // prevent another appending!
            } elseif ($fAddAsChangelogTab === true) {
                $tab                   = new stdClass();
                $tab->file             = '';
                $tab->idx              = $i;
                $tab->id               = 'addon-' . $j;
                $tab->kPluginAdminMenu = $_adminMenu->kPluginAdminMenu;
                $tab->cName            = $_adminMenu->cName;
                $tab->html             = $smarty->fetch('tpl_inc/plugin_changelog.tpl');
                $customTabs[]          = $tab;
                ++$j;
                $fAddAsChangelogTab = false; // prevent another appending!
            }
        }
    }
}

$smarty->assign('customPluginTabs', $customTabs)
       ->assign('oPlugin', $oPlugin)
       ->assign('hinweis', $cHinweis)
       ->assign('fehler', $cFehler)
       ->assign('step', $step)
       ->display('plugin.tpl');
