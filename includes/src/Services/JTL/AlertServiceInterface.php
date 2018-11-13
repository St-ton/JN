<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Services\JTL;

/**
 * Class LinkService
 * @package Link
 */
interface AlertServiceInterface
{
    public function initFromSession(): void;

    public function addAlert(string $variant, string $message, string $type, string $key): \Alert;

    public function setErrorAlert(string $variant, string $message): \Alert;

    public function setNoticeAlert(string $variant, string $message): \Alert;

    public function addCustomAlert(string $variant, string $message, string $key): \Alert;

    public function getErrorAlert(): ?\Alert;

    public function getNoticeAlert(): ?\Alert;

    public function getCustomAlert(string $key): ?\Alert;

    public function getCustomAlerts(): array;
}
