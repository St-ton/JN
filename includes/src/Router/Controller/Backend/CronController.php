<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use JTL\Backend\Permissions;
use JTL\Cron\Admin\Controller;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class CronController
 * @package JTL\Router\Controller\Backend
 */
class CronController extends AbstractBackendController
{
    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        $this->smarty = $smarty;
        $this->checkPermissions(Permissions::CRON_VIEW);
        $this->getText->loadAdminLocale('pages/cron');

        $admin    = Shop::Container()->get(Controller::class);
        $deleted  = 0;
        $updated  = 0;
        $inserted = 0;
        $tab      = 'overview';
        if (Form::validateToken()) {
            if (isset($_POST['reset'])) {
                $updated = $admin->resetQueueEntry(Request::postInt('reset'));
            } elseif (isset($_POST['delete'])) {
                $deleted = $admin->deleteQueueEntry(Request::postInt('delete'));
            } elseif (Request::postInt('add-cron') === 1) {
                $inserted = $admin->addQueueEntry($_POST);
                $tab      = 'add-cron';
            } elseif (Request::postVar('a') === 'saveSettings') {
                $tab = 'settings';
                $this->saveAdminSectionSettings(\CONF_CRON, $_POST);
            }
        }
        $this->getAdminSectionSettings(\CONF_CRON);

        return $smarty->assign('jobs', $admin->getJobs())
            ->assign('deleted', $deleted)
            ->assign('updated', $updated)
            ->assign('inserted', $inserted)
            ->assign('available', $admin->getAvailableCronJobs())
            ->assign('tab', $tab)
            ->assign('route', $this->route)
            ->getResponse('cron.tpl');
    }
}
