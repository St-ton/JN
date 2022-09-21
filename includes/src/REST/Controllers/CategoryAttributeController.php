<?php declare(strict_types=1);

namespace JTL\REST\Controllers;

use JTL\Cache\JTLCacheInterface;
use JTL\DB\DbInterface;
use JTL\REST\Models\CategoryAttributeModel;
use League\Fractal\Manager;
use League\Route\RouteGroup;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class CategoryAttributeController
 * @package JTL\REST\Controllers
 */
class CategoryAttributeController extends AbstractController
{
    /**
     * CategoryAttributeController constructor.
     * @inheritdoc
     */
    public function __construct(Manager $fractal, protected DbInterface $db, protected JTLCacheInterface $cache)
    {
        parent::__construct(CategoryAttributeModel::class, $fractal, $this->db, $this->cache);
    }

    /**
     * @inheritdoc
     */
    public function registerRoutes(RouteGroup $routeGroup): void
    {
        $routeGroup->get('categoryattribute', [$this, 'index']);
        $routeGroup->get('categoryattribute/{id}', [$this, 'show']);
        $routeGroup->put('categoryattribute/{id}', [$this, 'update']);
        $routeGroup->post('categoryattribute', [$this, 'create']);
        $routeGroup->delete('categoryattribute/{id}', [$this, 'delete']);
    }

    /**
     * @inheritdoc
     */
    protected function createRequestValidationRules(ServerRequestInterface $request): array
    {
        return [
            'id'         => 'required|integer',
            'categoryID' => 'required|integer',
            'name'       => 'required|max:255',
            'value'      => 'required|max:255'
        ];
    }
}
