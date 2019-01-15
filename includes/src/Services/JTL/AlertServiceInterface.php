<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Services\JTL;

use Alert;

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
     * @return Alert
     */
    public function addAlert(string $type, string $message, string $key, array $options = null): Alert;

    /**
     * @param string $key
     * @return null|Alert
     */
    public function getAlert(string $key): ?Alert;

    /**
     * @return array
     */
    public function getAlertlist(): array;

    /**
     * @param string $type
     * @return bool
     */
    public function alertTypeExists(string $type): bool;

    /**
     * @param string $key
     */
    public function displayAlertByKey(string $key): void;
}
