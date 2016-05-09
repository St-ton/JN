<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once dirname(__FILE__) . '/includes/admininclude.php';

$oAccount->permission('PLUGIN_ADMIN_VIEW', true, true);

require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'plugin_inc.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'toolsajax_inc.php';

$cHinweis           = '';
$cFehler            = '';
$step               = 'plugin_uebersicht';
$customPluginTabs   = array();
$invalidateCache    = false;
$pluginTemplateFile = 'plugin.tpl';
if ($step === 'plugin_uebersicht') {
    $kPlugin = verifyGPCDataInteger('kPlugin');
    if ($kPlugin > 0) {
        // Ein Settinglink wurde submitted
        if (verifyGPCDataInteger('Setting') === 1) {
            if (!validateToken()) {
                $bError = true;
            } else {
                $oPluginEinstellungConf_arr = Shop::DB()->query(
                    "SELECT *
                        FROM tplugineinstellungenconf
                        WHERE kPluginAdminMenu != 0
                            AND kPlugin = " . $kPlugin . "
                            AND cConf = 'Y'
                            AND kPluginAdminMenu = " . intval($_POST['kPluginAdminMenu']), 2
                );
                $bError = false;
                if (count($oPluginEinstellungConf_arr) > 0) {
                    foreach ($oPluginEinstellungConf_arr as $oPluginEinstellungConf) {
                        Shop::DB()->delete('tplugineinstellungen', array('kPlugin', 'cName'), array($kPlugin, $oPluginEinstellungConf->cWertName));
                        $oPluginEinstellung          = new stdClass();
                        $oPluginEinstellung->kPlugin = $kPlugin;
                        $oPluginEinstellung->cName   = $oPluginEinstellungConf->cWertName;
                        if (isset($_POST[$oPluginEinstellungConf->cWertName])) {
                            if (is_array($_POST[$oPluginEinstellungConf->cWertName])) {
                                //radio buttons
                                $oPluginEinstellung->cWert = $_POST[$oPluginEinstellungConf->cWertName][0];
                            } else {
                                //textarea/text
                                $oPluginEinstellung->cWert = $_POST[$oPluginEinstellungConf->cWertName];
                            }
                        } else {
                            //checkboxes that are not checked
                            $oPluginEinstellung->cWert = null;
                        }
                        $kKey = Shop::DB()->insert('tplugineinstellungen', $oPluginEinstellung);

                        if (!$kKey) {
                            $bError = true;
                        }
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
        if (verifyGPCDataInteger('kPluginAdminMenu') > 0) {
            $smarty->assign('defaultTabbertab', verifyGPCDataInteger('kPluginAdminMenu'));
        }
        if (strlen(verifyGPDataString('cPluginTab')) > 0) {
            $smarty->assign('defaultTabbertab', verifyGPDataString('cPluginTab'));
        }

        $oPlugin = new Plugin($kPlugin, $invalidateCache);
        $smarty->assign('oPlugin', $oPlugin);
        $i = 0;
        $j = 0;

        foreach ($oPlugin->oPluginAdminMenu_arr as $_adminMenu) {
            if ($_adminMenu->nConf === '0' && $_adminMenu->cDateiname !== '' && file_exists($oPlugin->cAdminmenuPfad . $_adminMenu->cDateiname)) {
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
            } elseif ($_adminMenu->nConf === '1') {
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
            }
        }
    }
}

$smarty->assign('customPluginTabs', $customPluginTabs)
       ->assign('hinweis', $cHinweis)
       ->assign('fehler', $cFehler)
       ->assign('step', $step)
       ->display($pluginTemplateFile);
