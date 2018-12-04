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
     * @return Alert
     */
    public function addAlert(string $type, string $message, string $key): Alert;

    /**
     * @param string $key
     * @return null|Alert
     */
    public function getAlert(string $key): ?Alert;

    /**
     * @return array
     */
    public function getAlertlist(): array;

}
