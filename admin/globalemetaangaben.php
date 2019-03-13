<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTL\Helpers\Form;
use JTL\Shop;
use JTL\Sprache;
use JTL\Alert\Alert;

require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('SETTINGS_GLOBAL_META_VIEW', true, true);
/** @global \JTL\Smarty\JTLSmarty $smarty */
$db = Shop::Container()->getDB();
setzeSprache();
if (isset($_POST['einstellungen']) && (int)$_POST['einstellungen'] === 1 && Form::validateToken()) {
    saveAdminSectionSettings(CONF_METAANGABEN, $_POST);
    $title     = $_POST['Title'];
    $desc      = $_POST['Meta_Description'];
    $metaKeys  = $_POST['Meta_Keywords'];
    $metaDescr = $_POST['Meta_Description_Praefix'];
    $db->delete(
        'tglobalemetaangaben',
        ['kSprache', 'kEinstellungenSektion'],
        [(int)$_SESSION['kSprache'], CONF_METAANGABEN]
    );
    $globalMetaData                        = new stdClass();
    $globalMetaData->kEinstellungenSektion = CONF_METAANGABEN;
    $globalMetaData->kSprache              = (int)$_SESSION['kSprache'];
    $globalMetaData->cName                 = 'Title';
    $globalMetaData->cWertName             = $title;
    $db->insert('tglobalemetaangaben', $globalMetaData);
    $globalMetaData                        = new stdClass();
    $globalMetaData->kEinstellungenSektion = CONF_METAANGABEN;
    $globalMetaData->kSprache              = (int)$_SESSION['kSprache'];
    $globalMetaData->cName                 = 'Meta_Description';
    $globalMetaData->cWertName             = $desc;
    $db->insert('tglobalemetaangaben', $globalMetaData);
    $globalMetaData                        = new stdClass();
    $globalMetaData->kEinstellungenSektion = CONF_METAANGABEN;
    $globalMetaData->kSprache              = (int)$_SESSION['kSprache'];
    $globalMetaData->cName                 = 'Meta_Keywords';
    $globalMetaData->cWertName             = $metaKeys;
    $db->insert('tglobalemetaangaben', $globalMetaData);
    $globalMetaData                        = new stdClass();
    $globalMetaData->kEinstellungenSektion = CONF_METAANGABEN;
    $globalMetaData->kSprache              = (int)$_SESSION['kSprache'];
    $globalMetaData->cName                 = 'Meta_Description_Praefix';
    $globalMetaData->cWertName             = $metaDescr;
    $db->insert('tglobalemetaangaben', $globalMetaData);
    $keywords              = new stdClass();
    $keywords->cISOSprache = $_SESSION['cISOSprache'];
    $keywords->cKeywords   = $_POST['keywords'];
    $db->delete('texcludekeywords', 'cISOSprache', $keywords->cISOSprache);
    $db->insert('texcludekeywords', $keywords);
    Shop::Container()->getCache()->flushAll();
    Shop::Container()->getAlertService()->addAlert(Alert::TYPE_SUCCESS, __('successConfigSave'), 'successConfigSave');
}

$excludeKeywords = $db->select('texcludekeywords', 'cISOSprache', $_SESSION['cISOSprache']);
$meta            = $db->selectAll(
    'tglobalemetaangaben',
    ['kSprache', 'kEinstellungenSektion'],
    [(int)$_SESSION['kSprache'], CONF_METAANGABEN]
);
$metaData        = [];
foreach ($meta as $item) {
    $metaData[$item->cName] = $item->cWertName;
}

$smarty->assign('oConfig_arr', getAdminSectionSettings(CONF_METAANGABEN))
       ->assign('oMetaangaben_arr', $metaData)
       ->assign('keywords', $excludeKeywords)
       ->assign('Sprachen', Sprache::getAllLanguages())
       ->display('globalemetaangaben.tpl');
