<?php declare(strict_types=1);

namespace JTL\Router\Controller;

use JTL\Link\SpecialPageNotFoundException;
use JTL\Router\ControllerFactory;
use JTL\Router\State;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class RootController
 * @package JTL\Router\Controller
 */
class RootController extends AbstractController
{
    /**
     * @inheritdoc
     */
    public function getStateFromSlug(array $args): State
    {
        try {
            $home = Shop::Container()->getLinkService()->getSpecialPage(\LINKTYP_STARTSEITE);
        } catch (SpecialPageNotFoundException) {
            return $this->state;
        }
        $this->state->pageType = \PAGE_STARTSEITE;
        $this->state->linkType = \LINKTYP_STARTSEITE;

        return $this->state->type !== ''
            ? $this->updateProductFilter()
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
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        $this->getStateFromSlug($args);
        $factory    = new ControllerFactory($this->state, $this->db, $this->cache, $smarty);
        $controller = $factory->getEntryPoint();
        if (!$controller->init()) {
            return $controller->notFoundResponse($request, $args, $smarty);
        }

        return $controller->getResponse($request, $args, $smarty);
    }
}
