<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use JTL\Backend\Permissions;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use JTL\Update\MigrationManager;
use JTL\Update\Updater;
use JTLShop\SemVer\Version;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class DBUpdateController
 * @package JTL\Router\Controller\Backend
 */
class DBUpdateController extends AbstractBackendController
{
    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        $this->smarty = $smarty;
        $this->checkPermissions(Permissions::SHOP_UPDATE_VIEW);
        $this->getText->loadAdminLocale('pages/dbupdater');
        $smarty->clearCompiledTemplate();
        $updater             = new Updater($this->db);
        $template            = Shop::Container()->getTemplateService()->getActiveTemplate(false);
        $fileVersion         = $updater->getCurrentFileVersion();
        $hasMinUpdateVersion = true;
        if (!$updater->hasMinUpdateVersion()) {
            $this->alertService->addWarning(
                $updater->getMinUpdateVersionError(),
                'errorMinShopVersionRequired'
            );
            $hasMinUpdateVersion = false;
        }
        if ((int)($_SESSION['disabledPlugins'] ?? 0) > 0) {
            $this->alertService->addWarning(
                \sprintf(
                    \__('%d plugins were disabled for compatibility reasons. '
                        . 'Please check your installed plugins manually.'),
                    (int)$_SESSION['disabledPlugins']
                ),
                'errorMinShopVersionRequired'
            );
            unset($_SESSION['disabledPlugins']);
        }
        if ((bool)($_SESSION['maintenance_forced'] ?? false) === true) {
            $this->db->update('teinstellungen', 'cName', 'wartungsmodus_aktiviert', (object)['cWert' => 'N']);
            $this->cache->flushTags([\CACHING_GROUP_OPTION]);
        }

        return $smarty->assign('updatesAvailable', $updater->hasPendingUpdates())
            ->assign('manager', ADMIN_MIGRATION ? new MigrationManager($this->db) : null)
            ->assign('isPluginManager', false)
            ->assign('migrationURL', $this->baseURL . $this->route)
            ->assign('currentFileVersion', $fileVersion)
            ->assign('currentDatabaseVersion', $updater->getCurrentDatabaseVersion())
            ->assign('hasDifferentVersions', !Version::parse($fileVersion)->equals(Version::parse($fileVersion)))
            ->assign('version', $updater->getVersion())
            ->assign('updateError', $updater->error())
            ->assign('currentTemplateFileVersion', $template->getFileVersion())
            ->assign('currentTemplateDatabaseVersion', $template->getVersion())
            ->assign('hasMinUpdateVersion', $hasMinUpdateVersion)
            ->assign('route', $this->route)
            ->getResponse('dbupdater.tpl');
    }
}
