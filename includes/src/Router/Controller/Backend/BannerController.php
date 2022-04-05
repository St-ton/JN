<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use DateTime;
use Exception;
use JTL\Boxes\Admin\BoxAdmin;
use JTL\Customer\CustomerGroup;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\ImageMap;
use JTL\IO\IOResponse;
use JTL\Media\Image;
use JTL\Pagination\Pagination;
use JTL\Smarty\JTLSmarty;
use League\Route\Route;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Shop;
use stdClass;

/**
 * Class SelectionWizardController
 * @package JTL\Router\Controller\Backend
 */
class BannerController extends AbstractBackendController
{
    public function getResponse(
        ServerRequestInterface $request,
        array $args,
        JTLSmarty $smarty,
        Route $route
    ): ResponseInterface {
        $this->getText->loadAdminLocale('pages/banner');
        $this->smarty = $smarty;
        $this->checkPermissions('DISPLAY_BANNER_VIEW');
        $action   = (isset($_REQUEST['action']) && Form::validateToken()) ? $_REQUEST['action'] : 'view';
        $postData = Text::filterXSS($_POST);
        if ((isset($postData['cName']) || isset($postData['kImageMap'])) && Form::validateToken()) {
            $checks     = [];
            $imageMap   = new ImageMap($this->db);
            $imageMapID = Request::postInt('kImageMap', null);
            $name       = \htmlspecialchars($postData['cName'], \ENT_COMPAT | \ENT_HTML401, \JTL_CHARSET);
            if (mb_strlen($name) === 0) {
                $checks['cName'] = 1;
            }
            $bannerPath = Request::postVar('cPath', '') !== '' ? $postData['cPath'] : null;
            if (isset($_FILES['oFile'])
                && Image::isImageUpload($_FILES['oFile'])
                && \move_uploaded_file($_FILES['oFile']['tmp_name'], PFAD_ROOT . \PFAD_BILDER_BANNER . $_FILES['oFile']['name'])
            ) {
                $bannerPath = $_FILES['oFile']['name'];
            }
            if ($bannerPath === null) {
                $checks['oFile'] = $_FILES['oFile']['error'];
            }
            $dateFrom  = null;
            $dateUntil = null;
            if (Request::postVar('vDatum') !== '') {
                try {
                    $dateFrom = new DateTime($postData['vDatum']);
                    $dateFrom = $dateFrom->format('Y-m-d H:i:s');
                } catch (Exception $e) {
                    $checks['vDatum'] = 1;
                }
            }
            if (Request::postVar('bDatum') !== '') {
                try {
                    $dateUntil = new DateTime($postData['bDatum']);
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
                $key             = $postData['cKey'];
                $keyValue        = '';
                $value           = '';
                if ($pageType === \PAGE_ARTIKEL) {
                    $key      = 'kArtikel';
                    $keyValue = 'article_key';
                    $value    = $postData[$keyValue] ?? null;
                } elseif ($pageType === \PAGE_ARTIKELLISTE) {
                    $filters  = [
                        'kMerkmalWert' => 'attribute_key',
                        'kKategorie'   => 'categories_key',
                        'kHersteller'  => 'manufacturer_key',
                        'cSuche'       => 'keycSuche'
                    ];
                    $keyValue = $filters[$key];
                    $value    = $postData[$keyValue] ?? null;
                } elseif ($pageType === \PAGE_EIGENE) {
                    $key      = 'kLink';
                    $keyValue = 'link_key';
                    $value    = $postData[$keyValue] ?? null;
                }

                if (!empty($keyValue) && empty($value)) {
                    $this->alertService->addError(\sprintf(\__('errorKeyMissing'), $key), 'errorKeyMissing');
                } else {
                    $this->db->delete('textensionpoint', ['cClass', 'kInitial'], ['ImageMap', $imageMapID]);
                    $ext                = new stdClass();
                    $ext->kSprache      = $languageID;
                    $ext->kKundengruppe = $customerGroupID;
                    $ext->nSeite        = $pageType;
                    $ext->cKey          = $key;
                    $ext->cValue        = $value;
                    $ext->cClass        = 'ImageMap';
                    $ext->kInitial      = $imageMapID;

                    $ins = $this->db->insert('textensionpoint', $ext);
                    $this->cache->flushTags([\CACHING_GROUP_CORE]);
                    if ($imageMapID && $ins > 0) {
                        $action = 'view';
                        $this->alertService->addSuccess(\__('successSave'), 'successSave');
                    } else {
                        $this->alertService->addError(\__('errorSave'), 'errorSave');
                    }
                }
            } else {
                $this->alertService->addError(\__('errorFillRequired'), 'errorFillRequired');

                if (($checks['vDatum'] ?? 0) === 1) {
                    $this->alertService->addError(\__('errorDate'), 'errorDate');
                }
                if (($checks['bDatum'] ?? 0) === 1) {
                    $this->alertService->addError(\__('errorDate'), 'errorDate');
                } elseif (($checks['bDatum'] ?? 0) === 2) {
                    $this->alertService->addError(\__('errorDateActiveToGreater'), 'errorDateActiveToGreater');
                }
                if (($checks['oFile'] ?? 0) === 1) {
                    $this->alertService->addError(\__('errorImageSizeTooLarge'), 'errorImageSizeTooLarge');
                }

                $smarty->assign('cName', $postData['cName'] ?? null)
                    ->assign('vDatum', $postData['vDatum'] ?? null)
                    ->assign('bDatum', $postData['bDatum'] ?? null)
                    ->assign('kSprache', $postData['kSprache'] ?? null)
                    ->assign('kKundengruppe', $postData['kKundengruppe'] ?? null)
                    ->assign('nSeitenTyp', $postData['nSeitenTyp'] ?? null)
                    ->assign('cKey', $postData['cKey'] ?? null)
                    ->assign('categories_key', $postData['categories_key'] ?? null)
                    ->assign('attribute_key', $postData['attribute_key'] ?? null)
                    ->assign('tag_key', $postData['tag_key'] ?? null)
                    ->assign('manufacturer_key', $postData['manufacturer_key'] ?? null)
                    ->assign('keycSuche', $postData['keycSuche'] ?? null);
            }
        }
        switch ($action) {
            case 'area':
                $imageMap = $this->holeBanner(Request::postInt('id'), false);
                if (!\is_object($imageMap)) {
                    $this->alertService->addError(\__('errrorBannerNotFound'), 'errrorBannerNotFound');
                    $action = 'view';
                    break;
                }

                $smarty->assign('banner', $imageMap);
                break;

            case 'edit':
                $id       = (int)($postData['id'] ?? $postData['kImageMap']);
                $imageMap = $this->holeBanner($id);

                $smarty->assign('oExtension', $this->holeExtension($id))
                    ->assign('bannerFiles', $this->holeBannerDateien())
                    ->assign('customerGroups', CustomerGroup::getGroups())
                    ->assign('nMaxFileSize', \getMaxFileSize(\ini_get('upload_max_filesize')))
                    ->assign('banner', $imageMap);

                if (!\is_object($imageMap)) {
                    $this->alertService->addError(\__('errrorBannerNotFound'), 'errrorBannerNotFound');
                    $action = 'view';
                }
                break;

            case 'new':
                $smarty->assign('banner', $imageMap ?? null)
                    ->assign('customerGroups', CustomerGroup::getGroups())
                    ->assign('nMaxFileSize', \getMaxFileSize(\ini_get('upload_max_filesize')))
                    ->assign('bannerFiles', $this->holeBannerDateien());
                break;

            case 'delete':
                if ($this->entferneBanner(Request::postInt('id'))) {
                    $this->cache->flushTags([\CACHING_GROUP_CORE]);
                    $this->alertService->addSuccess(\__('successDeleted'), 'successDeleted');
                } else {
                    $this->alertService->addError(\__('errorDeleted'), 'errorDeleted');
                }
                break;

            default:
                break;
        }
        $pagination = (new Pagination('banners'))
            ->setRange(4)
            ->setItemArray($this->holeAlleBanner())
            ->assemble();

        return $smarty->assign('action', $action)
            ->assign('validPageTypes', (new BoxAdmin($this->db))->getMappedValidPageTypes())
            ->assign('pagination', $pagination)
            ->assign('route', $route->getPath())
            ->assign('banners', $pagination->getPageItems())
            ->getResponse('banner.tpl');
    }

    /**
     * @return stdClass[]
     * @former holeAlleBanner()
     */
    private function holeAlleBanner(): array
    {
        return (new ImageMap($this->db))->fetchAll();
    }

    /**
     * @param int  $imageMapID
     * @param bool $fill
     * @return bool|stdClass
     * @former holeBanner()
     */
    private function holeBanner(int $imageMapID, bool $fill = true)
    {
        return (new ImageMap($this->db))->fetch($imageMapID, true, $fill);
    }

    /**
     * @param int $imageMapID
     * @return mixed
     * @former holeExtension()
     */
    private function holeExtension(int $imageMapID)
    {
        return $this->db->select('textensionpoint', 'cClass', 'ImageMap', 'kInitial', $imageMapID);
    }

    /**
     * @param int $imageMapID
     * @return bool
     * @former entferneBanner()
     */
    private function entferneBanner(int $imageMapID): bool
    {
        $banner = new ImageMap($this->db);
        $this->db->delete('textensionpoint', ['cClass', 'kInitial'], ['ImageMap', $imageMapID]);

        return $banner->delete($imageMapID);
    }

    /**
     * @return string[]
     * @former holeBannerDateien()
     */
    private function holeBannerDateien(): array
    {
        $files = [];
        if (($handle = \opendir(PFAD_ROOT . \PFAD_BILDER_BANNER)) !== false) {
            while (($file = \readdir($handle)) !== false) {
                if ($file !== '.' && $file !== '..' && $file[0] !== '.') {
                    $files[] = $file;
                }
            }
            \closedir($handle);
        }

        return $files;
    }

    /**
     * @param mixed $data
     * @return IOResponse
     * @former saveBannerAreasIO()
     */
    public static function saveBannerAreasIO($data): IOResponse
    {
        $banner   = new ImageMap(Shop::Container()->getDB());
        $response = new IOResponse();
        $data     = \json_decode($data);
        foreach ($data->oArea_arr as $area) {
            $area->kArtikel      = (int)$area->kArtikel;
            $area->kImageMap     = (int)$area->kImageMap;
            $area->kImageMapArea = (int)$area->kImageMapArea;
        }
        $banner->saveAreas($data);

        return $response;
    }
}
