<?php declare(strict_types=1);

namespace JTL\Router\Handler;

use JTL\DB\DbInterface;
use JTL\Router\State;
use JTL\Smarty\JTLSmarty;
use Laminas\Diactoros\ServerRequest;
use League\Route\Route;
use Psr\Http\Message\ResponseInterface;
use stdClass;

/**
 * Interface HandlerInterface
 * @package JTL\Router\Handler
 */
interface HandlerInterface
{
    /**
     * @param DbInterface $db
     * @param State       $state
     */
    public function __construct(DbInterface $db, State $state);

    /**
     * @param array $args
     * @return State
     */
    public function getStateFromSlug(array $args): State;

    /**
     * @param ServerRequest $request
     * @param array         $args
     * @param JTLSmarty     $smarty
     * @param Route         $route
     * @return ResponseInterface
     */
    public function handle(ServerRequest $request, array $args, JTLSmarty $smarty, Route $route): ResponseInterface;

    /**
     * @param stdClass $seo
     * @param string   $slug
     * @return State
     */
    public function updateState(stdClass $seo, string $slug): State;
}
