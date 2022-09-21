<?php declare(strict_types=1);

namespace JTL\REST\Controllers;

use JTL\Cache\JTLCacheInterface;
use JTL\DB\DbInterface;
use JTL\Language\LanguageModel;
use League\Fractal\Manager;
use League\Route\RouteGroup;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class LanguageController
 * @package JTL\REST\Controllers
 */
class LanguageController extends AbstractController
{
    /**
     * LanguageController constructor.
     * @inheritdoc
     */
    public function __construct(Manager $fractal, protected DbInterface $db, protected JTLCacheInterface $cache)
    {
        parent::__construct(LanguageModel::class, $fractal, $this->db, $this->cache);
    }

    /**
     * @inheritdoc
     */
    public function registerRoutes(RouteGroup $routeGroup): void
    {
        $routeGroup->get('/language', [$this, 'index']);
        $routeGroup->get('/language/{id}', [$this, 'show']);
        $routeGroup->put('/language/{id}', [$this, 'update']);
        $routeGroup->post('/language', [$this, 'create']);
        $routeGroup->delete('/language/{id}', [$this, 'delete']);
    }

    /**
     * @inheritdoc
     */
    protected function createRequestValidationRules(ServerRequestInterface $request): array
    {
        return [
            'id'     => 'required|integer',
            'nameDE' => 'required|max:255',
            'nameEN' => 'required|max:255'
        ];
    }
}
