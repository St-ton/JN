<?php declare(strict_types=1);

namespace JTL\Router\Middleware;

use Exception;
use JTL\DB\DbInterface;
use JTL\REST\Models\ApiKeyModel;
use JTL\REST\Permissions;
use JTL\Shop;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class ApiKeyMiddleware
 * @package JTL\Router\Middleware
 */
class ApiKeyMiddleware implements MiddlewareInterface
{
    /**
     * @param DbInterface $db
     */
    public function __construct(private readonly DbInterface $db)
    {
    }

    /**
     * @param ServerRequestInterface $request
     * @return bool
     */
    private function validateApiKey(ServerRequestInterface $request): bool
    {
        return true;
        $key = $request->getHeader('x-api-key')[0] ?? null;
        if ($key === null) {
            return false;
        }
        try {
            $model = ApiKeyModel::loadByAttributes(['key' => $key], $this->db);

            return (new Permissions($model->getPermissions()))->methodAllowed($request->getMethod());
        } catch (Exception) {
            return false;
        }
    }

    /**
     * @inheritdoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!$this->validateApiKey($request)) {
            return new JsonResponse('Invalid api key', 403);
        }
        return $handler->handle($request);
    }
}
