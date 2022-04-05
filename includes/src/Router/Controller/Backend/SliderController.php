<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use JTL\Boxes\Admin\BoxAdmin;
use JTL\Customer\CustomerGroup;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Pagination\Pagination;
use JTL\Shop;
use JTL\Slide;
use JTL\Slider;
use JTL\Smarty\JTLSmarty;
use League\Route\Route;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use stdClass;

/**
 * Class SliderController
 * @package JTL\Router\Controller\Backend
 */
class SliderController extends AbstractBackendController
{
    public function getResponse(
        ServerRequestInterface $request,
        array $args,
        JTLSmarty $smarty,
        Route $route
    ): ResponseInterface {
        $this->smarty = $smarty;
        $this->checkPermissions('SLIDER_VIEW');
        $this->getText->loadAdminLocale('pages/slider');

        $_kSlider    = 0;
        $redirectUrl = Shop::getURL() . $route->getPath();
        $action      = isset($_REQUEST['action']) && Form::validateToken()
            ? $_REQUEST['action']
            : 'view';
        $kSlider     = (int)($_REQUEST['id'] ?? 0);
        switch ($action) {
            case 'slide_set':
                $filtered  = Text::filterXSS($_REQUEST);
                $aSlideKey = \array_keys((array)$filtered['aSlide']);
                $count     = count($aSlideKey);
                for ($i = 0; $i < $count; $i++) {
                    $slide  = new Slide();
                    $aSlide = $filtered['aSlide'][$aSlideKey[$i]];
                    if (mb_strpos((string)$aSlideKey[$i], 'neu') === false) {
                        $slide->setID((int)$aSlideKey[$i]);
                    }

                    $slide->setSliderID($kSlider);
                    $slide->setTitle(\htmlspecialchars($aSlide['cTitel'], \ENT_COMPAT | \ENT_HTML401, \JTL_CHARSET));
                    $slide->setImage($aSlide['cBild']);
                    $slide->setThumbnail($aSlide['cThumbnail']);
                    $slide->setText($aSlide['cText']);
                    $slide->setLink($aSlide['cLink']);
                    $slide->setSort((int)$aSlide['nSort']);
                    if ((int)$aSlide['delete'] === 1) {
                        $slide->delete();
                    } else {
                        $slide->save();
                    }
                    $this->cache->flushTags([\CACHING_GROUP_CORE]);
                }
                break;
            default:
                $smarty->assign('disabled', '');
                if ($action !== 'view' && !empty($_POST) && Form::validateToken()) {
                    $filtered = Text::filterXSS($_POST);
                    $slider   = new Slider($this->db);
                    $_kSlider = Request::postInt('kSlider');
                    $slider->load($kSlider, false);
                    $slider->set((object)$filtered);
                    // extensionpoint
                    $languageID      = Request::postInt('kSprache');
                    $customerGroupID = Request::postInt('kKundengruppe');
                    $pageType        = Request::postInt('nSeitenTyp');
                    $cKey            = Request::postVar('cKey');
                    $cKeyValue       = '';
                    $cValue          = '';
                    if ($pageType === \PAGE_ARTIKEL) {
                        $cKey      = 'kArtikel';
                        $cKeyValue = 'article_key';
                        $cValue    = $filtered[$cKeyValue];
                    } elseif ($pageType === \PAGE_ARTIKELLISTE) {
                        $filter = [
                            'kMerkmalWert' => 'attribute_key',
                            'kKategorie'   => 'categories_key',
                            'kHersteller'  => 'manufacturer_key',
                            'cSuche'       => 'keycSuche'
                        ];

                        $cKeyValue = $filter[$cKey];
                        $cValue    = $filtered[$cKeyValue];
                    } elseif ($pageType === \PAGE_EIGENE) {
                        $cKey      = 'kLink';
                        $cKeyValue = 'link_key';
                        $cValue    = $filtered[$cKeyValue];
                    }
                    if (!empty($cKeyValue) && empty($cValue)) {
                        $this->alertService->addError(\sprintf(\__('errorKeyMissing'), $cKey), 'errorKeyMissing');
                    } else {
                        if (empty($slider->getEffects())) {
                            $slider->setEffects('random');
                        }
                        if ($slider->save() === true) {
                            $this->db->delete(
                                'textensionpoint',
                                ['cClass', 'kInitial'],
                                ['slider', $slider->getID()]
                            );
                            $extension                = new stdClass();
                            $extension->kSprache      = $languageID;
                            $extension->kKundengruppe = $customerGroupID;
                            $extension->nSeite        = $pageType;
                            $extension->cKey          = $cKey;
                            $extension->cValue        = $cValue;
                            $extension->cClass        = 'slider';
                            $extension->kInitial      = $slider->getID();
                            $this->db->insert('textensionpoint', $extension);

                            $this->alertService->addSuccess(
                                \__('successSliderSave'),
                                'successSliderSave',
                                ['saveInSession' => true]
                            );
                            $this->cache->flushTags([\CACHING_GROUP_CORE]);
                            \header('Location: ' . $redirectUrl);
                            exit;
                        }
                        $this->alertService->addError(\__('errorSliderSave'), 'errorSliderSave');
                    }
                }
                break;
        }
        switch ($action) {
            case 'slides':
                $slider = new Slider($this->db);
                $slider->load($kSlider, false);
                $smarty->assign('oSlider', $slider);
                if (!\is_object($slider)) {
                    $this->alertService->addError(\__('errorSliderNotFound'), 'errorSliderNotFound');
                    $action = 'view';
                }
                break;

            case 'edit':
                if ($kSlider === 0 && $_kSlider > 0) {
                    $kSlider = $_kSlider;
                }
                $slider = new Slider($this->db);
                $slider->load($kSlider, false);
                $smarty->assign('customerGroups', CustomerGroup::getGroups())
                    ->assign('oExtension', $this->holeExtension($kSlider));

                if ($slider->getEffects() !== 'random') {
                    $effects = \explode(';', $slider->getEffects());
                    $options = '';
                    foreach ($effects as $cKey => $cValue) {
                        $options .= '<option value="' . $cValue . '">' . $cValue . '</option>';
                    }
                    $smarty->assign('cEffects', $options);
                } else {
                    $smarty->assign('checked', 'checked="checked"')
                        ->assign('disabled', 'disabled="true"');
                }
                $smarty->assign('oSlider', $slider);

                if (!\is_object($slider)) {
                    $this->alertService->addError(\__('errorSliderNotFound'), 'errorSliderNotFound');
                    $action = 'view';
                    break;
                }
                break;

            case 'new':
                $smarty->assign('checked', 'checked="checked"')
                    ->assign('customerGroups', CustomerGroup::getGroups())
                    ->assign('oSlider', new Slider($this->db));
                break;

            case 'delete':
                $slider = new Slider($this->db);
                $slider->load($kSlider, false);
                if ($slider->delete() === true) {
                    $this->cache->flushTags([\CACHING_GROUP_CORE]);
                    \header('Location: ' . $redirectUrl);
                    exit;
                }
                $this->alertService->addError(\__('errorSliderRemove'), 'errorSliderRemove');
                break;

            default:
                break;
        }

        $sliders    = $this->db->getObjects('SELECT * FROM tslider');
        $pagination = (new Pagination('sliders'))
            ->setRange(4)
            ->setItemArray($sliders)
            ->assemble();

        return $smarty->assign('action', $action)
            ->assign('kSlider', $kSlider)
            ->assign('validPageTypes', (new BoxAdmin($this->db))->getMappedValidPageTypes())
            ->assign('pagination', $pagination)
            ->assign('route', $route->getPath())
            ->assign('oSlider_arr', $pagination->getPageItems())
            ->getResponse('slider.tpl');
    }
    /**
     * @param int $sliderID
     * @return stdClass|null
     * @former holeExtension()
     */
    private function holeExtension(int $sliderID): ?stdClass
    {
        $data = $this->db->select('textensionpoint', 'cClass', 'slider', 'kInitial', $sliderID);
        if ($data !== null) {
            $data->kExtensionPoint = (int)$data->kExtensionPoint;
            $data->kSprache        = (int)$data->kSprache;
            $data->kKundengruppe   = (int)$data->kKundengruppe;
            $data->nSeite          = (int)$data->nSeite;
            $data->kInitial        = (int)$data->kInitial;
        }

        return $data;
    }
}
