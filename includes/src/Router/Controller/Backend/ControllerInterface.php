<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use JTL\Smarty\JTLSmarty;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Interface ControllerInterface
 * @package JTL\Router\Controller\Backend
 */
interface ControllerInterface
{
    /**
     * @return void
     */
    public function init(): void;

    /**
     * @param ServerRequestInterface $request
     * @param array                  $args
     * @param JTLSmarty              $smarty
     * @return ResponseInterface
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface;

    /**
     * @param ServerRequestInterface $request
     * @param array                  $args
     * @param JTLSmarty              $smarty
     * @return ResponseInterface
     */
    public function notFoundResponse(
        ServerRequestInterface $request,
        array                  $args,
        JTLSmarty              $smarty
    ): ResponseInterface;
}
