<?php declare(strict_types=1);

namespace JTL\REST\Controllers;

use JTL\Cache\JTLCacheInterface;
use JTL\DB\DbInterface;
use JTL\REST\Models\TaxClassModel;
use League\Fractal\Manager;
use League\Route\RouteGroup;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class TaxRateController
 * @package JTL\REST\Controllers
 */
class TaxClassController extends AbstractController
{
    /**
     * TaxClassController constructor.
     * @inheritdoc
     */
    public function __construct(Manager $fractal, protected DbInterface $db, protected JTLCacheInterface $cache)
    {
        parent::__construct(TaxClassModel::class, $fractal, $this->db, $this->cache);
    }

    /**
     * @inheritdoc
     */
    public function registerRoutes(RouteGroup $routeGroup): void
    {
        $routeGroup->get('/taxclass', [$this, 'index']);
        $routeGroup->get('/taxclass/{id}', [$this, 'show']);
        $routeGroup->put('/taxclass/{id}', [$this, 'update']);
        $routeGroup->post('/taxclass', [$this, 'create']);
        $routeGroup->delete('/taxclass/{id}', [$this, 'delete']);
    }

    /**
     * @inheritdoc
     */
    protected function createRequestValidationRules(ServerRequestInterface $request): array
    {
        return [
            'id'        => 'required|integer',
            'name'      => 'required|max:255'
        ];
    }

    /**
     * @inheritdoc
     */
    protected function updateRequestValidationRules(ServerRequestInterface $request): array
    {
        return [
            'id'        => 'integer',
            'name'      => 'required|max:255'
        ];
    }
}
