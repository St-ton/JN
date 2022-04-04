<?php declare(strict_types=1);

namespace JTL\Router\Controller;

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
     * @return ResponseInterface
     */
    public function getResponse(): ResponseInterface;

    /**
     * @return ResponseInterface
     */
    public function notFoundResponse(): ResponseInterface;
}
