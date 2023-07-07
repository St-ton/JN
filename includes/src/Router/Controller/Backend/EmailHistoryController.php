<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use JTL\Backend\Permissions;
use JTL\Emailhistory;
use JTL\Helpers\GeneralObject;
use JTL\Pagination\Pagination;
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
    public function getResponse(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $this->checkPermissions(Permissions::EMAILHISTORY_VIEW);
        $this->getText->loadAdminLocale('pages/emailhistory');

        $step    = 'uebersicht';
        $history = new Emailhistory();
        $action  = ($this->tokenIsValid && $this->request->post('a') !== null) ? $this->request->post('a') : '';
        if ($action === 'delete') {
            if ($this->request->post('remove_all') !== null) {
                if ($history->deleteAll() === 0) {
                    $this->alertService->addError(\__('errorHistoryDelete'), 'errorHistoryDelete');
                }
            } elseif (GeneralObject::hasCount('kEmailhistory', $this->request->getBody())) {
                $history->deletePack($this->request->post('kEmailhistory'));
                $this->alertService->addSuccess(\__('successHistoryDelete'), 'successHistoryDelete');
            } else {
                $this->alertService->addError(\__('errorSelectEntry'), 'errorSelectEntry');
            }
        }

        $pagination = (new Pagination('emailhist'))
            ->setItemCount($history->getCount())
            ->assemble();

        return $this->smarty->assign('pagination', $pagination)
            ->assign('oEmailhistory_arr', $history->getAll(' LIMIT ' . $pagination->getLimitSQL()))
            ->assign('step', $step)
            ->getResponse('emailhistory.tpl');
    }
}
