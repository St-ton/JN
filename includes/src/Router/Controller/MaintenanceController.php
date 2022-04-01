<?php declare(strict_types=1);

namespace JTL\Router\Controller;

use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use Psr\Http\Message\ResponseInterface;

/**
 * Class MaintenanceController
 * @package JTL\Router\Controller
 */
class MaintenanceController extends AbstractController
{
    public function init(): bool
    {
        parent::init();

        return true;
    }

    public function getResponse(JTLSmarty $smarty): ResponseInterface
    {
        Shop::setPageType(\PAGE_WARTUNG);
        $this->preRender($smarty);

        return $smarty->getResponse('snippets/maintenance.tpl')->withStatus(503);
    }
}
