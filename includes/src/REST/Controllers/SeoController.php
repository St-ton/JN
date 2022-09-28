<?php declare(strict_types=1);

namespace JTL\REST\Controllers;

use JTL\Cache\JTLCacheInterface;
use JTL\DB\DbInterface;
use JTL\REST\Models\SeoModel;
use League\Fractal\Manager;
use League\Route\RouteGroup;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class SeoController
 * @package JTL\REST\Controllers
 */
class SeoController extends AbstractController
{
    /**
     * SeoController constructor.
     * @inheritdoc
     */
    public function __construct(Manager $fractal, protected DbInterface $db, protected JTLCacheInterface $cache)
    {
        parent::__construct(SeoModel::class, $fractal, $this->db, $this->cache);
    }

    /**
     * @inheritdoc
     */
    public function registerRoutes(RouteGroup $routeGroup): void
    {
        $routeGroup->get('/seo', [$this, 'index']);
        $routeGroup->get('/seo/{id}', [$this, 'show']);
        $routeGroup->put('/seo/{id}', [$this, 'update']);
        $routeGroup->post('/seo', [$this, 'create']);
        $routeGroup->delete('/seo/{id}', [$this, 'delete']);
    }

    /**
     * @inheritdoc
     */
    protected function createRequestValidationRules(ServerRequestInterface $request): array
    {
        return [
            'slug'   => 'required|max:255',
            'type'   => 'required|max:255',
            'id'     => 'required|integer',
            'languageID' => 'required|integer'
        ];
    }

    /**
     * @inheritdoc
     */
    protected function updateRequestValidationRules(ServerRequestInterface $request): array
    {
        return [
            'slug'   => 'required|max:255',
            'type'   => 'required|max:255',
            'languageID' => 'required|integer'
        ];
    }
}
