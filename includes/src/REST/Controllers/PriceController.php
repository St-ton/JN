<?php declare(strict_types=1);

namespace JTL\REST\Controllers;

use JTL\Cache\JTLCacheInterface;
use JTL\DB\DbInterface;
use JTL\REST\Models\PriceModel;
use League\Fractal\Manager;
use League\Route\RouteGroup;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class PriceController
 * @package JTL\REST\Controllers
 */
class PriceController extends AbstractController
{
    /**
     * PriceController constructor.
     * @inheritdoc
     */
    public function __construct(Manager $fractal, protected DbInterface $db, protected JTLCacheInterface $cache)
    {
        parent::__construct(PriceModel::class, $fractal, $this->db, $this->cache);
    }

    /**
     * @inheritdoc
     */
    public function registerRoutes(RouteGroup $routeGroup): void
    {
        $routeGroup->get('/price', $this->index(...));
        $routeGroup->get('/price/{id}', $this->show(...));
        $routeGroup->put('/price/{id}', $this->update(...));
        $routeGroup->post('/price', $this->create(...));
        $routeGroup->delete('/price/{id}', $this->delete(...));
    }

    /**
     * @inheritdoc
     */
    protected function createRequestValidationRules(ServerRequestInterface $request): array
    {
        return [
            'id'         => 'integer',
            'productID'  => 'required|integer',
            'customerID' => 'integer'
        ];
    }

    /**
     * @inheritdoc
     */
    protected function updateRequestValidationRules(ServerRequestInterface $request): array
    {
        return [
            'id'         => 'required|integer',
            'productID'  => 'required|integer',
            'customerID' => 'integer'
        ];
    }
}
