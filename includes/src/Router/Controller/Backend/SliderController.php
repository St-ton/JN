<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use JTL\Backend\Permissions;
use JTL\Customer\CustomerGroup;
use JTL\Helpers\Text;
use JTL\Pagination\Pagination;
use JTL\Slide;
use JTL\Slider;
use JTL\Smarty\JTLSmarty;
use Laminas\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use stdClass;

/**
 * Class SliderController
 * @package JTL\Router\Controller\Backend
 */
class SliderController extends AbstractBackendController
{
    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        $this->checkPermissions(Permissions::SLIDER_VIEW);
        $this->getText->loadAdminLocale('pages/slider');

        $tmpID    = 0;
        $action   = $this->tokenIsValid && $this->request->request('action', null) !== null
            ? $this->request->request('action')
            : 'view';
        $sliderID = (int)$this->request->request('id', 0);
        if ($action === 'slide_set') {
            $this->actionSlideSet($sliderID);
        } else {
            $this->smarty->assign('disabled', '');
            if ($this->tokenIsValid && $action !== 'view' && !empty($this->request->getBody())) {
                $tmpID = $this->request->postInt('kSlider');
                if (($response = $this->actionView($sliderID)) !== null) {
                    return $response;
                }
            }
        }
        switch ($action) {
            case 'slides':
                $slider = new Slider($this->db);
                $slider->load($sliderID, false);
                $this->smarty->assign('oSlider', $slider);
                if (!\is_object($slider)) {
                    $this->alertService->addError(\__('errorSliderNotFound'), 'errorSliderNotFound');
                    $action = 'view';
                }
                break;
            case 'edit':
                if ($sliderID === 0 && $tmpID > 0) {
                    $sliderID = $tmpID;
                }
                $slider = new Slider($this->db);
                $slider->load($sliderID, false);
                $this->smarty->assign('customerGroups', CustomerGroup::getGroups())
                    ->assign('oExtension', $this->getExtension($sliderID));

                if ($slider->getEffects() !== 'random') {
                    $effects = \explode(';', $slider->getEffects());
                    $options = '';
                    foreach ($effects as $cValue) {
                        $options .= '<option value="' . $cValue . '">' . $cValue . '</option>';
                    }
                    $this->smarty->assign('cEffects', $options);
                } else {
                    $this->smarty->assign('checked', 'checked="checked"')
                        ->assign('disabled', 'disabled="true"');
                }
                $this->smarty->assign('oSlider', $slider);

                if (!\is_object($slider)) {
                    $this->alertService->addError(\__('errorSliderNotFound'), 'errorSliderNotFound');
                    $action = 'view';
                    break;
                }
                break;
            case 'new':
                $this->smarty->assign('checked', 'checked="checked"')
                    ->assign('customerGroups', CustomerGroup::getGroups())
                    ->assign('oSlider', new Slider($this->db));
                break;
            case 'delete':
                $slider = new Slider($this->db);
                $slider->load($sliderID, false);
                if ($slider->delete() === true) {
                    $this->cache->flushTags([\CACHING_GROUP_CORE]);

                    return new RedirectResponse($this->baseURL . $this->route);
                }
                $this->alertService->addError(\__('errorSliderRemove'), 'errorSliderRemove');
                break;
            default:
                break;
        }

        $pagination = (new Pagination('sliders'))
            ->setRange(4)
            ->setItemArray($this->db->getObjects('SELECT * FROM tslider'))
            ->assemble();

        return $this->smarty->assign('action', $action)
            ->assign('kSlider', $sliderID)
            ->assign('validPageTypes', BoxController::getMappedValidPageTypes())
            ->assign('pagination', $pagination)
            ->assign('oSlider_arr', $pagination->getPageItems())
            ->getResponse('slider.tpl');
    }

    /**
     * @param int $sliderID
     * @return stdClass|null
     * @former holeExtension()
     */
    private function getExtension(int $sliderID): ?stdClass
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

    /**
     * @param int $sliderID
     * @return void
     */
    private function actionSlideSet(int $sliderID): void
    {
        $filtered = Text::filterXSS($_REQUEST);
        foreach (\array_keys((array)$filtered['aSlide']) as $item) {
            $slide  = new Slide();
            $aSlide = $filtered['aSlide'][$item];
            if (!\str_contains((string)$item, 'neu')) {
                $slide->setID((int)$item);
            }
            $slide->setSliderID($sliderID);
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
        }
        $this->cache->flushTags([\CACHING_GROUP_CORE]);
    }

    /**
     * @param int $sliderID
     * @return ResponseInterface|null
     */
    private function actionView(int $sliderID): ?ResponseInterface
    {
        $filtered = Text::filterXSS($this->request->getBody());
        $slider   = new Slider($this->db);
        $slider->load($sliderID, false);
        $slider->set((object)$filtered);
        $languageID      = $this->request->postInt('kSprache');
        $customerGroupID = $this->request->postInt('kKundengruppe');
        $pageType        = $this->request->postInt('nSeitenTyp');
        $key             = $this->request->post('cKey');
        $keyValue        = '';
        $value           = '';
        if ($pageType === \PAGE_ARTIKEL) {
            $key      = 'kArtikel';
            $keyValue = 'article_key';
            $value    = $filtered[$keyValue];
        } elseif ($pageType === \PAGE_ARTIKELLISTE) {
            $filter   = [
                'kMerkmalWert' => 'attribute_key',
                'kKategorie'   => 'categories_key',
                'kHersteller'  => 'manufacturer_key',
                'cSuche'       => 'keycSuche'
            ];
            $keyValue = $filter[$key];
            $value    = $filtered[$keyValue];
        } elseif ($pageType === \PAGE_EIGENE) {
            $key      = 'kLink';
            $keyValue = 'link_key';
            $value    = $filtered[$keyValue];
        }
        if (!empty($keyValue) && empty($value)) {
            $this->alertService->addError(\sprintf(\__('errorKeyMissing'), $key), 'errorKeyMissing');

            return null;
        }
        if (empty($slider->getEffects())) {
            $slider->setEffects('random');
        }
        if ($slider->save() !== true) {
            $this->alertService->addError(\__('errorSliderSave'), 'errorSliderSave');

            return null;
        }
        $this->db->delete(
            'textensionpoint',
            ['cClass', 'kInitial'],
            ['slider', $slider->getID()]
        );
        $extension                = new stdClass();
        $extension->kSprache      = $languageID;
        $extension->kKundengruppe = $customerGroupID;
        $extension->nSeite        = $pageType;
        $extension->cKey          = $key;
        $extension->cValue        = $value;
        $extension->cClass        = 'slider';
        $extension->kInitial      = $slider->getID();
        $this->db->insert('textensionpoint', $extension);
        $this->alertService->addSuccess(
            \__('successSliderSave'),
            'successSliderSave',
            ['saveInSession' => true]
        );
        $this->cache->flushTags([\CACHING_GROUP_CORE]);

        return new RedirectResponse($this->baseURL . $this->route);
    }
}
