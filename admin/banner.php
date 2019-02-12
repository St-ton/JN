<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use Helpers\Form;

require_once __DIR__ . '/includes/admininclude.php';
$oAccount->permission('DISPLAY_BANNER_VIEW', true, true);
/** @global \Smarty\JTLSmarty $smarty */
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'banner_inc.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'toolsajax_inc.php';
$cAction     = (isset($_REQUEST['action']) && Form::validateToken()) ? $_REQUEST['action'] : 'view';
$alertHelper = Shop::Container()->getAlertService();
if (!empty($_POST) && (isset($_POST['cName']) || isset($_POST['kImageMap'])) && Form::validateToken()) {
    $cPlausi_arr = [];
    $oBanner     = new ImageMap();
    $kImageMap   = (isset($_POST['kImageMap']) ? (int)$_POST['kImageMap'] : null);
    $cName       = htmlspecialchars($_POST['cName'], ENT_COMPAT | ENT_HTML401, JTL_CHARSET);
    if (mb_strlen($cName) === 0) {
        $cPlausi_arr['cName'] = 1;
    }
    $cBannerPath = (isset($_POST['cPath']) && $_POST['cPath'] !== '' ? $_POST['cPath'] : null);
    if (isset($_FILES['oFile'])
        && $_FILES['oFile']['error'] === UPLOAD_ERR_OK
        && move_uploaded_file($_FILES['oFile']['tmp_name'], PFAD_ROOT . PFAD_BILDER_BANNER . $_FILES['oFile']['name'])
    ) {
        $cBannerPath = $_FILES['oFile']['name'];
    }
    if ($cBannerPath === null) {
        $cPlausi_arr['oFile'] = 1;
    }
    $vDatum = null;
    $bDatum = null;
    if (isset($_POST['vDatum']) && $_POST['vDatum'] !== '') {
        try {
            $vDatum = new DateTime($_POST['vDatum']);
            $vDatum = $vDatum->format('Y-m-d H:i:s');
        } catch (Exception $e) {
            $cPlausi_arr['vDatum'] = 1;
        }
    }
    if (isset($_POST['bDatum']) && $_POST['bDatum'] !== '') {
        try {
            $bDatum = new DateTime($_POST['bDatum']);
            $bDatum = $bDatum->format('Y-m-d H:i:s');
        } catch (Exception $e) {
            $cPlausi_arr['bDatum'] = 1;
        }
    }
    if ($bDatum !== null && $bDatum < $vDatum) {
        $cPlausi_arr['bDatum'] = 2;
    }
    if (mb_strlen($cBannerPath) === 0) {
        $cPlausi_arr['cBannerPath'] = 1;
    }
    if (count($cPlausi_arr) === 0) {
        if ($kImageMap === null || $kImageMap === 0) {
            $kImageMap = $oBanner->save($cName, $cBannerPath, $vDatum, $bDatum);
        } else {
            $oBanner->update($kImageMap, $cName, $cBannerPath, $vDatum, $bDatum);
        }
        // extensionpoint
        $kSprache      = (int)$_POST['kSprache'];
        $kKundengruppe = (int)$_POST['kKundengruppe'];
        $nSeite        = (int)$_POST['nSeitenTyp'];
        $cKey          = $_POST['cKey'];
        $cKeyValue     = '';
        $cValue        = '';

        if ($nSeite === PAGE_ARTIKEL) {
            $cKey      = 'kArtikel';
            $cKeyValue = 'article_key';
            $cValue    = $_POST[$cKeyValue] ?? null;
        } elseif ($nSeite === PAGE_ARTIKELLISTE) {
            $aFilter_arr = [
                'kTag'         => 'tag_key',
                'kMerkmalWert' => 'attribute_key',
                'kKategorie'   => 'categories_key',
                'kHersteller'  => 'manufacturer_key',
                'cSuche'       => 'keycSuche'
            ];
            $cKeyValue   = $aFilter_arr[$cKey];
            $cValue      = $_POST[$cKeyValue] ?? null;
        } elseif ($nSeite === PAGE_EIGENE) {
            $cKey      = 'kLink';
            $cKeyValue = 'link_key';
            $cValue    = $_POST[$cKeyValue] ?? null;
        }

        Shop::Container()->getDB()->delete('textensionpoint', ['cClass', 'kInitial'], ['ImageMap', $kImageMap]);
        $oExtension                = new stdClass();
        $oExtension->kSprache      = $kSprache;
        $oExtension->kKundengruppe = $kKundengruppe;
        $oExtension->nSeite        = $nSeite;
        $oExtension->cKey          = $cKey;
        $oExtension->cValue        = $cValue;
        $oExtension->cClass        = 'ImageMap';
        $oExtension->kInitial      = $kImageMap;

        $ins = Shop::Container()->getDB()->insert('textensionpoint', $oExtension);
        if ($kImageMap && $ins > 0) {
            $cAction = 'view';
            $alertHelper->addAlert(Alert::TYPE_NOTE, __('successSave'), 'successSave');
        } else {
            $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorSave'), 'errorSave');
        }
    } else {
        $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorFillRequired'), 'errorFillRequired');

        if (($cPlausi_arr['vDatum'] ?? 0) === 1) {
            $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorDate'), 'errorDate');
        }
        if (($cPlausi_arr['bDatum'] ?? 0) === 1) {
            $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorDate'), 'errorDate');
        } elseif (($cPlausi_arr['bDatum'] ?? 0) === 2) {
            $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorDateActiveToGreater'), 'errorDateActiveToGreater');
        }
        if (($cPlausi_arr['oFile'] ?? 0) === 1) {
            $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorImageSizeTooLarge'), 'errorImageSizeTooLarge');
        }

        $smarty->assign('cName', $_POST['cName'] ?? null)
               ->assign('vDatum', $_POST['vDatum'] ?? null)
               ->assign('bDatum', $_POST['bDatum'] ?? null)
               ->assign('kSprache', $_POST['kSprache'] ?? null)
               ->assign('kKundengruppe', $_POST['kKundengruppe'] ?? null)
               ->assign('nSeitenTyp', $_POST['nSeitenTyp'] ?? null)
               ->assign('cKey', $_POST['cKey'] ?? null)
               ->assign('categories_key', $_POST['categories_key'] ?? null)
               ->assign('attribute_key', $_POST['attribute_key'] ?? null)
               ->assign('tag_key', $_POST['tag_key'] ?? null)
               ->assign('manufacturer_key', $_POST['manufacturer_key'] ?? null)
               ->assign('keycSuche', $_POST['keycSuche'] ?? null);
    }
}
switch ($cAction) {
    case 'area':
        $oBanner = holeBanner((int)$_POST['id'], false);
        if (!is_object($oBanner)) {
            $alertHelper->addAlert(Alert::TYPE_ERROR, __('errrorBannerNotFound'), 'errrorBannerNotFound');
            $cAction = 'view';
            break;
        }

        $smarty->assign('oBanner', $oBanner)
               ->assign('cBannerLocation', Shop::getURL() . '/' . PFAD_BILDER_BANNER);
        break;

    case 'edit':
        $id      = (int)($_POST['id'] ?? $_POST['kImageMap']);
        $oBanner = holeBanner($id);

        $smarty->assign('oExtension', holeExtension($id))
               ->assign('cBannerFile_arr', holeBannerDateien())
               ->assign('oSprachen_arr', Sprache::getInstance()->gibInstallierteSprachen())
               ->assign('oKundengruppe_arr', Kundengruppe::getGroups())
               ->assign('nMaxFileSize', getMaxFileSize(ini_get('upload_max_filesize')))
               ->assign('oBanner', $oBanner);

        if (!is_object($oBanner)) {
            $alertHelper->addAlert(Alert::TYPE_ERROR, __('errrorBannerNotFound'), 'errrorBannerNotFound');
            $cAction = 'view';
        }
        break;

    case 'new':
        $smarty->assign('oBanner', $oBanner ?? null)
               ->assign('oSprachen_arr', Sprache::getInstance()->gibInstallierteSprachen())
               ->assign('oKundengruppe_arr', Kundengruppe::getGroups())
               ->assign('cBannerLocation', PFAD_BILDER_BANNER)
               ->assign('nMaxFileSize', getMaxFileSize(ini_get('upload_max_filesize')))
               ->assign('cBannerFile_arr', holeBannerDateien());
        break;

    case 'delete':
        if (entferneBanner((int)$_POST['id'])) {
            $alertHelper->addAlert(Alert::TYPE_NOTE, __('successDeleted'), 'successDeleted');
        } else {
            $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorDeleted'), 'errorDeleted');
        }
        break;

    default:
        break;
}

$smarty->assign('cAction', $cAction)
       ->assign('oBanner_arr', holeAlleBanner())
       ->display('banner.tpl');
