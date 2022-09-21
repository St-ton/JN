<?php declare(strict_types=1);

namespace JTL\REST\Controllers;

use JTL\Cache\JTLCacheInterface;
use JTL\DB\DbInterface;
use JTL\REST\Models\ProductAttributeModel;
use League\Fractal\Manager;
use League\Route\RouteGroup;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class ProductAttributeController
 * @package JTL\REST\Controllers
 */
class ProductAttributeController extends AbstractController
{
    /**
     * ProductAttributeController constructor.
     * @inheritdoc
     */
    public function __construct(Manager $fractal, protected DbInterface $db, protected JTLCacheInterface $cache)
    {
        parent::__construct(ProductAttributeModel::class, $fractal, $this->db, $this->cache);
    }

    /**
     * @inheritdoc
     */
    public function registerRoutes(RouteGroup $routeGroup): void
    {
        $routeGroup->get('/productattribute', [$this, 'index']);
        $routeGroup->get('/productattribute/{id}', [$this, 'show']);
        $routeGroup->put('/productattribute/{id}', [$this, 'update']);
        $routeGroup->post('/productattribute', [$this, 'create']);
        $routeGroup->delete('/productattribute/{id}', [$this, 'delete']);
    }

    /**
     * @inheritdoc
     */
    protected function createRequestValidationRules(ServerRequestInterface $request): array
    {
        return [
            'id'        => 'required|integer',
            'productID' => 'required|integer',
            'name'      => 'required|max:255',
            'cWert'     => 'required'
        ];
    }

    /**
     * @inheritdoc
     */
    protected function updateRequestValidationRules(ServerRequestInterface $request): array
    {
        return [
            'id'   => 'integer',
            'name' => 'required|max:255'
        ];
    }
}
