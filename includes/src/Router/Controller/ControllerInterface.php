<?php declare(strict_types=1);

namespace JTL\Router\Controller;

use JTL\Smarty\JTLSmarty;

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
     * @return void
     */
    public function handleState(JTLSmarty $smarty): void;

    /**
     * @param JTLSmarty $smarty
     * @return string
     */
    public function getResponse(JTLSmarty $smarty): string;

    /**
     * @param JTLSmarty $smarty
     * @return string
     */
    public function notFoundResponse(JTLSmarty $smarty): string;
}
