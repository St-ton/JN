<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once dirname(__FILE__) . '/includes/admininclude.php';

$oAccount->permission('SETTINGS_GLOBAL_META_VIEW', true, true);

$Einstellungen = Shop::getSettings(array(CONF_METAANGABEN));
$chinweis      = '';
$cfehler       = '';
setzeSprache();

if (isset($_POST['einstellungen']) && intval($_POST['einstellungen']) === 1 && validateToken()) {
    saveAdminSectionSettings(CONF_METAANGABEN, $_POST);

    $cTitle           = $_POST['Title'];
    $cMetaDesc        = $_POST['Meta_Description'];
    $cMetaKeys        = $_POST['Meta_Keywords'];
    $cMetaDescPraefix = $_POST['Meta_Description_Praefix'];
    Shop::DB()->delete('tglobalemetaangaben', array('kSprache', 'kEinstellungenSektion'), array((int)$_SESSION['kSprache'], CONF_METAANGABEN));
    // Title
    unset($oGlobaleMetaAngaben);
    $oGlobaleMetaAngaben                        = new stdClass();
    $oGlobaleMetaAngaben->kEinstellungenSektion = CONF_METAANGABEN;
    $oGlobaleMetaAngaben->kSprache              = (int)$_SESSION['kSprache'];
    $oGlobaleMetaAngaben->cName                 = 'Title';
    $oGlobaleMetaAngaben->cWertName             = $cTitle;
    Shop::DB()->insert('tglobalemetaangaben', $oGlobaleMetaAngaben);
    // Meta Description
    unset($oGlobaleMetaAngaben);
    $oGlobaleMetaAngaben                        = new stdClass();
    $oGlobaleMetaAngaben->kEinstellungenSektion = CONF_METAANGABEN;
    $oGlobaleMetaAngaben->kSprache              = (int)$_SESSION['kSprache'];
    $oGlobaleMetaAngaben->cName                 = 'Meta_Description';
    $oGlobaleMetaAngaben->cWertName             = $cMetaDesc;
    Shop::DB()->insert('tglobalemetaangaben', $oGlobaleMetaAngaben);
    // Meta Keywords
    unset($oGlobaleMetaAngaben);
    $oGlobaleMetaAngaben                        = new stdClass();
    $oGlobaleMetaAngaben->kEinstellungenSektion = CONF_METAANGABEN;
    $oGlobaleMetaAngaben->kSprache              = (int)$_SESSION['kSprache'];
    $oGlobaleMetaAngaben->cName                 = 'Meta_Keywords';
    $oGlobaleMetaAngaben->cWertName             = $cMetaKeys;
    Shop::DB()->insert('tglobalemetaangaben', $oGlobaleMetaAngaben);
    // Meta Description Präfix
    unset($oGlobaleMetaAngaben);
    $oGlobaleMetaAngaben                        = new stdClass();
    $oGlobaleMetaAngaben->kEinstellungenSektion = CONF_METAANGABEN;
    $oGlobaleMetaAngaben->kSprache              = (int)$_SESSION['kSprache'];
    $oGlobaleMetaAngaben->cName                 = 'Meta_Description_Praefix';
    $oGlobaleMetaAngaben->cWertName             = $cMetaDescPraefix;
    Shop::DB()->insert('tglobalemetaangaben', $oGlobaleMetaAngaben);
    Shop::Cache()->flushTags(array(CACHING_GROUP_OPTION));
    $chinweis .= 'Ihre Einstellungen wurden &uuml;bernommen.<br />';
    unset($oConfig_arr);
}

$oConfig_arr = Shop::DB()->query(
    "SELECT *
        FROM teinstellungenconf
        WHERE kEinstellungenSektion = " . CONF_METAANGABEN . "
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
            WHERE kEinstellungenSektion = " . CONF_METAANGABEN . "
                AND cName = '" . $oConfig_arr[$i]->cWertName . "'", 1
    );
    $oConfig_arr[$i]->gesetzterWert = (isset($oSetValue->cWert) ? $oSetValue->cWert : null);
}

$oMetaangaben_arr = Shop::DB()->query(
    "SELECT *
        FROM tglobalemetaangaben
        WHERE kSprache = " . (int)$_SESSION['kSprache'] . "
            AND kEinstellungenSektion = " . CONF_METAANGABEN, 2
);

if (is_array($oMetaangaben_arr) && count($oMetaangaben_arr) > 0) {
    $cTMP_arr = array();
    foreach ($oMetaangaben_arr as $oMetaangaben) {
        $cTMP_arr[$oMetaangaben->cName] = $oMetaangaben->cWertName;
    }
    $smarty->assign('oMetaangaben_arr', $cTMP_arr);
}

$smarty->assign('oConfig_arr', $oConfig_arr)
       ->assign('Sprachen', gibAlleSprachen())
       ->assign('hinweis', $chinweis)
       ->assign('fehler', $cfehler)
       ->display('globalemetaangaben.tpl');
