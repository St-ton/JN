<?php declare(strict_types=1);

namespace JTL\REST\Controllers;

use JTL\Cache\JTLCacheInterface;
use JTL\DB\DbInterface;
use League\Fractal\Manager;
use League\Route\RouteGroup;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class CacheController
 * @package JTL\REST\Controllers
 */
class CacheController extends AbstractController
{
    /**
     * CacheController constructor.
     * @inheritdoc
     */
    public function __construct(Manager $fractal, protected DbInterface $db, protected JTLCacheInterface $cache)
    {
        parent::__construct('null', $fractal, $this->db, $this->cache);
    }

    /**
     * @inheritdoc
     */
    public function registerRoutes(RouteGroup $routeGroup): void
    {
        $routeGroup->delete('cache/all', $this->deleteAll(...));
        $routeGroup->delete('cache/{id}', $this->delete(...));
        $routeGroup->delete('cache', $this->deleteTag(...));
    }

    /**
     * @return ResponseInterface
     */
    public function deleteAll(): ResponseInterface
    {
        return $this->respondWithArray(['data' => $this->cache->flushAll()]);
    }

    /**
     * @inheritdoc
     */
    public function delete(ServerRequestInterface $request, array $params): ResponseInterface
    {
        return $this->respondWithArray(['data' => $this->cache->flush($params['id'])]);
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function deleteTag(ServerRequestInterface $request): ResponseInterface
    {
        $validatorResponse = $this->validateRequest($request, $this->deleteTagRequestValidationRules());
        if ($validatorResponse !== true) {
            return $this->sendInvalidFieldResponse($validatorResponse);
        }
        $tags = $request->getQueryParams()['tags'];
        foreach ($tags as &$tag) {
            if (\str_starts_with($tag, 'CACHING_GROUP_') && \defined($tag)) {
                $tag = \constant($tag);
            }
        }

        return $this->respondWithArray(['data' => $this->cache->flushTags($tags)]);
    }

    /**
     * @param ServerRequestInterface $request
     * @return array
     */
    protected function deleteRequestValidationRules(ServerRequestInterface $request): array
    {
        return ['id' => 'required'];
    }

    /**
     * @return array
     */
    protected function deleteTagRequestValidationRules(): array
    {
        return ['tags' => 'required|array'];
    }
}
