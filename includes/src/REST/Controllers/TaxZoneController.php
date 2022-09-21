<?php declare(strict_types=1);

namespace JTL\REST\Controllers;

use JTL\Cache\JTLCacheInterface;
use JTL\DB\DbInterface;
use JTL\REST\Models\TaxZoneModel;
use League\Fractal\Manager;
use League\Route\RouteGroup;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class TaxZoneController
 * @package JTL\REST\Controllers
 */
class TaxZoneController extends AbstractController
{
    /**
     * TaxZoneController constructor.
     * @inheritdoc
     */
    public function __construct(Manager $fractal, protected DbInterface $db, protected JTLCacheInterface $cache)
    {
        parent::__construct(TaxZoneModel::class, $fractal, $this->db, $this->cache);
    }

    /**
     * @inheritdoc
     */
    public function registerRoutes(RouteGroup $routeGroup): void
    {
        $routeGroup->get('/taxzone', [$this, 'index']);
        $routeGroup->get('/taxzone/{id}', [$this, 'show']);
        $routeGroup->put('/taxzone/{id}', [$this, 'update']);
        $routeGroup->post('/taxzone', [$this, 'create']);
        $routeGroup->delete('/taxzone/{id}', [$this, 'delete']);
    }

    /**
     * @inheritdoc
     */
    protected function createRequestValidationRules(ServerRequestInterface $request): array
    {
        return [
            'id'   => 'required|integer',
            'name' => 'required|max:255'
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
