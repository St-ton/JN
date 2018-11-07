<?php

/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/admininclude.php';
require_once PFAD_ROOT . PFAD_ADMIN . 'toolsajax.server.php';
$oAccount->permission('SLIDER_VIEW', true, true);
/** @global JTLSmarty $smarty */
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'slider_inc.php';
$cFehler     = '';
$cHinweis    = '';
$_kSlider    = 0;
$redirectUrl = Shop::getURL() . '/' . PFAD_ADMIN . 'slider.php';
$action      = isset($_REQUEST['action']) && FormHelper::validateToken()
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
            if (strpos($aSlideKey[$i], 'neu') === false) {
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
        if (!empty($_POST) && FormHelper::validateToken()) {
            $slider   = new Slider();
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

            if (empty($slider->getEffects())) {
                $slider->setEffects('random');
            }
            if ($slider->save() === true) {
                Shop::Container()->getDB()->delete(
                    'textensionpoint',
                    ['cClass', 'kInitial'],
                    ['Slider', $slider->getID()]
                );
                $oExtension                = new stdClass();
                $oExtension->kSprache      = $kSprache;
                $oExtension->kKundengruppe = $kKundengruppe;
                $oExtension->nSeite        = $nSeite;
                $oExtension->cKey          = $cKey;
                $oExtension->cValue        = $cValue;
                $oExtension->cClass        = 'Slider';
                $oExtension->kInitial      = $slider->getID();
                Shop::Container()->getDB()->insert('textensionpoint', $oExtension);

                header('Location: ' . $redirectUrl);
                exit;
            }
            $cFehler .= 'Slider konnte nicht gespeichert werden.';

            if (empty($cFehler)) {
                $cHinweis = 'Änderungen erfolgreich gespeichert.';
            }
        }
        break;
}
// Daten anzeigen
switch ($action) {
    case 'slides':
        $slider = new Slider();
        $slider->load($kSlider, false);
        $smarty->assign('oSlider', $slider);
        if (!is_object($slider)) {
            $cFehler = 'Slider wurde nicht gefunden.';
            $action  = 'view';
        }
        break;

    case 'edit':
        if ($kSlider === 0 && $_kSlider > 0) {
            $kSlider = $_kSlider;
        }
        $slider = new Slider();
        $slider->load($kSlider, false);
        $oExtension = holeExtension($kSlider);
        $smarty->assign('oSprachen_arr', Sprache::getInstance(false)->gibInstallierteSprachen())
               ->assign('oKundengruppe_arr', Kundengruppe::getGroups())
               ->assign('oExtension', $oExtension);

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
            $cFehler = 'Slider wurde nicht gefunden.';
            $action  = 'view';
            break;
        }
        break;

    case 'new':
        $slider = new Slider();
        $smarty->assign('checked', 'checked="checked"')
               ->assign('oSprachen_arr', Sprache::getInstance(false)->gibInstallierteSprachen())
               ->assign('oKundengruppe_arr', Kundengruppe::getGroups())
               ->assign('oSlider', $slider);
        break;

    case 'delete':
        $slider   = new Slider();
        $slider->load($kSlider, false);
        $bSuccess = $slider->delete($kSlider);
        if ($bSuccess === true) {
            header('Location: ' . $redirectUrl);
            exit;
        }
        $cFehler = 'Slider konnte nicht entfernt werden.';
        break;

    default:
        break;
}

$smarty->assign('cFehler', $cFehler)
       ->assign('cHinweis', $cHinweis)
       ->assign('cAction', $action)
       ->assign('kSlider', $kSlider)
       ->assign('oSlider_arr', Shop::Container()->getDB()->query(
           'SELECT * FROM tslider',
           \DB\ReturnType::ARRAY_OF_OBJECTS
       ))
       ->display('slider.tpl');
