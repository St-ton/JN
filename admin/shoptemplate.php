<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTL\Alert\Alert;
use JTL\DB\ReturnType;
use JTL\Helpers\Form;
use JTL\Helpers\Overlay;
use JTL\Helpers\Request;
use JTL\Helpers\Template as TemplateHelper;
use JTL\LessParser;
use JTL\Shop;
use JTL\SimpleCSS;
use JTL\Template;

/**
 * @global \JTL\Smarty\JTLSmarty $smarty
 */
require_once __DIR__ . '/includes/admininclude.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'template_inc.php';

$oAccount->permission('DISPLAY_TEMPLATE_VIEW', true, true);

$alertHelper    = Shop::Container()->getAlertService();
$lessVars       = [];
$lessVarsSkin   = [];
$lessColors     = [];
$lessColorsSkin = [];
$template       = Template::getInstance();
$db             = Shop::Container()->getDB();
$admin          = Request::getVar('admin') === 'true';
$templateHelper = TemplateHelper::getInstance(true);
$templateHelper->disableCaching();
if (isset($_POST['key'], $_POST['upload'])) {
    $file     = PFAD_ROOT . PFAD_TEMPLATES . $_POST['upload'];
    $response = (object)['status' => 'FAILED'];
    if (file_exists($file) && is_file($file)) {
        $delete = unlink($file);
        if ($delete === true) {
            $response->status = 'OK';
            $upload           = explode('/', $_POST['upload']);
            $template->setConfig($upload[0], 'theme', $_POST['cName'], '');
        }
    }
    die(json_encode($response));
}
if (Request::getVar('check') === 'true') {
    $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successTemplateSave'), 'successTemplateSave');
} elseif (Request::getVar('check') === 'false') {
    $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorTemplateSave'), 'errorTemplateSave');
}
if (Request::postVar('type') === 'layout' && Form::validateToken()) {
    $scss      = new SimpleCSS();
    $dir       = basename($_POST['ordner']);
    $customCSS = $scss->getCustomCSSFile($dir);
    if (Request::postInt('reset') === 1) {
        if (file_exists($customCSS) && is_writable($customCSS)) {
            $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successLayoutReset'), 'successLayoutReset');
        } else {
            $alertHelper->addAlert(
                Alert::TYPE_ERROR,
                sprintf(__('errorLayoutReset'), $customCSS),
                'errorLayoutReset'
            );
        }
    } else {
        $selectors     = $_POST['selector'];
        $attributes    = $_POST['attribute'];
        $values        = $_POST['value'];
        $scss          = new SimpleCSS();
        $selectorCount = count($selectors);
        for ($i = 0; $i < $selectorCount; $i++) {
            $scss->addCSS($selectors[$i], $attributes[$i], $values[$i]);
        }
        if (file_put_contents($customCSS, $scss->renderCSS()) === false) {
            $alertHelper->addAlert(
                Alert::TYPE_ERROR,
                sprintf(__('errorStyleFilePermission'), $customCSS),
                'errorStyleFilePermission'
            );
        } else {
            $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successLayoutSave'), 'successLayoutSave');
        }
    }
}
if (Request::postVar('type') === 'settings' && Form::validateToken()) {
    $dir          = $db->escape($_POST['ordner']);
    $parentFolder = null;
    $tplXML       = $template->leseXML($dir);
    if (!empty($tplXML->Parent)) {
        $parentFolder = (string)$tplXML->Parent;
        $parentTplXML = $template->leseXML($parentFolder);
    }
    $tplConfXML   = $template->leseEinstellungenXML($dir, $parentFolder);
    $sectionCount = count($_POST['cSektion']);
    for ($i = 0; $i < $sectionCount; $i++) {
        $section = $db->escape($_POST['cSektion'][$i]);
        $name    = $db->escape($_POST['cName'][$i]);
        $value   = $db->escape($_POST['cWert'][$i]);
        // for uploads, the value of an input field is the $_FILES index of the uploaded file
        if (mb_strpos($value, 'upload-') === 0) {
            // all upload fields have to start with "upload-" - so check for that
            if (!empty($_FILES[$value]['name']) && $_FILES[$value]['error'] === UPLOAD_ERR_OK) {
                // we have an upload field and the file is set in $_FILES array
                $file  = $_FILES[$value];
                $value = basename($_FILES[$value]['name']);
                $break = false;
                foreach ($tplConfXML as $_section) {
                    if (!isset($_section->oSettings_arr)) {
                        continue;
                    }
                    foreach ($_section->oSettings_arr as $_setting) {
                        if (!isset($_setting->cKey, $_setting->rawAttributes['target']) || $_setting->cKey !== $name) {
                            continue;
                        }
                        $templatePath = PFAD_TEMPLATES . $dir . '/' . $_setting->rawAttributes['target'];
                        $base         = PFAD_ROOT . $templatePath;
                        // optional target file name + extension
                        if (isset($_setting->rawAttributes['targetFileName'])) {
                            $value = $_setting->rawAttributes['targetFileName'];
                        }
                        $targetFile = $base . $value;
                        if (!is_writable($base)) {
                            Shop::Container()->getAlertService()->addAlert(
                                Alert::TYPE_ERROR,
                                sprintf(__('errorFileUpload'), $templatePath),
                                'errorFileUpload',
                                ['saveInSession' => true]
                            );
                        } elseif (!move_uploaded_file($file['tmp_name'], $targetFile)) {
                            Shop::Container()->getAlertService()->addAlert(
                                Alert::TYPE_ERROR,
                                __('errorFileUploadGeneral'),
                                'errorFileUploadGeneral',
                                ['saveInSession' => true]
                            );
                        }
                        $break = true;
                        break;
                    }
                    if ($break === true) {
                        break;
                    }
                }
            } else {
                // no file uploaded, ignore
                continue;
            }
        }
        $template->setConfig($dir, $section, $name, $value);
    }
    $bCheck = __switchTemplate($_POST['ordner'], $_POST['eTyp']);
    if ($bCheck) {
        $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successTemplateSave'), 'successTemplateSave');
    } else {
        $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorTemplateSave'), 'errorTemplateSave');
    }

    if (Request::verifyGPCDataInt('activate') === 1) {
        $overlayHelper = new Overlay($db);
        $overlayHelper->loadOverlaysFromTemplateFolder($_POST['ordner']);
    }

    $db->query('UPDATE tglobals SET dLetzteAenderung = NOW()', ReturnType::DEFAULT);
    // re-init smarty with new template - problematic because of re-including functions.php
    header('Location: ' . Shop::getURL() . '/' .
        PFAD_ADMIN . 'shoptemplate.php?check=' .
        ($bCheck ? 'true' : 'false'), true, 301);
    exit;
}
if (mb_strlen(Request::getVar('settings', '')) > 0 && Form::validateToken()) {
    $dir          = $db->escape($_GET['settings']);
    $oTpl         = $templateHelper->getData($dir, $admin);
    $tplXML       = $templateHelper->getXML($dir, false);
    $preview      = [];
    $parentFolder = null;
    if (!empty($tplXML->Parent)) {
        $parentFolder = (string)$tplXML->Parent;
        $parentTplXML = $templateHelper->getXML($parentFolder, false);
    }
    $tplConfXML       = $template->leseEinstellungenXML($dir, $parentFolder);
    $tplLessXML       = $template->leseLessXML($dir);
    $currentSkin      = $template->getSkin();
    $frontendTemplate = PFAD_ROOT . PFAD_TEMPLATES . $template->getFrontendTemplate();
    $lessStack        = null;
    $shopURL          = Shop::getURL() . '/';
    if ($admin === true) {
        $oTpl->eTyp = 'admin';
        $bCheck     = __switchTemplate($dir, $oTpl->eTyp);
        if ($bCheck) {
            $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successTemplateSave'), 'successTemplateSave');
        } else {
            $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorTemplateSave'), 'errorTemplateSave');
        }
        $db->query('UPDATE tglobals SET dLetzteAenderung = NOW()', ReturnType::DEFAULT);
        // re-init smarty with new template - problematic because of re-including functions.php
        header('Location: ' . $shopURL . PFAD_ADMIN . 'shoptemplate.php', true, 301);
        exit;
    }
    // iterate over each "Section"
    foreach ($tplConfXML as $_conf) {
        // iterate over each "Setting" in this "Section"
        foreach ($_conf->oSettings_arr as $_setting) {
            if ($_setting->cType === 'upload'
                && isset($_setting->rawAttributes['target'], $_setting->rawAttributes['targetFileName'])
                && !file_exists(PFAD_ROOT . PFAD_TEMPLATES . $dir . '/' . $_setting->rawAttributes['target']
                    . $_setting->rawAttributes['targetFileName'])
            ) {
                $_setting->cValue = null;
            }
        }
        if (isset($_conf->cKey, $_conf->oSettings_arr)
            && $_conf->cKey === 'theme'
            && count($_conf->oSettings_arr) > 0
        ) {
            foreach ($_conf->oSettings_arr as $_themeConf) {
                if (isset($_themeConf->cKey, $_themeConf->oOptions_arr)
                    && $_themeConf->cKey === 'theme_default'
                    && count($_themeConf->oOptions_arr) > 0
                ) {
                    foreach ($_themeConf->oOptions_arr as $_theme) {
                        $previewImage = isset($_theme->cOrdner)
                            ? PFAD_ROOT . PFAD_TEMPLATES . $_theme->cOrdner . '/themes/' .
                            $_theme->cValue . '/preview.png'
                            : PFAD_ROOT . PFAD_TEMPLATES . $dir . '/themes/' . $_theme->cValue . '/preview.png';
                        if (file_exists($previewImage)) {
                            $base                     = $shopURL . PFAD_TEMPLATES;
                            $preview[$_theme->cValue] = isset($_theme->cOrdner)
                                ? $base . $_theme->cOrdner . '/themes/' . $_theme->cValue . '/preview.png'
                                : $base . $dir . '/themes/' . $_theme->cValue . '/preview.png';
                        }
                    }
                    break;
                }
            }
        }
    }
    foreach ($tplLessXML as $_less) {
        if (!isset($_less->cName)) {
            continue;
        }
        $themesLess = $_less;
        $less       = new LessParser();
        foreach ($themesLess->oFiles_arr as $filePaths) {
            if ($themesLess->cName === $currentSkin) {
                $less->read($frontendTemplate . '/' . $filePaths->cPath);
                $lessVarsSkin   = $less->getStack();
                $lessColorsSkin = $less->getColors();
            }
            $less->read($frontendTemplate . '/' . $filePaths->cPath);
            $lessVarsTPL   = $less->getStack();
            $lessColorsTPL = $less->getColors();
        }
        $lessVars[$themesLess->cName]   = $lessVarsTPL;
        $lessColors[$themesLess->cName] = $lessColorsTPL;
    }

    $smarty->assign('oTemplate', $oTpl)
           ->assign('themePreviews', (count($preview) > 0) ? $preview : null)
           ->assign('themePreviewsJSON', json_encode($preview))
           ->assign('themesLessVars', $lessVars)
           ->assign('themesLessVarsJSON', json_encode($lessVars))
           ->assign('themesLessVarsSkin', $lessVarsSkin)
           ->assign('themesLessVarsSkinJSON', json_encode($lessVarsSkin))
           ->assign('themesLessColorsSkin', $lessColorsSkin)
           ->assign('themesLessColorsJSON', json_encode($lessColors))
           ->assign('oEinstellungenXML', $tplConfXML);
} elseif (mb_strlen(Request::getVar('switch', '')) > 0) {
    if (__switchTemplate($_GET['switch'], ($admin === true ? 'admin' : 'standard'))) {
        $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successTemplateSave'), 'successTemplateSave');
    } else {
        $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorTemplateSave'), 'errorTemplateSave');
    }

    $db->query('UPDATE tglobals SET dLetzteAenderung = NOW()', ReturnType::DEFAULT);
}
$smarty->assign('admin', ($admin === true) ? 1 : 0)
       ->assign('oTemplate_arr', $templateHelper->getFrontendTemplates())
       ->assign('oAdminTemplate_arr', $templateHelper->getAdminTemplates())
       ->assign('oStoredTemplate_arr', $templateHelper->getStoredTemplates())
       ->display('shoptemplate.tpl');
