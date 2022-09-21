<?php declare(strict_types=1);

namespace JTL\REST\Controllers;

use JTL\Cache\JTLCacheInterface;
use JTL\DB\DbInterface;
use JTL\REST\Models\TaxRateModel;
use League\Fractal\Manager;
use League\Route\RouteGroup;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class TaxRateController
 * @package JTL\REST\Controllers
 */
class TaxRateController extends AbstractController
{
    /**
     * TaxRateController constructor.
     * @inheritdoc
     */
    public function __construct(Manager $fractal, protected DbInterface $db, protected JTLCacheInterface $cache)
    {
        parent::__construct(TaxRateModel::class, $fractal, $this->db, $this->cache);
    }

    /**
     * @inheritdoc
     */
    public function registerRoutes(RouteGroup $routeGroup): void
    {
        $routeGroup->get('/taxrate', [$this, 'index']);
        $routeGroup->get('/taxrate/{id}', [$this, 'show']);
        $routeGroup->put('/taxrate/{id}', [$this, 'update']);
        $routeGroup->post('/taxrate', [$this, 'create']);
        $routeGroup->delete('/taxrate/{id}', [$this, 'delete']);
    }

    /**
     * @inheritdoc
     */
    protected function createRequestValidationRules(ServerRequestInterface $request): array
    {
        return [
            'id'         => 'required|integer',
            'zoneID'     => 'required|integer',
            'taxClassID' => 'required|integer',
            'rate'       => 'required|numeric',
            'priority'   => 'integer'
        ];
    }
}
