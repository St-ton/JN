<?php declare(strict_types=1);

namespace JTL\Router\Controller;

use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class MaintenanceController
 * @package JTL\Router\Controller
 */
class MaintenanceController extends AbstractController
{
    /**
     * @inheritdoc
     */
    public function init(): bool
    {
        parent::init();

        return true;
    }

    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        $this->smarty = $smarty;
        Shop::setPageType(\PAGE_WARTUNG);
        $this->preRender();

        return $this->smarty->getResponse('snippets/maintenance.tpl')->withStatus(503);
    }
}
