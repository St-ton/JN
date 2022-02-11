<?php declare(strict_types=1);

use JTL\Alert\Alert;
use JTL\Backend\Settings\Manager;
use JTL\Backend\Settings\Search;
use JTL\Backend\Settings\SectionFactory;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Helpers\ShippingMethod;
use JTL\Helpers\Text;
use JTL\Mail\SmtpTest;
use JTL\Shop;
use JTL\Shopsetting;

require_once __DIR__ . '/includes/admininclude.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'einstellungen_inc.php';
/** @global \JTL\Smarty\JTLSmarty $smarty */
/** @global \JTL\Backend\AdminAccount $oAccount */
$sectionID      = (int)($_REQUEST['kSektion'] ?? 0);
$isSearch       = (int)($_REQUEST['einstellungen_suchen'] ?? 0) === 1;
$db             = Shop::Container()->getDB();
$getText        = Shop::Container()->getGetText();
$adminAccount   = Shop::Container()->getAdminAccount();
$alertService   = Shop::Container()->getAlertService();
$sectionFactory = new SectionFactory();
$search         = Request::verifyGPDataString('cSuche');
$searchQuery    = $search;
$settingManager = new Manager($db, $smarty, $adminAccount, $getText, $alertService);
$getText->loadConfigLocales(true, true);
if ($isSearch) {
    $oAccount->permission('SETTINGS_SEARCH_VIEW', true, true);
}

switch ($sectionID) {
    case CONF_GLOBAL:
        $oAccount->permission('SETTINGS_GLOBAL_VIEW', true, true);
        break;
    case CONF_STARTSEITE:
        $oAccount->permission('SETTINGS_STARTPAGE_VIEW', true, true);
        break;
    case CONF_EMAILS:
        $oAccount->permission('SETTINGS_EMAILS_VIEW', true, true);
        break;
    case CONF_ARTIKELUEBERSICHT:
        $oAccount->permission('SETTINGS_ARTICLEOVERVIEW_VIEW', true, true);
        // Sucheinstellungen haben eigene Logik
        header('Location: ' . Shop::getURL(true) . '/' . PFAD_ADMIN . 'sucheinstellungen.php');
        exit;
    case CONF_ARTIKELDETAILS:
        $oAccount->permission('SETTINGS_ARTICLEDETAILS_VIEW', true, true);
        break;
    case CONF_KUNDEN:
        $oAccount->permission('SETTINGS_CUSTOMERFORM_VIEW', true, true);
        break;
    case CONF_KAUFABWICKLUNG:
        $oAccount->permission('SETTINGS_BASKET_VIEW', true, true);
        break;
    case CONF_BILDER:
        $oAccount->permission('SETTINGS_IMAGES_VIEW', true, true);
        break;
    default:
        $oAccount->redirectOnFailure();
        break;
}
$postData        = Text::filterXSS($_POST);
$defaultCurrency = $db->select('twaehrung', 'cStandard', 'Y');
$step            = 'uebersicht';
$alertHelper     = Shop::Container()->getAlertService();
if ($sectionID > 0) {
    $step    = 'einstellungen bearbeiten';
    $section = $sectionFactory->getSection($sectionID, $settingManager);
    $smarty->assign('kEinstellungenSektion', $section->getID());
} else {
    $section = $sectionFactory->getSection(CONF_GLOBAL, $settingManager);
    $smarty->assign('kEinstellungenSektion', CONF_GLOBAL);
}
$smarty->assign('testResult', null);

if ($isSearch) {
    $step = 'einstellungen bearbeiten';
}
if (Request::postVar('resetSetting') !== null) {
    $settingManager->resetSetting(Request::postVar('resetSetting'));
} elseif (Request::postInt('einstellungen_bearbeiten') === 1 && $sectionID > 0 && Form::validateToken()) {
    // Einstellungssuche
    $step     = 'einstellungen bearbeiten';
    $confData = [];
    if ($isSearch) {
        $searchInstance = new Search($db, $getText, $settingManager);
        $sections       = $searchInstance->getResultSections($search);
        $smarty->assign('cSearch', $searchInstance->getTitle());
        foreach ($sections as $section) {
            $section->update($_POST);
        }
    } else {
        $sectionInstance = $sectionFactory->getSection($sectionID, $settingManager);
        $sectionInstance->update($_POST);
    }
    $db->query('UPDATE tglobals SET dLetzteAenderung = NOW()');
    $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successConfigSave'), 'successConfigSave');
    $tagsToFlush = [CACHING_GROUP_OPTION];
    if ($sectionID === 1 || $sectionID === 4 || $sectionID === 5) {
        $tagsToFlush[] = CACHING_GROUP_CORE;
        $tagsToFlush[] = CACHING_GROUP_ARTICLE;
        $tagsToFlush[] = CACHING_GROUP_CATEGORY;
    } elseif ($sectionID === 8) {
        $tagsToFlush[] = CACHING_GROUP_BOX;
    }
    Shop::Container()->getCache()->flushTags($tagsToFlush);
    Shopsetting::getInstance()->reset();
    if (isset($postData['test_emails']) && (int)$postData['test_emails'] === 1) {
        ob_start();
        $test = new SmtpTest();
        $test->run(Shop::getSettingSection(CONF_EMAILS));
        $result = ob_get_clean();
        $smarty->assign('testResult', $result);
    }
}
if ($step === 'uebersicht') {
    $overview = $settingManager->getAllSections();
    $smarty->assign('sectionOverview', $overview);
}
if ($step === 'einstellungen bearbeiten') {
    if ($isSearch) {
        $searchInstance = new Search($db, $getText, $settingManager);
        $sections       = $searchInstance->getResultSections($search);
        $smarty->assign('cSearch', $searchInstance->getTitle())
            ->assign('cSuche', $search);
    } else {
        $sectionInstance = $sectionFactory->getSection($sectionID, $settingManager);
        $sectionInstance->load();
        $sectionInstance->filter(Request::verifyGPDataString('group'));
        $sections = [$sectionInstance];
    }
    $group = Text::filterXSS(Request::verifyGPDataString('group'));
    $smarty->assign('section', $section)
        ->assign('title', __('settings') . ': ' . ($group !== '' ? __($group) : __($section->getName())))
        ->assign('sections', $sections);
}

$smarty->assign('cPrefURL', __('prefURL' . $sectionID))
    ->assign('step', $step)
    ->assign('countries', ShippingMethod::getPossibleShippingCountries())
    ->assign('waehrung', $defaultCurrency->cName)
    ->display('einstellungen.tpl');
