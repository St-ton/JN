<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use JTL\Backend\Permissions;
use JTL\Smarty\JTLSmarty;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class PriceHistoryController
 * @package JTL\Router\Controller\Backend
 */
class PriceHistoryController extends AbstractBackendController
{
    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        $this->checkPermissions(Permissions::MODULE_PRICECHART_VIEW);
        $this->getText->loadAdminLocale('pages/preisverlauf');
        if ($this->request->postInt('einstellungen') === 1) {
            $this->saveAdminSectionSettings(\CONF_PREISVERLAUF, $this->request->getBody());
        }
        $this->getAdminSectionSettings(\CONF_PREISVERLAUF);

        return $this->smarty->getResponse('preisverlauf.tpl');
    }
}
