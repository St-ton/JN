<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use Exception;
use JTL\Backend\DirManager;
use JTL\Backend\Permissions;
use JTL\Backend\Settings\Manager;
use JTL\Backend\Settings\SectionFactory;
use JTL\Backend\Settings\Sections\SectionInterface;
use JTL\Backend\Upgrade\Channels;
use JTL\Backend\Upgrade\ReleaseDownloader;
use JTL\Backend\Upgrade\Upgrader;
use JTL\Filesystem\Filesystem;
use JTL\Helpers\Form;
use JTL\Helpers\GeneralObject;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Minify\MinifyService;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use JTL\Update\MigrationManager;
use JTLShop\SemVer\Version;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use stdClass;

/**
 * Class CacheController
 * @package JTL\Router\Controller\Backend
 */
class UpgradeController extends AbstractBackendController
{
    /**
     * @var string
     */
    private string $tab = 'uebersicht';



    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        $this->smarty = $smarty;

        $this->checkPermissions(Permissions::OBJECTCACHE_VIEW);
        $this->getText->loadAdminLocale('pages/upgrade');

        $releaseDownloader = new ReleaseDownloader($smarty);
        $releaseDownloader->getReleases();
        if ($request->getMethod() === 'POST' && Form::validateToken()) {
            if (Request::postInt('upgrade') === 1) {
                $requestedID = Request::postInt('newerversions');
                $release = $releaseDownloader->getReleaseByID($requestedID);
                if ($release !== null) {
                    $upgrader = new Upgrader(
                        $this->db,
                        $this->cache,
                        Shop::Container()->get(Filesystem::class),
                        $smarty
                    );
                    $upgrader->upgradeByRelease($release);
                }
            }
        }

        return $this->smarty->getResponse('upgrade.tpl');
    }
}
