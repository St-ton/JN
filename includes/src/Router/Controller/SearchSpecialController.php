<?php declare(strict_types=1);

namespace JTL\Router\Controller;

use JTL\Router\State;
use JTL\Smarty\JTLSmarty;
use League\Route\RouteGroup;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class SearchSpecialController
 * @package JTL\Router\Controller
 */
class SearchSpecialController extends ProductListController
{
    /**
     * @var string
     */
    protected string $tseoSelector = 'suchspecial';

    /**
     * @inheritdoc
     */
    protected function handleSeoError(int $id, int $languageID): State
    {
        $this->state->is404 = true;

        return $this->updateProductFilter();
    }

    /**
     * @inheritdoc
     */
    public function register(RouteGroup $route, string $dynName): void
    {
        $name = \SLUG_ALLOW_SLASHES ? 'name:.+' : 'name';
        $route->get('/' . \ROUTE_PREFIX_SEARCHSPECIALS . '/id/{id:\d+}', $this->getResponse(...))
            ->setName('ROUTE_SEARCHSPECIAL_BY_ID' . $dynName);
        $route->get('/' . \ROUTE_PREFIX_SEARCHSPECIALS . '/{' . $name . '}', $this->getResponse(...))
            ->setName('ROUTE_SEARCHSPECIAL_BY_NAME' . $dynName);
        $route->post('/' . \ROUTE_PREFIX_SEARCHSPECIALS . '/id/{id:\d+}', $this->getResponse(...))
            ->setName('ROUTE_SEARCHSPECIAL_BY_ID' . $dynName . 'POST');
        $route->post('/' . \ROUTE_PREFIX_SEARCHSPECIALS . '/{' . $name . '}', $this->getResponse(...))
            ->setName('ROUTE_SEARCHSPECIAL_BY_NAME' . $dynName . 'POST');
    }

    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        $this->getStateFromSlug($args);
        if (!$this->init()) {
            return $this->notFoundResponse($request, $args, $smarty);
        }

        return parent::getResponse($request, $args, $smarty);
    }
}
