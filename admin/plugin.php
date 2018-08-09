<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('PLUGIN_ADMIN_VIEW', true, true);
/** @global JTLSmarty $smarty */
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'plugin_inc.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'toolsajax_inc.php';

$cHinweis           = '';
$cFehler            = '';
$step               = 'plugin_uebersicht';
$customPluginTabs   = [];
$invalidateCache    = false;
$pluginTemplateFile = 'plugin.tpl';
$bError             = false;
$updated            = false;
if ($step === 'plugin_uebersicht') {
    $kPlugin = RequestHelper::verifyGPCDataInt('kPlugin');
    if ($kPlugin > 0) {
        // Ein Settinglink wurde submitted
        if (RequestHelper::verifyGPCDataInt('Setting') === 1) {
            $updated = true;
            if (!FormHelper::validateToken()) {
                $bError = true;
            } else {
                $oPluginEinstellungConf_arr = isset($_POST['kPluginAdminMenu'])
                    ? Shop::Container()->getDB()->queryPrepared(
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
                    Shop::Container()->getDB()->delete(
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
                    $kKey = Shop::Container()->getDB()->insert('tplugineinstellungen', $oPluginEinstellung);

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

        $oPlugin = new Plugin($kPlugin, $invalidateCache);
        if (!$invalidateCache) { //make sure dynamic options are reloaded
            foreach ($oPlugin->oPluginEinstellungConf_arr as $option) {
                if (!empty($option->cSourceFile)) {
                    $option->oPluginEinstellungenConfWerte_arr = $oPlugin->getDynamicOptions($option);
                }
            }
        }
        $smarty->assign('oPlugin', $oPlugin);
        if ($updated === true) {
            executeHook(HOOK_PLUGIN_SAVE_OPTIONS, [
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
        $fMarkDown          = false;

        if (class_exists('Parsedown')) {
            $fMarkDown  = true;
            $oParseDown = new Parsedown();
        }
        $smarty->assign('fMarkDown', $fMarkDown);

        if ('' !== $oPlugin->cTextReadmePath) {
            $szReadmeContent = StringHandler::convertUTF8(file_get_contents($oPlugin->cTextReadmePath));
            if (class_exists('Parsedown')) {
                $szReadmeContent = $oParseDown->text($szReadmeContent);
            }
            $smarty->assign('szReadmeContent', $szReadmeContent);

            $oUnnamedTab                     = new stdClass();
            $oUnnamedTab->kPluginAdminMenu   = count($oPlugin->oPluginAdminMenu_arr) + 1;
            $oUnnamedTab->kPlugin            = $oPlugin->kPlugin;
            $oUnnamedTab->cName              = 'Dokumentation';
            $oUnnamedTab->cDateiname         = '';
            $oUnnamedTab->nSort              = count($oPlugin->oPluginAdminMenu_arr) + 1;
            $oUnnamedTab->nConf              = 0;
            $oPlugin->oPluginAdminMenu_arr[] = $oUnnamedTab;

            $fAddAsDocTab = true;
        }
        if ('' !== $oPlugin->cTextLicensePath) {
            $szLicenseContent = StringHandler::convertUTF8(file_get_contents($oPlugin->cTextLicensePath));
            if (class_exists('Parsedown')) {
                $szLicenseContent = $oParseDown->text($szLicenseContent);
            }
            $smarty->assign('szLicenseContent', $szLicenseContent);

            $oUnnamedTab                     = new stdClass();
            $oUnnamedTab->kPluginAdminMenu   = count($oPlugin->oPluginAdminMenu_arr) + 1;
            $oUnnamedTab->kPlugin            = $oPlugin->kPlugin;
            $oUnnamedTab->cName              = 'Lizenzvereinbarung';
            $oUnnamedTab->cDateiname         = '';
            $oUnnamedTab->nSort              = count($oPlugin->oPluginAdminMenu_arr) + 1;
            $oUnnamedTab->nConf              = 0;
            $oPlugin->oPluginAdminMenu_arr[] = $oUnnamedTab;

            $fAddAsLicenseTab = true;
        }
        if ('' !== $oPlugin->changelogPath) {
            $szChangelogContent = StringHandler::convertUTF8(file_get_contents($oPlugin->changelogPath));
            if (class_exists('Parsedown')) {
                $szChangelogContent = $oParseDown->text($szChangelogContent);
            }
            $smarty->assign('szChangelogContent', $szChangelogContent);

            $oUnnamedTab                     = new stdClass();
            $oUnnamedTab->kPluginAdminMenu   = count($oPlugin->oPluginAdminMenu_arr) + 1;
            $oUnnamedTab->kPlugin            = $oPlugin->kPlugin;
            $oUnnamedTab->cName              = 'Changelog';
            $oUnnamedTab->cDateiname         = '';
            $oUnnamedTab->nSort              = count($oPlugin->oPluginAdminMenu_arr) + 1;
            $oUnnamedTab->nConf              = 0;
            $oPlugin->oPluginAdminMenu_arr[] = $oUnnamedTab;

            $fAddAsChangelogTab = true;
        }
        // build the tabs
        foreach ($oPlugin->oPluginAdminMenu_arr as $_adminMenu) {
            if ((int)$_adminMenu->nConf === 0 && $_adminMenu->cDateiname !== ''
                && file_exists($oPlugin->cAdminmenuPfad . $_adminMenu->cDateiname)
            ) {
                ob_start();
                require $oPlugin->cAdminmenuPfad . $_adminMenu->cDateiname;

                $tab                   = new stdClass();
                $tab->file             = $oPlugin->cAdminmenuPfad . $_adminMenu->cDateiname;
                $tab->idx              = $i;
                $tab->id               = str_replace('.php', '', $_adminMenu->cDateiname);
                $tab->kPluginAdminMenu = $_adminMenu->kPluginAdminMenu;
                $tab->cName            = $_adminMenu->cName;
                $tab->html             = ob_get_contents();
                $customPluginTabs[]    = $tab;
                ob_end_clean();
                ++$i;
            } elseif ((int)$_adminMenu->nConf === 1) {
                $smarty->assign('oPluginAdminMenu', $_adminMenu);
                $tab                   = new stdClass();
                $tab->file             = $oPlugin->cAdminmenuPfad . $_adminMenu->cDateiname;
                $tab->idx              = $i;
                $tab->id               = 'settings-' . $j;
                $tab->kPluginAdminMenu = $_adminMenu->kPluginAdminMenu;
                $tab->cName            = $_adminMenu->cName;
                $tab->html             = $smarty->fetch('tpl_inc/plugin_options.tpl');
                $customPluginTabs[]    = $tab;
                ++$j;
            } elseif (true === $fAddAsDocTab) {
                $tab                   = new stdClass();
                $tab->file             = '';
                $tab->idx              = $i;
                $tab->id               = 'addon-' . $j;
                $tab->kPluginAdminMenu = $_adminMenu->kPluginAdminMenu;
                $tab->cName            = $_adminMenu->cName;
                $tab->html             = $smarty->fetch('tpl_inc/plugin_documentation.tpl');
                $customPluginTabs[]    = $tab;
                ++$j;
                $fAddAsDocTab = false; // prevent another appending!
            } elseif (true === $fAddAsLicenseTab) {
                $tab                   = new stdClass();
                $tab->file             = '';
                $tab->idx              = $i;
                $tab->id               = 'addon-' . $j;
                $tab->kPluginAdminMenu = $_adminMenu->kPluginAdminMenu;
                $tab->cName            = $_adminMenu->cName;
                $tab->html             = $smarty->fetch('tpl_inc/plugin_license.tpl');
                $customPluginTabs[]    = $tab;
                ++$j;
                $fAddAsLicenseTab = false; // prevent another appending!
            } elseif (true === $fAddAsChangelogTab) {
                $tab                   = new stdClass();
                $tab->file             = '';
                $tab->idx              = $i;
                $tab->id               = 'addon-' . $j;
                $tab->kPluginAdminMenu = $_adminMenu->kPluginAdminMenu;
                $tab->cName            = $_adminMenu->cName;
                $tab->html             = $smarty->fetch('tpl_inc/plugin_changelog.tpl');
                $customPluginTabs[]    = $tab;
                ++$j;
                $fAddAsChangelogTab = false; // prevent another appending!
            }
        }
    }
}

$smarty->assign('customPluginTabs', $customPluginTabs)
       ->assign('hinweis', $cHinweis)
       ->assign('fehler', $cFehler)
       ->assign('step', $step)
       ->display($pluginTemplateFile);
