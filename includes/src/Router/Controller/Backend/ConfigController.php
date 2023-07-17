<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use JTL\Backend\Permissions;
use JTL\Backend\Settings\Manager;
use JTL\Backend\Settings\Search;
use JTL\Backend\Settings\SectionFactory;
use JTL\Backend\Settings\Sections\Subsection;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Helpers\ShippingMethod;
use JTL\Helpers\Text;
use JTL\Mail\SmtpTest;
use JTL\Router\Route;
use JTL\Shop;
use JTL\Shopsetting;
use JTL\Smarty\JTLSmarty;
use Laminas\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class ConfigController
 * @package JTL\Router\Controller\Backend
 */
class ConfigController extends AbstractBackendController
{
    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        $this->smarty = $smarty;
        $this->getText->loadAdminLocale('pages/einstellungen');
        $sectionID      = (int)($args['id'] ?? $_REQUEST['kSektion'] ?? 0);
        $isSearch       = (int)($_REQUEST['einstellungen_suchen'] ?? 0) === 1;
        $sectionFactory = new SectionFactory();
        $search         = Request::verifyGPDataString('cSuche');
        $settingManager = new Manager($this->db, $smarty, $this->account, $this->getText, $this->alertService);
        $this->getText->loadConfigLocales(true, true);
        $this->route = \str_replace('[/{id}]', '', $this->route);
        if ($isSearch) {
            $this->checkPermissions(Permissions::SETTINGS_SEARCH_VIEW);
        }
        switch ($sectionID) {
            case \CONF_GLOBAL:
                $this->checkPermissions(Permissions::SETTINGS_GLOBAL_VIEW);
                break;
            case \CONF_STARTSEITE:
                $this->checkPermissions(Permissions::SETTINGS_STARTPAGE_VIEW);
                break;
            case \CONF_EMAILS:
                $this->checkPermissions(Permissions::SETTINGS_EMAILS_VIEW);
                break;
            case \CONF_ARTIKELUEBERSICHT:
                $this->checkPermissions(Permissions::SETTINGS_ARTICLEOVERVIEW_VIEW);
                // Sucheinstellungen haben eigene Logik
                return new RedirectResponse($this->baseURL . '/' . Route::SEARCHCONFIG);
            case \CONF_ARTIKELDETAILS:
                $this->checkPermissions(Permissions::SETTINGS_ARTICLEDETAILS_VIEW);
                break;
            case \CONF_KUNDEN:
                $this->checkPermissions(Permissions::SETTINGS_CUSTOMERFORM_VIEW);
                break;
            case \CONF_KAUFABWICKLUNG:
                $this->checkPermissions(Permissions::SETTINGS_BASKET_VIEW);
                break;
            case \CONF_BOXEN:
                $this->checkPermissions(Permissions::SETTINGS_BOXES_VIEW);
                break;
            case \CONF_BILDER:
                return new RedirectResponse($this->baseURL . '/' . Route::IMAGES);
            case 0:
                break;
            default:
                return $this->notFoundResponse($request, $args, $smarty);
        }
        $postData        = Text::filterXSS($_POST);
        $defaultCurrency = $this->db->select('twaehrung', 'cStandard', 'Y');
        $step            = 'uebersicht';
        if ($sectionID > 0) {
            $step    = 'einstellungen bearbeiten';
            $section = $sectionFactory->getSection($sectionID, $settingManager);
            $smarty->assign('kEinstellungenSektion', $section->getID());
        } else {
            $section = $sectionFactory->getSection(\CONF_GLOBAL, $settingManager);
            $smarty->assign('kEinstellungenSektion', \CONF_GLOBAL);
        }
        $smarty->assign('testResult', null);

        if ($isSearch) {
            $step = 'einstellungen bearbeiten';
        }
        if (Request::postVar('resetSetting') !== null) {
            $settingManager->resetSetting(Request::postVar('resetSetting'));
        } elseif ($sectionID > 0 && Request::postInt('einstellungen_bearbeiten') === 1 && Form::validateToken()) {
            // Einstellungssuche
            $step = 'einstellungen bearbeiten';
            if ($isSearch) {
                $searchInstance = new Search($this->db, $this->getText, $settingManager);
                $sections       = $searchInstance->getResultSections($search);
                $smarty->assign('cSearch', $searchInstance->getTitle());
                foreach ($sections as $section) {
                    $section->update($_POST);
                }
            } else {
                $sectionInstance = $sectionFactory->getSection($sectionID, $settingManager);
                $sectionInstance->update($_POST);
            }
            $this->db->query('UPDATE tglobals SET dLetzteAenderung = NOW()');
            $this->alertService->addSuccess(\__('successConfigSave'), 'successConfigSave');
            $tagsToFlush = [\CACHING_GROUP_OPTION];
            if ($sectionID === 1 || $sectionID === 4 || $sectionID === 5) {
                $tagsToFlush[] = \CACHING_GROUP_CORE;
                $tagsToFlush[] = \CACHING_GROUP_ARTICLE;
                $tagsToFlush[] = \CACHING_GROUP_CATEGORY;
            } elseif ($sectionID === 8) {
                $tagsToFlush[] = \CACHING_GROUP_BOX;
            }
            $this->cache->flushTags($tagsToFlush);
            Shopsetting::getInstance($this->db, $this->cache)->reset();
            if (isset($postData['test_emails']) && (int)$postData['test_emails'] === 1) {
                \ob_start();
                $test = new SmtpTest();
                $test->run(Shop::getSettingSection(\CONF_EMAILS));
                $result = \ob_get_clean();
                $smarty->assign('testResult', $result);
            }
        }
        if ($step === 'uebersicht') {
            $overview = $settingManager->getAllSections();
            $smarty->assign('sectionOverview', $overview);
        }
        if ($step === 'einstellungen bearbeiten') {
            if ($isSearch) {
                $searchInstance = new Search($this->db, $this->getText, $settingManager);
                $sections       = $searchInstance->getResultSections($search);
                $smarty->assign('cSearch', $searchInstance->getTitle())
                    ->assign('cSuche', $search);
            } else {
                $group           = Request::verifyGPDataString('group');
                $sectionInstance = $sectionFactory->getSection($sectionID, $settingManager);
                $sectionInstance->load();
                $filtered = $sectionInstance->filter($group);
                if ($group !== '' && \count($filtered) > 0) {
                    $subsection = new Subsection();
                    $subsection->setName(\__($group));
                    $subsection->setItems($filtered);
                    $sectionInstance->setItems([]);
                    $sectionInstance->setSubsections([$subsection]);
                }
                $sections = [$sectionInstance];
            }
            $group = Text::filterXSS(Request::verifyGPDataString('group'));
            $smarty->assign('section', $section)
                ->assign('title', \__('settings') . ': ' . ($group !== '' ? \__($group) : \__($section->getName())))
                ->assign('sections', $sections);
        }
        $this->assignScrollPosition();

        return $smarty->assign('cPrefURL', \__('prefURL' . $sectionID))
            ->assign('step', $step)
            ->assign('route', $this->route)
            ->assign('countries', ShippingMethod::getPossibleShippingCountries())
            ->assign('waehrung', $defaultCurrency->cName ?? '')
            ->getResponse('einstellungen.tpl');
    }
}
