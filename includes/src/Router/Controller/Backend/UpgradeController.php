<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use JTL\Backend\Permissions;
use JTL\Backend\Upgrade\Channels;
use JTL\Backend\Upgrade\Checker;
use JTL\Backend\Upgrade\ReleaseDownloader;
use JTL\Backend\Upgrade\Upgrader;
use JTL\Filesystem\Filesystem;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use JTLShop\SemVer\Version;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class UpgradeController
 * @package JTL\Router\Controller\Backend
 * @since 5.3.0
 */
class UpgradeController extends AbstractBackendController
{
    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        $this->smarty = $smarty;
        $this->checkPermissions(Permissions::UPGRADE);
        $this->getText->loadAdminLocale('pages/upgrade');
        $this->smarty->assign('logs', [])
            ->assign('errors', []);

        if ($request->getMethod() === 'POST' && Form::validateToken()) {
            if (Request::postInt('upgrade') === 1) {
                $this->upgrade(Request::postInt('newerversions'));
            } elseif (Request::postInt('rollback') === 1) {
                $this->rollback(Request::postInt('backups'));
            }
        }
        $this->assignReleaseData();
        $this->assignLogData();

        return $this->smarty->getResponse('upgrade.tpl');
    }

    private function assignLogData(): void
    {
        $this->smarty->assign('upgrade_log', $this->db->selectAll('upgrade_log', [], []));
    }

    private function assignReleaseData(): void
    {
        $activeChannel     = Channels::getActiveChannel($this->db);
        $releaseDownloader = new ReleaseDownloader($this->db);
        $this->smarty->assign('channels', Channels::getChannels())
            ->assign('activeChannel', $activeChannel)
            ->assign('availableVersions', $releaseDownloader->getReleases($activeChannel))
            ->assign('currentVersion', Version::parse(\APPLICATION_VERSION));
    }

    private function upgrade(int $requestedID): void
    {
        $release = (new ReleaseDownloader($this->db))->getReleaseByID($requestedID);
        if ($release === null) {
            return;
        }
        $upgrader = new Upgrader(
            $this->db,
            $this->cache,
            Shop::Container()->get(Filesystem::class),
            $this->smarty
        );
        $upgrader->upgradeByRelease($release);
        $this->smarty->assign('logs', $upgrader->getLogs())
            ->assign('errors', $upgrader->getErrors());
    }

    private function rollback(int $id): void
    {
        $backup = $this->db->select('upgrade_log', 'id', $id);
        if ($backup === null || $backup->backup_db === null || $backup->backup_fs === null) {
            return;
        }
        $upgrader = new Upgrader(
            $this->db,
            $this->cache,
            Shop::Container()->get(Filesystem::class),
            $this->smarty
        );
        $upgrader->rollback($backup->backup_db, $backup->backup_fs);
        $this->smarty->assign('logs', $upgrader->getLogs())
            ->assign('errors', $upgrader->getErrors());
    }
}
