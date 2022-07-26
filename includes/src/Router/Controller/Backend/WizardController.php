<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use JTL\Backend\AuthToken;
use JTL\Backend\Permissions;
use JTL\Backend\Wizard\Controller;
use JTL\Backend\Wizard\DefaultFactory;
use JTL\Helpers\Request;
use JTL\Router\Route;
use JTL\Session\Backend;
use JTL\Smarty\JTLSmarty;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class WizardController
 * @package JTL\Router\Controller\Backend
 */
class WizardController extends AbstractBackendController
{
    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        $this->smarty = $smarty;
        $this->getText->loadAdminLocale('pages/wizard');
        $factory      = new DefaultFactory(
            $this->db,
            $this->getText,
            $this->alertService,
            $this->account
        );
        $controller   = new Controller($factory, $this->db, $this->cache, $this->getText);
        $token        = AuthToken::getInstance($this->db);
        $valid        = $token->isValid();
        $authRedirect = $valid && Backend::get('wizard-authenticated')
            ? Backend::get('wizard-authenticated')
            : false;

        Backend::set('redirectedToWizard', true);
        if (Request::getVar('action') === 'auth') {
            Backend::set('wizard-authenticated', Request::getVar('wizard-authenticated'));
            $token->requestToken(
                Backend::get('jtl_token'),
                $this->baseURL . '/' . Route::CODE . '/wizard'
            );
        }
        unset($_SESSION['wizard-authenticated']);
        $this->checkPermissions(Permissions::WIZARD_VIEW);

        return $smarty->assign('steps', $controller->getSteps())
            ->assign('authRedirect', $authRedirect)
            ->assign('hasAuth', $valid)
            ->assign('route', $this->route)
            ->getResponse('wizard.tpl');
    }
}
