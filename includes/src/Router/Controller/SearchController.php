<?php declare(strict_types=1);

namespace JTL\Router\Controller;

use JTL\Router\State;
use JTL\Smarty\JTLSmarty;
use League\Route\RouteGroup;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class SearchController
 * @package JTL\Router\Controller
 */
class SearchController extends ProductListController
{
    /**
     * @inheritdoc
     */
    public function getStateFromSlug(array $args): State
    {
        $query = $args['query'] ?? null;
        if ($query !== null) {
            $this->state->searchQuery = \urldecode($query);
        }

        return $this->updateProductFilter();
    }

    /**
     * @inheritdoc
     */
    public function register(RouteGroup $route, string $dynName): void
    {
        $route->get('/' . \ROUTE_PREFIX_SEARCH . '[/{query:.+}]', [$this, 'getResponse'])
            ->setName('ROUTE_SEARCH' . $dynName);
        $route->post('/' . \ROUTE_PREFIX_SEARCH . '[/{query:.+}]', [$this, 'getResponse'])
            ->setName('ROUTE_SEARCH' . $dynName . 'POST');
    }

    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        $this->getStateFromSlug($args);

        return parent::getResponse($request, $args, $smarty);
    }
}
