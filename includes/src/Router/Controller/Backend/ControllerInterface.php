<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use JTL\Smarty\JTLSmarty;
use League\Route\Route;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Interface ControllerInterface
 * @package JTL\Router\Controller\Backend
 */
interface ControllerInterface
{
    /**
     * @return bool
     */
    public function init(): bool;

    /**
     * @param ServerRequestInterface $request
     * @param array                  $args
     * @param JTLSmarty              $smarty
     * @param Route                  $route
     * @return ResponseInterface
     */
    public function getResponse(
        ServerRequestInterface $request,
        array $args,
        JTLSmarty $smarty,
        Route $route
    ): ResponseInterface;

    /**
     * @param ServerRequestInterface $request
     * @param array                  $args
     * @param JTLSmarty              $smarty
     * @param Route                  $route
     * @return ResponseInterface
     */
    public function notFoundResponse(
        ServerRequestInterface $request,
        array $args,
        JTLSmarty $smarty,
        Route $route
    ): ResponseInterface;
}
