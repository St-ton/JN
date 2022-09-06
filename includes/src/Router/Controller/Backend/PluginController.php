<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use InvalidArgumentException;
use JTL\Backend\Permissions;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Plugin\Admin\InputType;
use JTL\Plugin\Admin\Installation\MigrationManager;
use JTL\Plugin\Admin\Markdown;
use JTL\Plugin\Data\Config;
use JTL\Plugin\Helper;
use JTL\Plugin\LoaderInterface;
use JTL\Plugin\Plugin;
use JTL\Plugin\PluginInterface;
use JTL\Plugin\State;
use JTL\Smarty\JTLSmarty;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class PluginController
 * @package JTL\Router\Controller\Backend
 */
class PluginController extends AbstractBackendController
{
    /**
     * @var bool
     */
    private bool $updated = false;

    /**
     * @var bool
     */
    private bool $hasError = false;

    /**
     * @var bool
     */
    private bool $invalidateCache = false;

    /**
     * @var bool
     */
    private bool $pluginNotFound = false;

    /**
     * @var string
     */
    private string $notice = '';

    /**
     * @var string
     */
    private string $error = '';

    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        $this->smarty = $smarty;
        $this->checkPermissions(Permissions::PLUGIN_ADMIN_VIEW);
        $this->getText->loadAdminLocale('pages/plugin');

        $pluginID    = (int)$args['id'];
        $plugin      = null;
        $loader      = null;
        $activeTab   = -1;
        $this->step  = 'plugin_uebersicht';
        $this->route = \str_replace('{id}', (string)$pluginID, $this->route);
        $this->smarty->assign('hasDifferentVersions', false)
            ->assign('currentDatabaseVersion', 0)
            ->assign('currentFileVersion', 0)
            ->assign('pluginBackendURL', $this->baseURL . $this->route)
            ->assign('route', $this->route);

        if (\SAFE_MODE) {
            $this->alertService->addWarning(\__('Safe mode enabled.'), 'warnSafeMode');
        }
        if ($pluginID <= 0) {
            return $this->smarty->assign('oPlugin', $plugin)
                ->assign('step', $this->step)
                ->assign('pluginNotFound', true)
                ->getResponse('plugin.tpl');
        }

        if (Request::verifyGPCDataInt('Setting') === 1) {
            $this->actionConfig($pluginID);
        }
        if (Request::verifyGPCDataInt('kPluginAdminMenu') > 0) {
            $activeTab = Request::verifyGPCDataInt('kPluginAdminMenu');
        }
        if (\mb_strlen(Request::verifyGPDataString('cPluginTab')) > 0) {
            $activeTab = Request::verifyGPDataString('cPluginTab');
        }
        $this->smarty->assign('defaultTabbertab', $activeTab);
        $loader = $loader ?? Helper::getLoaderByPluginID($pluginID, $this->db, $this->cache);
        global $plugin, $oPlugin;
        if ($loader !== null) {
            try {
                $plugin = $loader->init($pluginID, $this->invalidateCache);
            } catch (InvalidArgumentException) {
                $this->pluginNotFound = true;
            }
        }
        if ($plugin !== null) {
            $oPlugin = $plugin;
            if (ADMIN_MIGRATION && $plugin instanceof Plugin) {
                $this->getText->loadAdminLocale('pages/dbupdater');
                $manager    = new MigrationManager(
                    $this->db,
                    $plugin->getPaths()->getBasePath() . \PFAD_PLUGIN_MIGRATIONS,
                    $plugin->getPluginID(),
                    $plugin->getMeta()->getSemVer()
                );
                $migrations = \count($manager->getMigrations());
                $this->smarty->assign('manager', $manager)
                    ->assign('updatesAvailable', $migrations > \count($manager->getExecutedMigrations()));
            }
            $this->smarty->assign('oPlugin', $plugin);
            if ($this->updated === true) {
                \executeHook(\HOOK_PLUGIN_SAVE_OPTIONS, [
                    'plugin'   => $plugin,
                    'hasError' => &$this->hasError,
                    'msg'      => &$this->notice,
                    'error'    => $this->error,
                    'options'  => $plugin->getConfig()->getOptions()
                ]);
            }
            $this->renderMenu($plugin, $loader);
        }
        $this->alertService->addNotice($this->notice, 'pluginNotice');
        $this->alertService->addError($this->error, 'pluginError');
        if ($plugin !== null && $plugin->getState() === State::DISABLED) {
            $this->alertService->addWarning(\__('pluginIsDeactivated'), 'pluginIsDeactivated');
        }

        return $this->smarty->assign('oPlugin', $plugin)
            ->assign('step', $this->step)
            ->assign('pluginNotFound', $this->pluginNotFound || $plugin === null)
            ->getResponse('plugin.tpl');
    }

    /**
     * @param int $pluginID
     * @return void
     */
    private function actionConfig(int $pluginID): void
    {
        $this->updated = true;
        if (!Form::validateToken()) {
            $this->hasError = true;
        } else {
            $plgnConf = isset($_POST['kPluginAdminMenu'])
                ? $this->db->getObjects(
                    "SELECT *
                    FROM tplugineinstellungenconf
                    WHERE kPluginAdminMenu != 0
                        AND kPlugin = :plgn
                        AND cConf != 'N'
                        AND kPluginAdminMenu = :kpm",
                    ['plgn' => $pluginID, 'kpm' => Request::postInt('kPluginAdminMenu')]
                )
                : [];
            foreach ($plgnConf as $current) {
                if ($current->cInputTyp === InputType::NONE) {
                    continue;
                }
                $this->db->delete(
                    'tplugineinstellungen',
                    ['kPlugin', 'cName'],
                    [$pluginID, $current->cWertName]
                );
                $upd = (object)[
                    'kPlugin' => $pluginID,
                    'cName'   => $current->cWertName,
                    'cWert'   => null
                ];
                if (isset($_POST[$current->cWertName])) {
                    if (\is_array($_POST[$current->cWertName])) {
                        if ($current->cConf === Config::TYPE_DYNAMIC) {
                            // selectbox with "multiple" attribute
                            $upd->cWert = \serialize($_POST[$current->cWertName]);
                        } else {
                            // radio buttons
                            $upd->cWert = $_POST[$current->cWertName][0];
                        }
                    } else {
                        // textarea/text
                        $upd->cWert = $_POST[$current->cWertName];
                    }
                }
                if (!$this->db->insert('tplugineinstellungen', $upd)) {
                    $this->hasError = true;
                }
                $this->invalidateCache = true;
            }
        }
        if ($this->hasError) {
            $this->error = \__('errorConfigSave');
        } else {
            $this->notice = \__('successConfigSave');
        }
        $loader = Helper::getLoaderByPluginID($pluginID, $this->db, $this->cache);
        try {
            $plugin = $loader->init($pluginID, $this->invalidateCache);
        } catch (InvalidArgumentException) {
            $this->pluginNotFound = true;
            $plugin               = null;
        }
        if ($plugin !== null && $plugin->isBootstrap()) {
            Helper::updatePluginInstance($plugin);
        }
    }

    /**
     * @param PluginInterface $plugin
     * @param LoaderInterface $loader
     * @return void
     */
    private function renderMenu(PluginInterface $plugin, LoaderInterface $loader): void
    {
        foreach ($plugin->getAdminMenu()->getItems() as $menu) {
            if ($menu->isMarkdown === true) {
                $markdown = new Markdown();
                $markdown->setImagePrefixURL($plugin->getPaths()->getBaseURL());
                $content    = $markdown->text(Text::convertUTF8(\file_get_contents($menu->file)));
                $menu->html = $this->smarty->assign('content', $content)->fetch($menu->tpl);
            } elseif ($menu->configurable === false) {
                if (\SAFE_MODE) {
                    $menu->html = \__('Safe mode enabled.');
                } elseif ($menu->file !== '' && \file_exists($plugin->getPaths()->getAdminPath() . $menu->file)) {
                    \ob_start();
                    $oPlugin = $plugin;
                    $smarty  = $this->getSmarty();
                    require $plugin->getPaths()->getAdminPath() . $menu->file;
                    $menu->html = \ob_get_clean();
                } elseif (!empty($menu->tpl) && $menu->kPluginAdminMenu === -1) {
                    if (isset($menu->data)) {
                        $this->smarty->assign('data', $menu->data);
                    }
                    $menu->html = $this->smarty->fetch($menu->tpl);
                } elseif ($plugin->isBootstrap() === true) {
                    $menu->html = Helper::bootstrap($plugin->getID(), $loader)
                        ?->renderAdminMenuTab($menu->name, $menu->id, $this->smarty);
                }
            } elseif ($menu->configurable === true) {
                $hidden = true;
                foreach ($plugin->getConfig()->getOptions() as $confItem) {
                    if ($confItem->inputType !== InputType::NONE
                        && $confItem->confType !== Config::TYPE_NOT_CONFIGURABLE
                    ) {
                        $hidden = false;
                        break;
                    }
                }
                if ($hidden) {
                    $plugin->getAdminMenu()->removeItem($menu->kPluginAdminMenu);
                    continue;
                }
                $this->smarty->assign('oPluginAdminMenu', $menu);
                $menu->html = $this->smarty->fetch('tpl_inc/plugin_options.tpl');
            }
        }
    }
}
