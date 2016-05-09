<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once dirname(__FILE__) . '/includes/admininclude.php';

$oAccount->permission('SETTINGS_SITEMAP_VIEW', true, true);

$Einstellungen = Shop::getSettings(array(CONF_BILDER));
$shopSettings  = Shopsetting::getInstance();
$cHinweis      = '';
$cFehler       = '';
if (isset($_POST['speichern'])) {
    $cHinweis .= saveAdminSectionSettings(CONF_BILDER, $_POST);
    MediaImage::clearCache('product');
}

$oConfig_arr = Shop::DB()->query(
    "SELECT *
        FROM teinstellungenconf
        WHERE kEinstellungenSektion = " . CONF_BILDER . "
        ORDER BY nSort", 2
);
$configCount = count($oConfig_arr);
for ($i = 0; $i < $configCount; $i++) {
    if ($oConfig_arr[$i]->cInputTyp === 'selectbox') {
        $oConfig_arr[$i]->ConfWerte = Shop::DB()->query(
            "SELECT *
                FROM teinstellungenconfwerte
                WHERE kEinstellungenConf = " . (int)$oConfig_arr[$i]->kEinstellungenConf . "
                ORDER BY nSort", 2
        );
    }
    $oSetValue = Shop::DB()->query(
        "SELECT cWert
            FROM teinstellungen
            WHERE kEinstellungenSektion = " . CONF_BILDER . "
                AND cName = '" . $oConfig_arr[$i]->cWertName . "'", 1
    );
    $oConfig_arr[$i]->gesetzterWert = (isset($oSetValue->cWert)) ? $oSetValue->cWert : null;
}
Shop::Cache()->flushTags(array(CACHING_GROUP_OPTION));
$shopSettings->reset();
$Einstellungen = Shop::getSettings(array(CONF_BILDER));

$smarty->assign('oConfig_arr', $oConfig_arr)
       ->assign('oConfig', $Einstellungen['bilder'])
       ->assign('hinweis', $cHinweis)
       ->assign('fehler', $cFehler)
       ->display('bilder.tpl');
