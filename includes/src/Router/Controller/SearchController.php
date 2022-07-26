<?php declare(strict_types=1);

namespace JTL\Router\Controller;

use JTL\Router\DefaultParser;
use JTL\Router\State;
use JTL\Smarty\JTLSmarty;
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
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        $this->getStateFromSlug($args);

        return parent::getResponse($request, $args, $smarty);
    }
}
