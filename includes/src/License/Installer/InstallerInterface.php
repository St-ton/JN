<?php declare(strict_types=1);

namespace JTL\License\Installer;

use JTL\License\AjaxResponse;

/**
 * Interface InstallerInterface
 * @package JTL\License\Installer
 */
interface InstallerInterface
{
    /**
     * @param string       $itemID
     * @param string       $downloadedArchive
     * @param AjaxResponse $response
     * @return int
     */
    public function update(string $itemID, string $downloadedArchive, AjaxResponse $response): int;

    /**
     * @param string       $itemID
     * @param string       $downloadedArchive
     * @param AjaxResponse $response
     * @return int
     */
    public function install(string $itemID, string $downloadedArchive, AjaxResponse $response): int;
}
