<?php declare(strict_types=1);

namespace JTL\Router\Controller;

use JTL\Shop;
use Psr\Http\Message\ResponseInterface;

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
    public function getResponse(): ResponseInterface
    {
        Shop::setPageType(\PAGE_WARTUNG);
        $this->preRender();

        return $this->smarty->getResponse('snippets/maintenance.tpl')->withStatus(503);
    }
}
