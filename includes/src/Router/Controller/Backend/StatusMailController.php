<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use JTL\Backend\Permissions;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Smarty\JTLSmarty;
use JTL\Statusmail;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class StatusMailController
 * @package JTL\Router\Controller\Backend
 */
class StatusMailController extends AbstractBackendController
{
    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        $this->smarty = $smarty;
        $this->checkPermissions(Permissions::EMAIL_REPORTS_VIEW);
        $this->getText->loadAdminLocale('pages/statusemail');

        $statusMail = new Statusmail($this->db);
        if (Form::validateToken()) {
            if (Request::postVar('action') === 'sendnow') {
                $statusMail->sendAllActiveStatusMails();
            } elseif (Request::postInt('einstellungen') === 1) {
                if ($statusMail->updateConfig()) {
                    $this->alertService->addSuccess(\__('successChangesSave'), 'successChangesSave');
                } else {
                    $this->alertService->addError(\__('errorConfigSave'), 'errorConfigSave');
                }
            }
        }

        return $smarty->assign('step', 'statusemail_uebersicht')
            ->assign('route', $this->route)
            ->assign('oStatusemailEinstellungen', $statusMail->loadConfig())
            ->getResponse('statusemail.tpl');
    }
}
