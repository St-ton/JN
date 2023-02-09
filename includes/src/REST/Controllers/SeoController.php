<?php declare(strict_types=1);

namespace JTL\REST\Controllers;

use Exception;
use JTL\Cache\JTLCacheInterface;
use JTL\DB\DbInterface;
use JTL\Model\DataModelInterface;
use JTL\REST\Models\SeoModel;
use League\Fractal\Manager;
use League\Route\RouteGroup;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class SeoController
 * @package JTL\REST\Controllers
 * @todo: table has no primary keys, models cannot be uniquely loaded
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
     * @param ServerRequestInterface $request
     * @param array                  $params
     * @return ResponseInterface
     */
    public function show(ServerRequestInterface $request, array $params): ResponseInterface
    {
        $id = (int)($params['id'] ?? 0);
        try {
            $class    = $this->modelClass;
            $instance = (new $class($this->db));
            if (\property_exists($instance, 'full')) {
                $instance->full = $this->full;
            }
            /** @var $class DataModelInterface */
            $result = $instance->init(['cKey' => $id], DataModelInterface::ON_NOTEXISTS_FAIL);
        } catch (Exception $e) {
            return $this->sendNotFoundResponse();
        }
        return $this->respondWithModel($result);
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
