<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use Illuminate\Support\Collection;
use InvalidArgumentException;
use JTL\Backend\Permissions;
use JTL\Filesystem\Filesystem;
use JTL\Helpers\Text;
use JTL\License\Manager;
use JTL\License\Mapper;
use JTL\Mapper\PluginState as StateMapper;
use JTL\Mapper\PluginValidation as ValidationMapper;
use JTL\Minify\MinifyService;
use JTL\Plugin\Admin\Installation\Extractor;
use JTL\Plugin\Admin\Installation\InstallationResponse;
use JTL\Plugin\Admin\Installation\Installer;
use JTL\Plugin\Admin\Installation\Uninstaller;
use JTL\Plugin\Admin\Listing;
use JTL\Plugin\Admin\ListingItem;
use JTL\Plugin\Admin\StateChanger;
use JTL\Plugin\Admin\Updater;
use JTL\Plugin\Admin\Validation\LegacyPluginValidator;
use JTL\Plugin\Admin\Validation\PluginValidator;
use JTL\Plugin\Helper;
use JTL\Plugin\InstallCode;
use JTL\Plugin\LegacyPluginLoader;
use JTL\Plugin\PluginLoader;
use JTL\Plugin\State;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use JTL\XMLParser;
use JTLShop\SemVer\Version;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\Response\RedirectResponse;
use League\Flysystem\MountManager;
use League\Flysystem\UnableToDeleteDirectory;
use League\Flysystem\UnableToDeleteFile;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use stdClass;
use function Functional\first;
use function Functional\group;
use function Functional\select;

/**
 * Class PluginManagerController
 * @package JTL\Router\Controller\Backend
 */
class PluginManagerController extends AbstractBackendController
{
    /**
     * @var LegacyPluginValidator
     */
    private LegacyPluginValidator $legacyValidator;

    /**
     * @var PluginValidator
     */
    private PluginValidator $validator;

    /**
     * @var StateChanger
     */
    private StateChanger $stateChanger;

    /**
     * @var Uninstaller
     */
    private Uninstaller $uninstaller;

    /**
     * @var Installer
     */
    private Installer $installer;

    /**
     * @var MinifyService
     */
    private MinifyService $minify;

    /**
     * @var Collection
     */
    private Collection $pluginsInstalled;

    /**
     * @var Collection
     */
    private Collection $pluginsProblematic;

    /**
     * @var Collection
     */
    private Collection $pluginsAvailable;

    /**
     * @var Collection
     */
    private Collection $pluginsDisabled;

    /**
     * @var Collection
     */
    private Collection $pluginsErroneous;

    /**
     * @var Collection
     */
    private Collection $pluginsAll;

    /**
     * @var string
     */
    private string $errorMessage = '';

    /**
     * @var string
     */
    private string $notice = '';

    /**
     * @var bool
     */
    private bool $reload = false;

    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        $this->checkPermissions(Permissions::PLUGIN_ADMIN_VIEW);
        $this->getText->loadAdminLocale('pages/pluginverwaltung');
        $this->getText->loadAdminLocale('pages/plugin');
        $pluginUploaded = false;
        $pluginNotFound = false;
        $response       = null;
        $this->step     = 'pluginverwaltung_uebersicht';

        $parser                = new XMLParser();
        $extractor             = new Extractor($parser);
        $this->minify          = new MinifyService();
        $this->legacyValidator = new LegacyPluginValidator($this->db, $parser);
        $this->validator       = new PluginValidator($this->db, $parser);
        $this->stateChanger    = new StateChanger($this->db, $this->cache, $this->legacyValidator, $this->validator);
        $this->uninstaller     = new Uninstaller($this->db, $this->cache);
        $this->installer       = new Installer(
            $this->db,
            $this->uninstaller,
            $this->legacyValidator,
            $this->validator,
            $this->cache
        );

        if (isset($_SESSION['plugin_msg'])) {
            $this->notice = $_SESSION['plugin_msg'];
            unset($_SESSION['plugin_msg']);
        } elseif (\mb_strlen($this->request->request('h')) > 0) {
            $this->notice = Text::filterXSS(\base64_decode($this->request->request('h')));
        }
        if ($this->tokenIsValid && !empty($_FILES['plugin-install-upload'])) {
            $response       = $extractor->extractPlugin($_FILES['plugin-install-upload']['tmp_name']);
            $pluginUploaded = true;
        }
        $this->assignPluginList();
        if ($pluginUploaded === true) {
            return $this->actionUpload($response);
        }

        if ($this->tokenIsValid && $this->request->requestInt('pluginverwaltung_uebersicht') === 1) {
            $this->actionOverview();
        } elseif ($this->tokenIsValid && $this->request->requestInt('pluginverwaltung_sprachvariable') === 1) {
            $this->actionLanguageVariables();
        }

        if ($this->step === 'pluginverwaltung_sprachvariablen') {
            $pluginID = $this->request->requestInt('kPlugin');
            $loader   = Helper::getLoaderByPluginID($pluginID, $this->db);
            try {
                $this->smarty->assign('pluginLanguages', Shop::Lang()->gibInstallierteSprachen())
                    ->assign('plugin', $loader->init($pluginID))
                    ->assign('kPlugin', $pluginID);
            } catch (InvalidArgumentException) {
                $pluginNotFound = true;
            }
        }

        if ($this->reload === true) {
            $_SESSION['plugin_msg'] = $this->notice;
            return new RedirectResponse($this->baseURL . $this->route, 303);
        }

        if (\SAFE_MODE) {
            $this->alertService->addWarning(\__('Safe mode restrictions.'), 'warnSafeMode', ['dismissable' => false]);
        }
        $this->alertService->addError($this->errorMessage, 'errorPlugin');
        $this->alertService->addNotice($this->notice, 'noticePlugin');
        $this->addMarkdown();

        return $this->smarty->assign('hinweis64', \base64_encode($this->notice))
            ->assign('step', $this->step)
            ->assign('mapper', new StateMapper())
            ->assign('pluginNotFound', $this->smarty->getTemplateVars('pluginNotFound') ?? $pluginNotFound)
            ->assign('shopVersion', Version::parse(\APPLICATION_VERSION))
            ->getResponse('pluginverwaltung.tpl');
    }

    private function assignPluginList(): void
    {
        $manager                  = new Manager($this->db, $this->cache);
        $mapper                   = new Mapper($manager);
        $licenses                 = $mapper->getCollection();
        $listing                  = new Listing($this->db, $this->cache, $this->legacyValidator, $this->validator);
        $this->pluginsAll         = $listing->getAll();
        $this->pluginsDisabled    = $listing->getDisabled()->each(function (ListingItem $item) use ($licenses): void {
            $exsID = $item->getExsID();
            if ($exsID === null) {
                return;
            }
            $license = $licenses->getForExsID($exsID);
            if ($license === null || $license->getLicense()->isExpired()) {
                $this->stateChanger->deactivate($item->getID(), State::EXS_LICENSE_EXPIRED);
                $item->setAvailable(false);
                $item->setState(State::EXS_LICENSE_EXPIRED);
            } elseif ($license->getLicense()->getSubscription()->isExpired()) {
                $this->stateChanger->deactivate($item->getID(), State::EXS_SUBSCRIPTION_EXPIRED);
                $item->setAvailable(false);
                $item->setState(State::EXS_LICENSE_EXPIRED);
            }
        })->filter(static function (ListingItem $e): bool {
            return $e->getState() === State::DISABLED;
        });
        $this->pluginsProblematic = $listing->getProblematic();
        $this->pluginsInstalled   = $listing->getEnabled();
        $this->pluginsAvailable   = $listing->getAvailable()->each(function (ListingItem $item) use ($licenses): void {
            $exsID = $item->getExsID();
            if ($exsID === null) {
                return;
            }
            $license = $licenses->getForExsID($exsID);
            if ($license === null || $license->getLicense()->isExpired()) {
                $item->setHasError(true);
                $item->setErrorMessage(\__('Lizenz abgelaufen'));
                $item->setAvailable(false);
            } elseif ($license->getLicense()->getSubscription()->isExpired()) {
                $item->setHasError(true);
                $item->setErrorMessage(\__('Subscription abgelaufen'));
                $item->setAvailable(false);
            }
        })->filter(static function (ListingItem $item): bool {
            return $item->isAvailable() === true && $item->isInstalled() === false;
        });
        $this->pluginsErroneous   = $listing->getErroneous();

        $this->smarty->assign('pluginsDisabled', $this->pluginsDisabled)
            ->assign('pluginsInstalled', $this->pluginsInstalled)
            ->assign('pluginsProblematic', $this->pluginsProblematic)
            ->assign('pluginsAvailable', $this->pluginsAvailable)
            ->assign('pluginsErroneous', $this->pluginsErroneous)
            ->assign('allPluginItems', $this->pluginsAll);
    }

    /**
     * @param InstallationResponse $installationResponse
     * @return ResponseInterface
     */
    private function actionUpload(InstallationResponse $installationResponse): ResponseInterface
    {
        $this->smarty->assign('shopVersion', Version::parse(\APPLICATION_VERSION))
            ->assign('cTab', 'upload');

        $html                  = new stdClass();
        $html->enabled         = $this->smarty->fetch('tpl_inc/pluginverwaltung_uebersicht_aktiviert_tab.tpl');
        $html->enabled_count   = $this->smarty->getTemplateVars('pluginsInstalled')->count();
        $html->available       = $this->smarty->fetch('tpl_inc/pluginverwaltung_uebersicht_verfuegbar.tpl');
        $html->available_count = $this->smarty->getTemplateVars('pluginsAvailable')->count();
        $html->erroneous       = $this->smarty->fetch('tpl_inc/pluginverwaltung_uebersicht_fehlerhaft.tpl');
        $html->erroneous_count = $this->smarty->getTemplateVars('pluginsErroneous')->count();
        $installationResponse->setHtml($html);

        $response = (new Response())->withStatus(200)->withAddedHeader('content-type', 'application/json');
        $response->getBody()->write($installationResponse->toJson());

        return $response;
    }

    private function actionLanguageVariables(): void
    {
        $this->step = 'pluginverwaltung_sprachvariablen';
        if ($this->request->requestInt('kPlugin') <= 0) {
            return;
        }
        $pluginID = $this->request->requestInt('kPlugin');
        $varID    = $this->request->requestInt('kPluginSprachvariable');
        if ($varID > 0) {
            $this->resetLangVar($pluginID, $varID);
        } else {
            $this->editLangVar($pluginID);
        }
        $this->cache->flushTags([\CACHING_GROUP_PLUGIN . '_' . $pluginID]);
    }

    private function actionOverview(): void
    {
        if ($this->request->postInt('lizenzkey') > 0) {
            $this->enterKeyStep($this->request->postInt('lizenzkey'));
        } elseif ($this->request->postInt('lizenzkeyadd') === 1 && $this->request->postInt('kPlugin') > 0) {
            $this->enterKey($this->request->postInt('kPlugin'));
        } elseif (\is_array($this->request->post('kPlugin')) && \count($this->request->post('kPlugin')) > 0) {
            $this->massAction();
        } elseif ($this->request->requestInt('updaten') === 1) {
            $this->update();
        } elseif ($this->request->requestInt('sprachvariablen') === 1) {
            $this->step = 'pluginverwaltung_sprachvariablen';
        } elseif ($this->request->post('installieren') !== null) {
            $this->install();
        } elseif ($this->request->postInt('delete') === 1) {
            $this->delete();
        } else {
            $this->errorMessage = \__('errorAtLeastOnePlugin');
        }
    }

    /**
     * @return void
     * @throws \JsonException
     */
    private function addMarkdown(): void
    {
        if ($this->step !== 'pluginverwaltung_uebersicht') {
            return;
        }
        $licenseFiles = [];
        $files        = [
            'license.md',
            'License.md',
            'LICENSE.md'
        ];
        foreach ($this->pluginsAvailable as $available) {
            /** @var ListingItem $available */
            $baseDir = $available->getPath();
            foreach ($files as $file) {
                if (\file_exists($baseDir . $file)) {
                    $licenseFiles[$available->getDir()] = $baseDir . $file;
                    break;
                }
            }
        }
        $this->smarty->assign('licenseFiles', \json_encode($licenseFiles, \JSON_THROW_ON_ERROR));
    }

    /**
     * @param int $pluginID
     * @param int $varID
     * @return void
     */
    private function resetLangVar(int $pluginID, int $varID): void
    {
        $langVar = $this->db->select(
            'tpluginsprachvariable',
            'kPlugin',
            $pluginID,
            'kPluginSprachvariable',
            $varID
        );
        if ($langVar !== null && $langVar->kPluginSprachvariable > 0) {
            $affected = $this->db->delete(
                'tpluginsprachvariablecustomsprache',
                ['kPlugin', 'cSprachvariable'],
                [$pluginID, $langVar->cName]
            );
            if ($affected >= 0) {
                $this->notice = \__('successVariableRestore');
            } else {
                $this->errorMessage = \__('errorLangVarNotFound');
            }
        } else {
            $this->errorMessage = \__('errorLangVarNotFound');
        }
    }

    /**
     * @param int $pluginID
     * @return void
     */
    private function editLangVar(int $pluginID): void
    {
        $original = $this->db->getObjects(
            'SELECT * FROM tpluginsprachvariable
                JOIN tpluginsprachvariablesprache
                ON tpluginsprachvariable.kPluginSprachvariable = tpluginsprachvariablesprache.kPluginSprachvariable
                WHERE tpluginsprachvariable.kPlugin = :pid',
            ['pid' => $pluginID]
        );
        $original = group($original, static function (stdClass $e): int {
            return (int)$e->kPluginSprachvariable;
        });
        foreach (Shop::Lang()->gibInstallierteSprachen() as $lang) {
            foreach (Helper::getLanguageVariables($pluginID) as $langVar) {
                $kPluginSprachvariable = $langVar->kPluginSprachvariable;
                $cSprachvariable       = $langVar->cName;
                $iso                   = \mb_convert_case($lang->cISO, \MB_CASE_UPPER);
                $idx                   = $kPluginSprachvariable . '_' . $iso;
                if ($this->request->post($idx) === null) {
                    continue;
                }
                $this->db->delete(
                    'tpluginsprachvariablecustomsprache',
                    ['kPlugin', 'cSprachvariable', 'cISO'],
                    [$pluginID, $cSprachvariable, $iso]
                );
                $customLang                        = new stdClass();
                $customLang->kPlugin               = $pluginID;
                $customLang->cSprachvariable       = $cSprachvariable;
                $customLang->cISO                  = $iso;
                $customLang->kPluginSprachvariable = $kPluginSprachvariable;
                $customLang->cName                 = $this->request->post($idx);
                $match                             = first(
                    select(
                        $original[$kPluginSprachvariable],
                        static function ($e) use ($customLang): bool {
                            return $e->cISO === $customLang->cISO;
                        }
                    )
                );
                if (isset($match->cName) && $match->cName === $customLang->cName) {
                    continue;
                }
                if ($match === null) {
                    $pluginLang                        = new stdClass();
                    $pluginLang->kPluginSprachvariable = $kPluginSprachvariable;
                    $pluginLang->cISO                  = $iso;
                    $pluginLang->cName                 = '';
                    $this->db->insert('tpluginsprachvariablesprache', $pluginLang);
                }

                $this->db->insert('tpluginsprachvariablecustomsprache', $customLang);
            }
        }
        $this->notice = \__('successChangesSave');
        $this->step   = 'pluginverwaltung_uebersicht';
        $this->reload = true;
    }

    /**
     * @param int $pluginID
     * @return void
     */
    private function enterKey(int $pluginID): void
    {
        $this->step = 'pluginverwaltung_lizenzkey';
        $data       = $this->db->select('tplugin', 'kPlugin', $pluginID);
        $plugin     = null;
        if ($data !== null && $data->kPlugin > 0) {
            $loader = Helper::getLoader((int)$data->bExtension === 1, $this->db, $this->cache);
            $plugin = $loader->init($pluginID, true);
            require_once $plugin->getPaths()->getLicencePath() . $plugin->getLicense()->getClassName();
            $class         = $plugin->getLicense()->getClass();
            $license       = new $class();
            $licenseMethod = \PLUGIN_LICENCE_METHODE;
            if ($license->$licenseMethod(Text::filterXSS($this->request->post('cKey')))) {
                Helper::updateStatusByID(State::ACTIVATED, $plugin->getID());
                $plugin->getLicense()->setKey(Text::filterXSS($this->request->post('cKey')));
                $this->db->update(
                    'tplugin',
                    'kPlugin',
                    $plugin->getID(),
                    (object)['cLizenz' => $this->request->post('cKey')]
                );
                $this->notice = \__('successPluginKeySave');
                $this->step   = 'pluginverwaltung_uebersicht';
                $this->reload = true;
                // Lizenzpruefung bestanden => aktiviere alle Zahlungsarten (falls vorhanden)
                Helper::updatePaymentMethodState($plugin, 1);
            } else {
                $this->errorMessage = \__('errorPluginKeyInvalid');
            }
        } else {
            $this->errorMessage = \__('errorPluginNotFound');
        }
        $this->cache->flushTags([\CACHING_GROUP_CORE, \CACHING_GROUP_LANGUAGE, \CACHING_GROUP_PLUGIN]);
        $this->smarty->assign('kPlugin', $pluginID)
            ->assign('oPlugin', $plugin);
    }

    /**
     * @param int $pluginID
     * @return void
     */
    private function enterKeyStep(int $pluginID): void
    {
        $this->step = 'pluginverwaltung_lizenzkey';
        $loader     = Helper::getLoaderByPluginID($pluginID, $this->db, $this->cache);
        try {
            $plugin = $loader->init($pluginID, true);
        } catch (InvalidArgumentException) {
            $plugin = null;
            $this->smarty->assign('pluginNotFound', true);
        }
        $this->smarty->assign('oPlugin', $plugin)
            ->assign('kPlugin', $pluginID);
        $this->cache->flushTags([\CACHING_GROUP_CORE, \CACHING_GROUP_LANGUAGE, \CACHING_GROUP_PLUGIN]);
    }

    private function update(): void
    {
        $res       = InstallCode::INVALID_PLUGIN_ID;
        $pluginID  = $this->request->requestInt('kPlugin');
        $updatable = $this->pluginsInstalled->concat($this->pluginsDisabled)
            ->concat($this->pluginsErroneous)
            ->concat($this->pluginsProblematic);
        $toInstall = $updatable->first(static function ($e) use ($pluginID): bool {
            /** @var ListingItem $e */
            return $e->getID() === $pluginID;
        });
        $updater   = new Updater($this->db, $this->installer);
        /** @var ListingItem $toInstall */
        if ($toInstall !== null && ($res = $updater->updateFromListingItem($toInstall)) === InstallCode::OK) {
            $this->notice .= \__('successPluginUpdate');
            $this->reload  = true;
            $this->cache->flushTags(
                [\CACHING_GROUP_CORE, \CACHING_GROUP_LANGUAGE, \CACHING_GROUP_LICENSES, \CACHING_GROUP_PLUGIN]
            );
            $this->minify->flushCache();
        } else {
            $mapper             = new ValidationMapper();
            $this->errorMessage = \sprintf(
                \__('Could not perform update. Error code %d - %s'),
                $res,
                $mapper->map($res)
            );
        }
    }

    private function delete(): void
    {
        $dirs    = $this->request->post('cVerzeichnis', []);
        $res     = \count($dirs) > 0;
        $manager = new MountManager(['plgn' => Shop::Container()->get(Filesystem::class)]);
        foreach ($dirs as $dir) {
            $dir  = \basename($dir);
            $test = $this->request->post('ext', [])[$dir] ?? -1;
            if ($test === -1) {
                continue;
            }
            $dirName = (int)$test === 1
                ? (\PLUGIN_DIR . $dir)
                : (\PFAD_PLUGIN . $dir);
            try {
                $manager->deleteDirectory('plgn://' . $dirName);
            } catch (UnableToDeleteFile | UnableToDeleteDirectory) {
                $res = false;
            }
        }
        $_SESSION['plugin_msg'] = $res === true
            ? \__('successPluginDelete')
            : \__('errorPluginDeleteAtLeastOne');
    }

    private function install(): void
    {
        $dirs = $this->request->post('cVerzeichnis', []);
        if (\SAFE_MODE) {
            $this->errorMessage = \__('Safe mode enabled.') . ' - ' . \__('pluginBtnInstall');
            return;
        }
        if (!\is_array($dirs)) {
            return;
        }
        foreach ($dirs as $dir) {
            $this->installer->setDir(\basename($dir));
            $res = $this->installer->prepare();
            if ($res === InstallCode::OK || $res === InstallCode::OK_LEGACY) {
                $this->notice = \__('successPluginInstall');
                $this->reload = true;
            } elseif ($res > InstallCode::OK) {
                $mapper             = new ValidationMapper();
                $this->errorMessage = \sprintf(
                    \__('Error during the installation. Error code %d - %s'),
                    $res,
                    $mapper->map($res)
                );
            }
        }
        $this->minify->flushCache();
    }

    private function massAction(): void
    {
        $uninstallErroneous = $this->request->postInt('uninstall') === 1;
        $deleteData         = $this->request->postInt('delete-data', 1) === 1;
        $deleteFiles        = $this->request->postInt('delete-files', 1) === 1;
        foreach (\array_map('\intval', $this->request->post('kPlugin', [])) as $pluginID) {
            if ($this->request->post('aktivieren') !== null) {
                if (\SAFE_MODE) {
                    $this->errorMessage = \__('Safe mode enabled.') . ' - ' . \__('activate');
                    break;
                }
                $res = $this->stateChanger->activate($pluginID);
                switch ($res) {
                    case InstallCode::OK:
                        if ($this->notice !== \__('successPluginActivate')) {
                            $this->notice .= \__('successPluginActivate');
                        }
                        $this->reload = true;
                        $this->minify->flushCache();
                        break;
                    case InstallCode::WRONG_PARAM:
                        $this->errorMessage = \__('errorAtLeastOnePlugin');
                        break;
                    case InstallCode::NO_PLUGIN_FOUND:
                        $this->errorMessage = \__('errorPluginNotFound');
                        break;
                    case InstallCode::DIR_DOES_NOT_EXIST:
                        $this->errorMessage = \__('errorPluginNotFoundFilesystem');
                        break;
                    default:
                        break;
                }

                if ($res > 3) {
                    $mapper             = new ValidationMapper();
                    $this->errorMessage = $mapper->map($res);
                }
            } elseif ($this->request->post('deaktivieren') !== null) {
                $res = $this->stateChanger->deactivate($pluginID);

                switch ($res) {
                    case InstallCode::OK: // Alles O.K. Plugin wurde deaktiviert
                        if ($this->notice !== \__('successPluginDeactivate')) {
                            $this->notice .= \__('successPluginDeactivate');
                        }
                        $this->reload = true;
                        $this->minify->flushCache();
                        break;
                    case InstallCode::WRONG_PARAM: // $kPlugin wurde nicht uebergeben
                        $this->errorMessage = \__('errorAtLeastOnePlugin');
                        break;
                    case InstallCode::NO_PLUGIN_FOUND: // SQL Fehler bzw. Plugin nicht gefunden
                        $this->errorMessage = \__('errorPluginNotFound');
                        break;
                }
            } elseif ($this->request->post('deinstallieren') !== null || $uninstallErroneous) {
                $plugin = $this->db->select('tplugin', 'kPlugin', $pluginID);
                $ok     = false;
                if ($plugin !== null && $plugin->kPlugin > 0) {
                    switch ($this->uninstaller->uninstall($pluginID, false, null, $deleteData, $deleteFiles)) {
                        case InstallCode::WRONG_PARAM:
                            $this->errorMessage = \__('errorAtLeastOnePlugin');
                            break;
                        case InstallCode::SQL_ERROR:
                            $this->errorMessage = \__('errorPluginDeleteSQL');
                            break;
                        case InstallCode::NO_PLUGIN_FOUND:
                            $this->errorMessage = \__('errorPluginNotFound');
                            break;
                        case InstallCode::OK:
                        default:
                            $ok           = true;
                            $this->notice = \__('successPluginDelete');
                            $this->reload = true;
                            $this->minify->flushCache();
                            break;
                    }
                } else {
                    $this->errorMessage = \__('errorPluginNotFoundMultiple');
                }
                if ($ok === false && $uninstallErroneous === true && $deleteFiles === true) {
                    $this->delete();
                }
            } elseif ($this->request->post('reload') !== null) {
                $plugin = $this->db->select('tplugin', 'kPlugin', $pluginID);
                if ($plugin !== null && $plugin->kPlugin > 0) {
                    $loader = (int)$plugin->bExtension === 1
                        ? new PluginLoader($this->db, $this->cache)
                        : new LegacyPluginLoader($this->db, $this->cache);
                    $res    = $this->stateChanger->reload($loader->init((int)$plugin->kPlugin), true);
                    if ($res === InstallCode::OK || $res === InstallCode::OK_LEGACY) {
                        $this->notice = \__('successPluginRefresh');
                        $this->reload = true;
                    } else {
                        $this->errorMessage = \__('errorPluginRefresh');
                    }
                } else {
                    $this->errorMessage = \__('errorPluginNotFoundMultiple');
                }
            }
        }
        $this->cache->flushTags([
            \CACHING_GROUP_CORE,
            \CACHING_GROUP_LANGUAGE,
            \CACHING_GROUP_LICENSES,
            \CACHING_GROUP_PLUGIN,
            \CACHING_GROUP_BOX
        ]);
    }
}
