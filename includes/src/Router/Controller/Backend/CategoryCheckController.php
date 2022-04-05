<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use JTL\Backend\Status;
use JTL\Smarty\JTLSmarty;
use League\Route\Route;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class CategoryCheckController
 * @package JTL\Router\Controller\Backend
 */
class CategoryCheckController extends AbstractBackendController
{
    public function getResponse(
        ServerRequestInterface $request,
        array $args,
        JTLSmarty $smarty,
        Route $route
    ): ResponseInterface {
        $this->smarty = $smarty;
        $this->checkPermissions('DIAGNOSTIC_VIEW');
        $this->getText->loadAdminLocale('pages/categorycheck');

        $status             = Status::getInstance($this->db, $this->cache);
        $orphanedCategories = $status->getOrphanedCategories(false);

        return $smarty->assign('passed', count($orphanedCategories) === 0)
            ->assign('cateogries', $orphanedCategories)
            ->getResponse('categorycheck.tpl');
    }
}
