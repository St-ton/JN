<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('SETTINGS_GLOBAL_META_VIEW', true, true);
/** @global Smarty\JTLSmarty $smarty */
$Einstellungen = Shop::getSettings([CONF_METAANGABEN]);
$chinweis      = '';
$cfehler       = '';
setzeSprache();
if (isset($_POST['einstellungen']) && (int)$_POST['einstellungen'] === 1 && FormHelper::validateToken()) {
    saveAdminSectionSettings(CONF_METAANGABEN, $_POST);

    $cTitle           = $_POST['Title'];
    $cMetaDesc        = $_POST['Meta_Description'];
    $cMetaKeys        = $_POST['Meta_Keywords'];
    $cMetaDescPraefix = $_POST['Meta_Description_Praefix'];
    Shop::Container()->getDB()->delete(
        'tglobalemetaangaben',
        ['kSprache', 'kEinstellungenSektion'],
        [(int)$_SESSION['kSprache'], CONF_METAANGABEN]
    );
    // Title
    unset($oGlobaleMetaAngaben);
    $oGlobaleMetaAngaben                        = new stdClass();
    $oGlobaleMetaAngaben->kEinstellungenSektion = CONF_METAANGABEN;
    $oGlobaleMetaAngaben->kSprache              = (int)$_SESSION['kSprache'];
    $oGlobaleMetaAngaben->cName                 = 'Title';
    $oGlobaleMetaAngaben->cWertName             = $cTitle;
    Shop::Container()->getDB()->insert('tglobalemetaangaben', $oGlobaleMetaAngaben);
    // Meta Description
    unset($oGlobaleMetaAngaben);
    $oGlobaleMetaAngaben                        = new stdClass();
    $oGlobaleMetaAngaben->kEinstellungenSektion = CONF_METAANGABEN;
    $oGlobaleMetaAngaben->kSprache              = (int)$_SESSION['kSprache'];
    $oGlobaleMetaAngaben->cName                 = 'Meta_Description';
    $oGlobaleMetaAngaben->cWertName             = $cMetaDesc;
    Shop::Container()->getDB()->insert('tglobalemetaangaben', $oGlobaleMetaAngaben);
    // Meta Keywords
    unset($oGlobaleMetaAngaben);
    $oGlobaleMetaAngaben                        = new stdClass();
    $oGlobaleMetaAngaben->kEinstellungenSektion = CONF_METAANGABEN;
    $oGlobaleMetaAngaben->kSprache              = (int)$_SESSION['kSprache'];
    $oGlobaleMetaAngaben->cName                 = 'Meta_Keywords';
    $oGlobaleMetaAngaben->cWertName             = $cMetaKeys;
    Shop::Container()->getDB()->insert('tglobalemetaangaben', $oGlobaleMetaAngaben);
    // Meta Description Präfix
    unset($oGlobaleMetaAngaben);
    $oGlobaleMetaAngaben                        = new stdClass();
    $oGlobaleMetaAngaben->kEinstellungenSektion = CONF_METAANGABEN;
    $oGlobaleMetaAngaben->kSprache              = (int)$_SESSION['kSprache'];
    $oGlobaleMetaAngaben->cName                 = 'Meta_Description_Praefix';
    $oGlobaleMetaAngaben->cWertName             = $cMetaDescPraefix;
    Shop::Container()->getDB()->insert('tglobalemetaangaben', $oGlobaleMetaAngaben);

    $keywords              = new stdClass();
    $keywords->cISOSprache = $_SESSION['cISOSprache'];
    $keywords->cKeywords   = $_POST['keywords'];
    Shop::Container()->getDB()->delete('texcludekeywords', 'cISOSprache', $keywords->cISOSprache);
    Shop::Container()->getDB()->insert('texcludekeywords', $keywords);
    Shop::Container()->getCache()->flushAll();
    $chinweis .= 'Ihre Einstellungen wurden übernommen.<br />';
    unset($oConfig_arr);
}

$oMetaangaben_arr = Shop::Container()->getDB()->selectAll(
    'tglobalemetaangaben',
    ['kSprache', 'kEinstellungenSektion'],
    [(int)$_SESSION['kSprache'], CONF_METAANGABEN]
);
$cTMP_arr         = [];
foreach ($oMetaangaben_arr as $oMetaangaben) {
    $cTMP_arr[$oMetaangaben->cName] = $oMetaangaben->cWertName;
}

$excludeKeywords = Shop::Container()->getDB()->select('texcludekeywords', 'cISOSprache', $_SESSION['cISOSprache']);

$smarty->assign('oConfig_arr', getAdminSectionSettings(CONF_METAANGABEN))
       ->assign('oMetaangaben_arr', $cTMP_arr)
       ->assign('keywords', $excludeKeywords)
       ->assign('Sprachen', Sprache::getAllLanguages())
       ->assign('hinweis', $chinweis)
       ->assign('fehler', $cfehler)
       ->display('globalemetaangaben.tpl');
