<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use JTL\Helpers\Request;
use JTL\Smarty\JTLSmarty;
use League\Route\Route;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class PriceHistoryController
 * @package JTL\Router\Controller\Backend
 */
class PriceHistoryController extends AbstractBackendController
{
    public function getResponse(
        ServerRequestInterface $request,
        array $args,
        JTLSmarty $smarty,
        Route $route
    ): ResponseInterface {
        $this->smarty = $smarty;
        $this->checkPermissions('MODULE_PRICECHART_VIEW');
        $this->getText->loadAdminLocale('pages/preisverlauf');
        if (Request::postInt('einstellungen') === 1) {
            \saveAdminSectionSettings(\CONF_PREISVERLAUF, $_POST);
        }
        \getAdminSectionSettings(\CONF_PREISVERLAUF);

        return $smarty->assign('route', $route->getPath())
            ->getResponse('preisverlauf.tpl');
    }
}
