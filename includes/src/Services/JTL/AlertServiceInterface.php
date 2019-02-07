<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Services\JTL;

use Alert;
use Tightenco\Collect\Support\Collection;

/**
 * Class AlertService
 */
interface AlertServiceInterface
{
    /**
     * @return void
     */
    public function initFromSession(): void;

    /**
     * @param string $type
     * @param string $message
     * @param string $key
     * @param array|null $options
     * @return Alert|null
     */
    public function addAlert(string $type, string $message, string $key, array $options = null): ?Alert;

    /**
     * @param string $key
     * @return null|Alert
     */
    public function getAlert(string $key): ?Alert;

    /**
     * @return Collection
     */
    public function getAlertlist(): Collection;

    /**
     * @param string $type
     * @return bool
     */
    public function alertTypeExists(string $type): bool;

    /**
     * @param string $key
     */
    public function displayAlertByKey(string $key): void;

    /**
     * @param string $key
     */
    public function removeAlertByKey(string $key): void;
}
