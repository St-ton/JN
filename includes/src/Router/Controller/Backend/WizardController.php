<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use JTL\Backend\AuthToken;
use JTL\Backend\Wizard\Controller;
use JTL\Backend\Wizard\DefaultFactory;
use JTL\Helpers\Request;
use JTL\License\Admin;
use JTL\License\Checker;
use JTL\License\Manager;
use JTL\Session\Backend;
use JTL\Shop;
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

        $checker      = new Checker(Shop::Container()->getLogService(), $this->db, $this->cache);
        $manager      = new Manager($this->db, $this->cache);
        $admin        = new Admin($manager, $this->db, $this->cache, $checker);
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

        if (Request::postVar('action') === 'code') {
            $admin->handleAuth();
        } elseif (Request::getVar('action') === 'auth') {
            Backend::set('wizard-authenticated', Request::getVar('wizard-authenticated'));
            $token->requestToken(
                Backend::get('jtl_token'),
                Shop::getAdminURL() . $this->route . '?action=code'
            );
        }
        if (Request::postVar('action') !== 'code') {
            unset($_SESSION['wizard-authenticated']);
            $this->checkPermissions('WIZARD_VIEW');

            return $smarty->assign('steps', $controller->getSteps())
                ->assign('authRedirect', $authRedirect)
                ->assign('hasAuth', $valid)
                ->assign('route', $this->route)
                ->getResponse('wizard.tpl');
        }
    }
}
