<?php declare(strict_types=1);

namespace JTL\Router\Controller;

use JTL\Customer\AccountController as CustomerAccountController;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;

/**
 * Class AccountController
 * @package JTL\Router\Controller
 */
class AccountController extends AbstractController
{
    public function init(): bool
    {
        parent::init();
        Shop::setPageType($this->state->pageType);

        return true;
    }

    public function handleState(JTLSmarty $smarty): void
    {
        echo $this->getResponse($smarty);
    }

    public function getResponse(JTLSmarty $smarty): string
    {
        require_once PFAD_ROOT . \PFAD_INCLUDES . 'bestellvorgang_inc.php';

        $linkService        = Shop::Container()->getLinkService();
        $controller         = new CustomerAccountController(
            $this->db,
            $this->alertService,
            $linkService,
            $smarty
        );
        $this->canonicalURL = $linkService->getStaticRoute('jtl.php');
        $controller->handleRequest();
        $this->preRender($smarty);
        \executeHook(\HOOK_JTL_PAGE);

        return $smarty->getResponse('account/index.tpl');
    }
}
