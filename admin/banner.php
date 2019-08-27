<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTL\Alert\Alert;
use JTL\Boxes\Admin\BoxAdmin;
use JTL\Customer\CustomerGroup;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\ImageMap;
use JTL\Language\LanguageHelper;
use JTL\Pagination\Pagination;
use JTL\Shop;

require_once __DIR__ . '/includes/admininclude.php';
$oAccount->permission('DISPLAY_BANNER_VIEW', true, true);
/** @global \JTL\Smarty\JTLSmarty $smarty */
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'banner_inc.php';
$action      = (isset($_REQUEST['action']) && Form::validateToken()) ? $_REQUEST['action'] : 'view';
$alertHelper = Shop::Container()->getAlertService();
$db          = Shop::Container()->getDB();
if (!empty($_POST) && (isset($_POST['cName']) || isset($_POST['kImageMap'])) && Form::validateToken()) {
    $checks     = [];
    $imageMap   = new ImageMap($db);
    $imageMapID = Request::postInt('kImageMap', null);
    $name       = htmlspecialchars($_POST['cName'], ENT_COMPAT | ENT_HTML401, JTL_CHARSET);
    if (mb_strlen($name) === 0) {
        $checks['cName'] = 1;
    }
    $bannerPath = Request::postVar('cPath') !== '' ? $_POST['cPath'] : null;
    if (isset($_FILES['oFile'])
        && $_FILES['oFile']['error'] === UPLOAD_ERR_OK
        && move_uploaded_file($_FILES['oFile']['tmp_name'], PFAD_ROOT . PFAD_BILDER_BANNER . $_FILES['oFile']['name'])
    ) {
        $bannerPath = $_FILES['oFile']['name'];
    }
    if ($bannerPath === null) {
        $checks['oFile'] = 1;
    }
    $dateFrom  = null;
    $dateUntil = null;
    if (Request::postVar('vDatum') !== '') {
        try {
            $dateFrom = new DateTime($_POST['vDatum']);
            $dateFrom = $dateFrom->format('Y-m-d H:i:s');
        } catch (Exception $e) {
            $checks['vDatum'] = 1;
        }
    }
    if (Request::postVar('bDatum') !== '') {
        try {
            $dateUntil = new DateTime($_POST['bDatum']);
            $dateUntil = $dateUntil->format('Y-m-d H:i:s');
        } catch (Exception $e) {
            $checks['bDatum'] = 1;
        }
    }
    if ($dateUntil !== null && $dateUntil < $dateFrom) {
        $checks['bDatum'] = 2;
    }
    if (mb_strlen($bannerPath) === 0) {
        $checks['cBannerPath'] = 1;
    }
    if (count($checks) === 0) {
        if ($imageMapID === null || $imageMapID === 0) {
            $imageMapID = $imageMap->save($name, $bannerPath, $dateFrom, $dateUntil);
        } else {
            $imageMap->update($imageMapID, $name, $bannerPath, $dateFrom, $dateUntil);
        }
        // extensionpoint
        $languageID      = Request::postInt('kSprache');
        $customerGroupID = Request::postInt('kKundengruppe');
        $pageType        = Request::postInt('nSeitenTyp');
        $key             = $_POST['cKey'];
        $keyValue        = '';
        $value           = '';
        if ($pageType === PAGE_ARTIKEL) {
            $key      = 'kArtikel';
            $keyValue = 'article_key';
            $value    = $_POST[$keyValue] ?? null;
        } elseif ($pageType === PAGE_ARTIKELLISTE) {
            $filters  = [
                'kMerkmalWert' => 'attribute_key',
                'kKategorie'   => 'categories_key',
                'kHersteller'  => 'manufacturer_key',
                'cSuche'       => 'keycSuche'
            ];
            $keyValue = $filters[$key];
            $value    = $_POST[$keyValue] ?? null;
        } elseif ($pageType === PAGE_EIGENE) {
            $key      = 'kLink';
            $keyValue = 'link_key';
            $value    = $_POST[$keyValue] ?? null;
        }

        if (!empty($keyValue) && empty($value)) {
            $alertHelper->addAlert(
                Alert::TYPE_ERROR,
                sprintf(__('errorKeyMissing'), $key),
                'errorKeyMissing'
            );
        } else {
            $db->delete('textensionpoint', ['cClass', 'kInitial'], ['ImageMap', $imageMapID]);
            $ext                = new stdClass();
            $ext->kSprache      = $languageID;
            $ext->kKundengruppe = $customerGroupID;
            $ext->nSeite        = $pageType;
            $ext->cKey          = $key;
            $ext->cValue        = $value;
            $ext->cClass        = 'ImageMap';
            $ext->kInitial      = $imageMapID;

            $ins = $db->insert('textensionpoint', $ext);

            if ($imageMapID && $ins > 0) {
                $action = 'view';
                $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successSave'), 'successSave');
            } else {
                $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorSave'), 'errorSave');
            }
        }
    } else {
        $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorFillRequired'), 'errorFillRequired');

        if (($checks['vDatum'] ?? 0) === 1) {
            $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorDate'), 'errorDate');
        }
        if (($checks['bDatum'] ?? 0) === 1) {
            $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorDate'), 'errorDate');
        } elseif (($checks['bDatum'] ?? 0) === 2) {
            $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorDateActiveToGreater'), 'errorDateActiveToGreater');
        }
        if (($checks['oFile'] ?? 0) === 1) {
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
switch ($action) {
    case 'area':
        $imageMap = holeBanner(Request::postInt('id'), false);
        if (!is_object($imageMap)) {
            $alertHelper->addAlert(Alert::TYPE_ERROR, __('errrorBannerNotFound'), 'errrorBannerNotFound');
            $action = 'view';
            break;
        }

        $smarty->assign('oBanner', $imageMap)
            ->assign('cBannerLocation', Shop::getURL() . '/' . PFAD_BILDER_BANNER);
        break;

    case 'edit':
        $id       = (int)($_POST['id'] ?? $_POST['kImageMap']);
        $imageMap = holeBanner($id);

        $smarty->assign('oExtension', holeExtension($id))
            ->assign('cBannerFile_arr', holeBannerDateien())
            ->assign('oSprachen_arr', LanguageHelper::getInstance()->gibInstallierteSprachen())
            ->assign('oKundengruppe_arr', CustomerGroup::getGroups())
            ->assign('nMaxFileSize', getMaxFileSize(ini_get('upload_max_filesize')))
            ->assign('oBanner', $imageMap);

        if (!is_object($imageMap)) {
            $alertHelper->addAlert(Alert::TYPE_ERROR, __('errrorBannerNotFound'), 'errrorBannerNotFound');
            $action = 'view';
        }
        break;

    case 'new':
        $smarty->assign('oBanner', $imageMap ?? null)
            ->assign('oSprachen_arr', LanguageHelper::getInstance()->gibInstallierteSprachen())
            ->assign('oKundengruppe_arr', CustomerGroup::getGroups())
            ->assign('cBannerLocation', PFAD_BILDER_BANNER)
            ->assign('nMaxFileSize', getMaxFileSize(ini_get('upload_max_filesize')))
            ->assign('cBannerFile_arr', holeBannerDateien());
        break;

    case 'delete':
        if (entferneBanner(Request::postInt('id'))) {
            $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successDeleted'), 'successDeleted');
        } else {
            $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorDeleted'), 'errorDeleted');
        }
        break;

    default:
        break;
}
$pagination = (new Pagination('banners'))
    ->setRange(4)
    ->setItemArray(holeAlleBanner())
    ->assemble();

$smarty->assign('action', $action)
    ->assign('validPageTypes', (new BoxAdmin($db))->getMappedValidPageTypes())
    ->assign('pagination', $pagination)
    ->assign('oBanner_arr', $pagination->getPageItems())
    ->display('banner.tpl');
