<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use JTL\Helpers\Request;
use JTL\Review\ReviewAdminController;
use JTL\Smarty\JTLSmarty;
use League\Route\Route;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class ReviewController
 * @package JTL\Router\Controller\Backend
 */
class ReviewController extends AbstractBackendController
{
    public function getResponse(
        ServerRequestInterface $request,
        array $args,
        JTLSmarty $smarty,
        Route $route
    ): ResponseInterface {
        $this->smarty = $smarty;
        $this->checkPermissions('MODULE_VOTESYSTEM_VIEW');
        $this->getText->loadAdminLocale('pages/bewertung');

        $this->setzeSprache();
        $controller = new ReviewAdminController($this->db, $this->cache, $this->alertService, $this->smarty);
        $tab        = mb_strlen(Request::verifyGPDataString('tab')) > 0
            ? Request::verifyGPDataString('tab')
            : 'freischalten';
        $step       = $controller->handleRequest();
        if ($step === 'bewertung_editieren' || Request::getVar('a') === 'editieren') {
            $step = 'bewertung_editieren';
            $smarty->assign('review', $controller->getReview(Request::verifyGPCDataInt('kBewertung')));
            if (Request::verifyGPCDataInt('nFZ') === 1) {
                $smarty->assign('nFZ', 1);
            }
        } elseif ($step === 'bewertung_uebersicht') {
            $controller->getOverview();
        }

        return $smarty->assign('step', $step)
            ->assign('cTab', $tab)
            ->assign('route', $route->getPath())
            ->getResponse('bewertung.tpl');
    }
}
