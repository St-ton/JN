<?php declare(strict_types=1);

namespace JTL\Router\Handler;

use JTL\Router\AbstractHandler;
use JTL\Router\Controller\PageController;
use JTL\Router\State;
use JTL\Session\Frontend;
use JTL\Shop;
use JTL\Shopsetting;
use JTL\Smarty\JTLSmarty;
use Laminas\Diactoros\ServerRequest;

/**
 * Class RootHandler
 * @package JTL\Router\Handler
 */
class RootHandler extends AbstractHandler
{
    /**
     * @inheritdoc
     */
    public function getStateFromRequest(ServerRequest $request, array $args): State
    {
        $home = Shop::Container()->getLinkService()->getSpecialPage(\LINKTYP_STARTSEITE);
        if ($home === null) {
            return $this->state;
        }
        $this->state->pageType = \PAGE_STARTSEITE;
        $this->state->linkType = \LINKTYP_STARTSEITE;

        return $this->state->type !== ''
            ? $this->state
            : $this->updateState(
                (object)[
                    'cSeo'     => $home->getSEO(),
                    'kLink'    => $home->getID(),
                    'kKey'     => $home->getID(),
                    'cKey'     => 'kLink',
                    'kSprache' => $home->getLanguageID()
                ],
                $home->getSEO()
            );
    }

    /**
     * @inheritdoc
     */
    public function handle(ServerRequest $request, array $args, JTLSmarty $smarty): string
    {
        $this->getStateFromRequest($request, $args);
        $controller = new PageController(
            $this->db,
            $this->state,
            Frontend::getCustomer()->getGroupID(),
            Shopsetting::getInstance()->getAll(),
            Shop::Container()->getAlertService()
        );
        if (!$controller->init()) {
            return $controller->notFoundResponse($smarty);
        }

        return $controller->getResponse($smarty);
    }
}
