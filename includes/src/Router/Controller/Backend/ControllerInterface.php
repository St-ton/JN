<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use JTL\Router\RequestParser;
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
     * @return ResponseInterface
     */
    public function getResponse(ServerRequestInterface $request, array $args): ResponseInterface;

    /**
     * @param ServerRequestInterface $request
     * @param array                  $args
     * @return ResponseInterface
     */
    public function notFoundResponse(ServerRequestInterface $request, array $args): ResponseInterface;

    /**
     * @param ServerRequestInterface $request
     * @param JTLSmarty              $smarty
     * @return RequestParser
     */
    public function initController(ServerRequestInterface $request, JTLSmarty $smarty): RequestParser;
}
