<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use JTL\Backend\Permissions;
use JTL\Emailhistory;
use JTL\Helpers\Form;
use JTL\Helpers\GeneralObject;
use JTL\Pagination\Pagination;
use JTL\Smarty\JTLSmarty;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class EmailHistoryController
 * @package JTL\Router\Controller\Backend
 */
class EmailHistoryController extends AbstractBackendController
{
    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        $this->smarty = $smarty;
        $this->checkPermissions(Permissions::EMAILHISTORY_VIEW);
        $this->getText->loadAdminLocale('pages/emailhistory');

        $step    = 'uebersicht';
        $history = new Emailhistory();
        $action  = (isset($_POST['a']) && Form::validateToken()) ? $_POST['a'] : '';
        if ($action === 'delete') {
            if (isset($_POST['remove_all'])) {
                if ($history->deleteAll() === 0) {
                    $this->alertService->addError(\__('errorHistoryDelete'), 'errorHistoryDelete');
                }
            } elseif (GeneralObject::hasCount('kEmailhistory', $_POST)) {
                $history->deletePack($_POST['kEmailhistory']);
                $this->alertService->addSuccess(\__('successHistoryDelete'), 'successHistoryDelete');
            } else {
                $this->alertService->addError(\__('errorSelectEntry'), 'errorSelectEntry');
            }
        }

        $pagination = (new Pagination('emailhist'))
            ->setItemCount($history->getCount())
            ->assemble();

        return $smarty->assign('pagination', $pagination)
            ->assign('oEmailhistory_arr', $history->getAll(' LIMIT ' . $pagination->getLimitSQL()))
            ->assign('step', $step)
            ->assign('route', $this->route)
            ->getResponse('emailhistory.tpl');
    }
}
