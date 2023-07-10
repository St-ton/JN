<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use JTL\Backend\Permissions;
use JTL\Backend\Settings\Manager;
use JTL\Backend\Settings\Search;
use JTL\Backend\Settings\SectionFactory;
use JTL\Backend\Settings\Sections\Subsection;
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
        $this->getText->loadAdminLocale('pages/einstellungen');
        $sectionID      = (int)($args['id'] ?? $this->request->request('kSektion', 0));
        $isSearch       = (int)($this->request->request('einstellungen_suchen', 0)) === 1;
        $sectionFactory = new SectionFactory();
        $search         = $this->request->request('cSuche');
        $settingManager = new Manager($this->db, $this->smarty, $this->account, $this->getText, $this->alertService);
        $this->getText->loadConfigLocales(true, true);
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
            case \CONF_BILDER:
                $this->checkPermissions(Permissions::SETTINGS_IMAGES_VIEW);
                break;
            case 0:
                break;
            default:
                return $this->notFoundResponse($request, $args);
        }
        $postData        = Text::filterXSS($request->getParsedBody());
        $defaultCurrency = $this->db->select('twaehrung', 'cStandard', 'Y');
        $step            = 'uebersicht';
        if ($sectionID > 0) {
            $step    = 'einstellungen bearbeiten';
            $section = $sectionFactory->getSection($sectionID, $settingManager);
            $this->smarty->assign('kEinstellungenSektion', $section->getID());
        } else {
            $section = $sectionFactory->getSection(\CONF_GLOBAL, $settingManager);
            $this->smarty->assign('kEinstellungenSektion', \CONF_GLOBAL);
        }
        $this->smarty->assign('testResult', null);

        if ($isSearch) {
            $step = 'einstellungen bearbeiten';
        }
        if ($this->request->post('resetSetting') !== null) {
            $settingManager->resetSetting($this->request->post('resetSetting'));
        } elseif ($this->tokenIsValid && $sectionID > 0 && $this->request->postInt('einstellungen_bearbeiten') === 1) {
            // Einstellungssuche
            $step = 'einstellungen bearbeiten';
            if ($isSearch) {
                $searchInstance = new Search($this->db, $this->getText, $settingManager);
                $sections       = $searchInstance->getResultSections($search);
                $this->smarty->assign('cSearch', $searchInstance->getTitle());
                foreach ($sections as $section) {
                    $section->update($request->getParsedBody());
                }
            } else {
                $sectionInstance = $sectionFactory->getSection($sectionID, $settingManager);
                $sectionInstance->update($request->getParsedBody());
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
            Shopsetting::getInstance()->reset();
            if (isset($postData['test_emails']) && (int)$postData['test_emails'] === 1) {
                \ob_start();
                $test = new SmtpTest();
                $test->run(Shop::getSettingSection(\CONF_EMAILS));
                $result = \ob_get_clean();
                $this->smarty->assign('testResult', $result);
            }
        }
        if ($step === 'uebersicht') {
            $overview = $settingManager->getAllSections();
            $this->smarty->assign('sectionOverview', $overview);
        }
        if ($step === 'einstellungen bearbeiten') {
            if ($isSearch) {
                $searchInstance = new Search($this->db, $this->getText, $settingManager);
                $sections       = $searchInstance->getResultSections($search);
                $this->smarty->assign('cSearch', $searchInstance->getTitle())
                    ->assign('cSuche', $search);
            } else {
                $group           = $this->request->request('group');
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
            $group = Text::filterXSS($this->request->request('group'));
            $this->smarty->assign('section', $section)
                ->assign('title', \__('settings') . ': ' . ($group !== '' ? \__($group) : \__($section->getName())))
                ->assign('sections', $sections);
        }
        $this->assignScrollPosition();

        return $this->smarty->assign('cPrefURL', \__('prefURL' . $sectionID))
            ->assign('step', $step)
            ->assign('countries', ShippingMethod::getPossibleShippingCountries())
            ->assign('waehrung', $defaultCurrency->cName ?? '')
            ->getResponse('einstellungen.tpl');
    }
}
