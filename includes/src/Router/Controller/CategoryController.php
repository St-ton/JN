<?php declare(strict_types=1);

namespace JTL\Router\Controller;

use JTL\Router\DefaultParser;
use JTL\Router\State;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use League\Route\RouteGroup;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class CategoryController
 * @package JTL\Router\Controller
 */
class CategoryController extends ProductListController
{
    /**
     * @var string
     */
    protected string $tseoSelector = 'kKategorie';

    /**
     * @param int $id
     * @param int $languageID
     * @return State
     */
    protected function handleSeoError(int $id, int $languageID): State
    {
        if ($id > 0) {
            $exists = $this->db->getSingleObject(
                'SELECT kKategorie
                    FROM tkategorie
                    WHERE kKategorie = :cid',
                ['cid' => $id]
            );
            if ($exists !== null) {
                $seo = (object)[
                    'cSeo'     => '',
                    'cKey'     => 'kKategorie',
                    'kKey'     => $id,
                    'kSprache' => $languageID
                ];

                return $this->updateState($seo, $seo->cSeo);
            }
        }
        $this->state->is404 = true;

        return $this->updateProductFilter();
    }

    /**
     * @inheritdoc
     */
    public function register(RouteGroup $route, string $dynName): void
    {
        $name = \SLUG_ALLOW_SLASHES ? 'name:.+' : 'name';
        $route->get('/' . \ROUTE_PREFIX_CATEGORIES . '/id/{id:\d+}', [$this, 'getResponse'])
            ->setName('ROUTE_CATEGORY_BY_ID' . $dynName);
        $route->get('/' . \ROUTE_PREFIX_CATEGORIES . '/{' . $name . '}', [$this, 'getResponse'])
            ->setName('ROUTE_CATEGORY_BY_NAME' . $dynName);
        $route->post('/' . \ROUTE_PREFIX_CATEGORIES . '/id/{id:\d+}', [$this, 'getResponse'])
            ->setName('ROUTE_CATEGORY_BY_ID' . $dynName . 'POST');
        $route->post('/' . \ROUTE_PREFIX_CATEGORIES . '/{' . $name . '}', [$this, 'getResponse'])
            ->setName('ROUTE_CATEGORY_BY_NAME' . $dynName . 'POST');
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
