<?php declare(strict_types=1);

namespace JTL\REST\Controllers;

use JTL\Cache\JTLCacheInterface;
use JTL\DB\DbInterface;
use JTL\REST\Models\AttributeModel;
use League\Fractal\Manager;
use League\Route\RouteGroup;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class AttributeController
 * @package JTL\REST\Controllers
 */
class AttributeController extends AbstractController
{
    /**
     * AttributeController constructor.
     * @inheritdoc
     */
    public function __construct(Manager $fractal, protected DbInterface $db, protected JTLCacheInterface $cache)
    {
        parent::__construct(AttributeModel::class, $fractal, $this->db, $this->cache);
    }

    /**
     * @inheritdoc
     */
    public function registerRoutes(RouteGroup $routeGroup): void
    {
        $routeGroup->get('/attribute', $this->index(...));
        $routeGroup->get('/attribute/{id}', $this->show(...));
        $routeGroup->put('/attribute/{id}', $this->update(...));
        $routeGroup->post('/attribute', $this->create(...));
        $routeGroup->delete('/attribute/{id}', $this->delete(...));
    }

    /**
     * @inheritdoc
     */
    protected function createRequestValidationRules(ServerRequestInterface $request): array
    {
        return [
            'id'        => 'required|numeric',
            'productID' => 'required|numeric',
            'name'      => 'required|max:255'
        ];
    }
}
