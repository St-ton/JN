<?php

use JTL\Alert\Alert;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Shop;

require_once __DIR__ . '/includes/admininclude.php';
/** @global \JTL\Backend\AdminAccount $oAccount */
/** @global \JTL\Smarty\JTLSmarty $smarty */

$oAccount->permission('SETTINGS_GLOBAL_META_VIEW', true, true);
$db = Shop::Container()->getDB();
setzeSprache();
if (Request::postInt('einstellungen') === 1 && Form::validateToken()) {
    saveAdminSectionSettings(CONF_METAANGABEN, $_POST);
    $title     = $_POST['Title'];
    $desc      = $_POST['Meta_Description'];
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
    $globalMetaData->cName                 = 'Meta_Description_Praefix';
    $globalMetaData->cWertName             = $metaDescr;
    $db->insert('tglobalemetaangaben', $globalMetaData);
    Shop::Container()->getCache()->flushAll();
    Shop::Container()->getAlertService()->addAlert(Alert::TYPE_SUCCESS, __('successConfigSave'), 'successConfigSave');
}

$meta     = $db->selectAll(
    'tglobalemetaangaben',
    ['kSprache', 'kEinstellungenSektion'],
    [(int)$_SESSION['kSprache'], CONF_METAANGABEN]
);
$metaData = [];
foreach ($meta as $item) {
    $metaData[$item->cName] = $item->cWertName;
}

$smarty->assign('oConfig_arr', getAdminSectionSettings(CONF_METAANGABEN))
    ->assign('oMetaangaben_arr', $metaData)
    ->display('globalemetaangaben.tpl');
