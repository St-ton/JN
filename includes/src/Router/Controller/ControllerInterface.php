<?php declare(strict_types=1);

namespace JTL\Router\Controller;

use JTL\Smarty\JTLSmarty;
use Psr\Http\Message\ResponseInterface;

/**
 * Interface ControllerInterface
 * @package JTL\Router\Controller
 */
interface ControllerInterface
{
    /**
     * @return bool
     */
    public function init(): bool;

    /**
     * @param JTLSmarty $smarty
     * @return ResponseInterface
     */
    public function getResponse(JTLSmarty $smarty): ResponseInterface;

    /**
     * @param JTLSmarty $smarty
     * @return ResponseInterface
     */
    public function notFoundResponse(JTLSmarty $smarty): ResponseInterface;
}
