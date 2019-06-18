<?php

/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTL\Alert\Alert;
use JTL\Boxes\Admin\BoxAdmin;
use JTL\Customer\Kundengruppe;
use JTL\DB\ReturnType;
use JTL\Helpers\Form;
use JTL\Language\LanguageHelper;
use JTL\Pagination\Pagination;
use JTL\Shop;
use JTL\Slide;
use JTL\Slider;

require_once __DIR__ . '/includes/admininclude.php';
require_once PFAD_ROOT . PFAD_ADMIN . 'toolsajax.server.php';
$oAccount->permission('SLIDER_VIEW', true, true);
/** @global \JTL\Smarty\JTLSmarty $smarty */
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'slider_inc.php';
$alertHelper = Shop::Container()->getAlertService();
$db          = Shop::Container()->getDB();
$_kSlider    = 0;
$redirectUrl = Shop::getURL() . '/' . PFAD_ADMIN . 'slider.php';
$action      = isset($_REQUEST['action']) && Form::validateToken()
    ? $_REQUEST['action']
    : 'view';
$kSlider     = isset($_REQUEST['id'])
    ? (int)$_REQUEST['id']
    : 0;
switch ($action) {
    case 'slide_set':
        $aSlideKey = array_keys((array)$_REQUEST['aSlide']);
        $count     = count($aSlideKey);
        for ($i = 0; $i < $count; $i++) {
            $slide  = new Slide();
            $aSlide = $_REQUEST['aSlide'][$aSlideKey[$i]];
            if (mb_strpos($aSlideKey[$i], 'neu') === false) {
                $slide->setID((int)$aSlideKey[$i]);
            }

            $slide->setSliderID($kSlider);
            $slide->setTitle(htmlspecialchars($aSlide['cTitel'], ENT_COMPAT | ENT_HTML401, JTL_CHARSET));
            $slide->setImage($aSlide['cBild']);
            $slide->setText($aSlide['cText']);
            $slide->setLink($aSlide['cLink']);
            $slide->setSort((int)$aSlide['nSort']);
            if ((int)$aSlide['delete'] === 1) {
                $slide->delete();
            } else {
                $slide->save();
            }
        }
        break;
    default:
        $smarty->assign('disabled', '');
        if (!empty($_POST) && Form::validateToken()) {
            $slider   = new Slider($db);
            $_kSlider = (int)$_POST['kSlider'];
            $slider->load($kSlider, false);
            $slider->set((object)$_REQUEST);
            // extensionpoint
            $kSprache      = (int)$_POST['kSprache'];
            $kKundengruppe = $_POST['kKundengruppe'];
            $nSeite        = (int)$_POST['nSeitenTyp'];
            $cKey          = $_POST['cKey'];
            $cKeyValue     = '';
            $cValue        = '';
            if ($nSeite === PAGE_ARTIKEL) {
                $cKey      = 'kArtikel';
                $cKeyValue = 'article_key';
                $cValue    = $_POST[$cKeyValue];
            } elseif ($nSeite === PAGE_ARTIKELLISTE) {
                $aFilter_arr = [
                    'kTag'         => 'tag_key',
                    'kMerkmalWert' => 'attribute_key',
                    'kKategorie'   => 'categories_key',
                    'kHersteller'  => 'manufacturer_key',
                    'cSuche'       => 'keycSuche'
                ];

                $cKeyValue = $aFilter_arr[$cKey];
                $cValue    = $_POST[$cKeyValue];
            } elseif ($nSeite === PAGE_EIGENE) {
                $cKey      = 'kLink';
                $cKeyValue = 'link_key';
                $cValue    = $_POST[$cKeyValue];
            }
            if (!empty($cKeyValue) && empty($cValue)) {
                $alertHelper->addAlert(
                    Alert::TYPE_ERROR,
                    sprintf(__('errorKeyMissing'), $cKey),
                    'errorKeyMissing'
                );
            } else {
                if (empty($slider->getEffects())) {
                    $slider->setEffects('random');
                }
                if ($slider->save() === true) {
                    Shop::Container()->getDB()->delete(
                        'textensionpoint',
                        ['cClass', 'kInitial'],
                        ['slider', $slider->getID()]
                    );
                    $extension                = new stdClass();
                    $extension->kSprache      = $kSprache;
                    $extension->kKundengruppe = $kKundengruppe;
                    $extension->nSeite        = $nSeite;
                    $extension->cKey          = $cKey;
                    $extension->cValue        = $cValue;
                    $extension->cClass        = 'slider';
                    $extension->kInitial      = $slider->getID();
                    Shop::Container()->getDB()->insert('textensionpoint', $extension);

                    $alertHelper->addAlert(
                        Alert::TYPE_SUCCESS,
                        __('successSliderSave'),
                        'successSliderSave',
                        ['saveInSession' => true]
                    );
                    header('Location: ' . $redirectUrl);
                    exit;
                }
                $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorSliderSave'), 'errorSliderSave');
            }
        }
        break;
}
switch ($action) {
    case 'slides':
        $slider = new Slider($db);
        $slider->load($kSlider, false);
        $smarty->assign('oSlider', $slider);
        if (!is_object($slider)) {
            $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorSliderNotFound'), 'errorSliderNotFound');
            $action = 'view';
        }
        break;

    case 'edit':
        if ($kSlider === 0 && $_kSlider > 0) {
            $kSlider = $_kSlider;
        }
        $slider = new Slider($db);
        $slider->load($kSlider, false);
        $smarty->assign('oSprachen_arr', LanguageHelper::getInstance()->gibInstallierteSprachen())
               ->assign('oKundengruppe_arr', Kundengruppe::getGroups())
               ->assign('oExtension', holeExtension($kSlider));

        if ($slider->getEffects() !== 'random') {
            $cEffects_arr = explode(';', $slider->getEffects());
            $cEffects     = '';
            foreach ($cEffects_arr as $cKey => $cValue) {
                $cEffects .= '<option value="' . $cValue . '">' . $cValue . '</option>';
            }
            $smarty->assign('cEffects', $cEffects);
        } else {
            $smarty->assign('checked', 'checked="checked"')
                   ->assign('disabled', 'disabled="true"');
        }
        $smarty->assign('oSlider', $slider);

        if (!is_object($slider)) {
            $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorSliderNotFound'), 'errorSliderNotFound');
            $action = 'view';
            break;
        }
        break;

    case 'new':
        $smarty->assign('checked', 'checked="checked"')
               ->assign('oSprachen_arr', LanguageHelper::getInstance()->gibInstallierteSprachen())
               ->assign('oKundengruppe_arr', Kundengruppe::getGroups())
               ->assign('oSlider', new Slider($db));
        break;

    case 'delete':
        $slider = new Slider($db);
        $slider->load($kSlider, false);
        if ($slider->delete() === true) {
            header('Location: ' . $redirectUrl);
            exit;
        }
        $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorSliderRemove'), 'errorSliderRemove');
        break;

    default:
        break;
}

$sliders    = $db->query('SELECT * FROM tslider', ReturnType::ARRAY_OF_OBJECTS);
$pagination = (new Pagination('sliders'))
    ->setRange(4)
    ->setItemArray($sliders)
    ->assemble();

$smarty->assign('cAction', $action)
       ->assign('kSlider', $kSlider)
       ->assign('validPageTypes', (new BoxAdmin($db))->getMappedValidPageTypes())
       ->assign('pagination', $pagination)
       ->assign('oSlider_arr', $pagination->getPageItems())
       ->display('slider.tpl');
