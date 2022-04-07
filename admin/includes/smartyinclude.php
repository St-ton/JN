<?php declare(strict_types=1);

use Illuminate\Support\Collection;
use JTL\Backend\AdminTemplate;
use JTL\Backend\Notification;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Language\LanguageHelper;
use JTL\License\Checker;
use JTL\License\Manager;
use JTL\License\Mapper;
use JTL\Plugin\Admin\StateChanger;
use JTL\Router\BackendRouter;
use JTL\Shop;
use JTL\Shopsetting;
use JTL\Smarty\ContextType;
use JTL\Smarty\JTLSmarty;
use JTL\Update\Updater;

/** @global \JTL\Backend\AdminAccount $oAccount */
/** @global array $adminMenu */

require_once __DIR__ . '/admin_menu.php';

$smarty             = JTLSmarty::getInstance(false, ContextType::BACKEND);
$template           = AdminTemplate::getInstance();
$config             = Shopsetting::getInstance()->getAll();
$shopURL            = Shop::getURL();
$adminURL           = Shop::getAdminURL();
$db                 = Shop::Container()->getDB();
$currentTemplateDir = $smarty->getTemplateUrlPath();
$updates            = new Collection();
$updater            = new Updater($db);
$availableLanguages = LanguageHelper::getInstance()->gibInstallierteSprachen();
$hasPendingUpdates  = $updater->hasPendingUpdates();
$resourcePaths      = $template->getResources(false);
$expired            = collect([]);
$gettext            = Shop::Container()->getGetText();
if (!$hasPendingUpdates) {
    $cache = Shop::Container()->getCache();
    if (Request::getVar('licensenoticeaccepted') === 'true') {
        $_SESSION['licensenoticeaccepted'] = 0;
    }
    if (Request::postVar('action') === 'disable-expired-plugins' && Form::validateToken()) {
        $sc = new StateChanger($db, $cache);
        foreach ($_POST['pluginID'] as $pluginID) {
            $sc->deactivate((int)$pluginID);
        }
    }
    $mapper                = new Mapper(new Manager($db, $cache));
    $checker               = new Checker(Shop::Container()->getBackendLogService(), $db, $cache);
    $updates               = $checker->getUpdates($mapper);
    $licenseNoticeAccepted = (int)($_SESSION['licensenoticeaccepted'] ?? -1);
    if ($licenseNoticeAccepted === -1 && SAFE_MODE === false) {
        $expired = $checker->getLicenseViolations($mapper);
    } else {
        $licenseNoticeAccepted++;
    }
    if ($licenseNoticeAccepted > 5) {
        $licenseNoticeAccepted = -1;
    }
    $_SESSION['licensenoticeaccepted'] = $licenseNoticeAccepted;
}
$langTag = $_SESSION['AdminAccount']->language ?? $gettext->getLanguage();
$smarty->assign('URL_SHOP', $shopURL)
    ->assign('expiredLicenses', $expired)
    ->assign('jtl_token', Form::getTokenInput())
    ->assign('shopURL', $shopURL)
    ->assign('adminURL', $adminURL)
    ->assign('adminTplVersion', empty($template->version) ? '1.0.0' : $template->version)
    ->assignDeprecated('PFAD_ADMIN', PFAD_ADMIN, '5.0.0')
    ->assignDeprecated('JTL_CHARSET', JTL_CHARSET, '5.0.0')
    ->assign('session_name', session_name())
    ->assign('session_id', session_id())
    ->assign('currentTemplateDir', $currentTemplateDir)
    ->assign('templateBaseURL', $adminURL . '/' . $currentTemplateDir)
    ->assign('lang', 'german')
    ->assign('admin_css', $resourcePaths['css'])
    ->assign('admin_js', $resourcePaths['js'])
    ->assign('account', $oAccount->account())
    ->assign('PFAD_CKEDITOR', $shopURL . '/' . PFAD_CKEDITOR)
    ->assign('PFAD_CODEMIRROR', $shopURL . '/' . PFAD_CODEMIRROR)
    ->assign('Einstellungen', $config)
    ->assign('notifications', Notification::getInstance($db))
    ->assign('licenseItemUpdates', $updates)
    ->assign('alertList', Shop::Container()->getAlertService())
    ->assign('favorites', $oAccount->favorites())
    ->assign('language', $langTag)
    ->assign('hasPendingUpdates', $hasPendingUpdates)
    ->assign('sprachen', $availableLanguages)
    ->assign('availableLanguages', $availableLanguages)
    ->assign('languageName', Locale::getDisplayLanguage($langTag, $langTag))
    ->assign('languages', $gettext->getAdminLanguages())
    ->assign('faviconAdminURL', Shop::getFaviconURL(true))
    ->assign('cTab', Text::filterXSS(Request::verifyGPDataString('tab')))
    ->assign(
        'wizardDone',
        (($conf['global']['global_wizard_done'] ?? 'Y') === 'Y'
            || !str_contains($_SERVER['REQUEST_URI'], BackendRouter::ROUTE_WIZARD))
        && !Request::getVar('fromWizard')
    );
