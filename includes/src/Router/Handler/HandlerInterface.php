<?php declare(strict_types=1);

namespace JTL\Router\Handler;

use JTL\DB\DbInterface;
use JTL\Router\State;
use JTL\Smarty\JTLSmarty;
use Laminas\Diactoros\ServerRequest;
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
     * @param ServerRequest $request
     * @param array         $args
     * @return State
     */
    public function getStateFromRequest(ServerRequest $request, array $args): State;

    /**
     * @param ServerRequest $request
     * @param array         $args
     * @param JTLSmarty     $smarty
     * @return string
     */
    public function handle(ServerRequest $request, array $args, JTLSmarty $smarty): string;

    /**
     * @param stdClass $seo
     * @param string   $slug
     * @return State
     */
    public function updateState(stdClass $seo, string $slug): State;
}
