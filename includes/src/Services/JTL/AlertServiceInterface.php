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
    public function addAlert(string $variant, string $message, string $type, string $key): void;

    public function setErrorAlert(string $variant, string $message): AlertService;

    public function setNoticeAlert(string $variant, string $message): AlertService;

    public function addCustomAlert(string $variant, string $message, string $key): AlertService;

    public function getErrorAlert(): ?\Alert;

    public function getNoticeAlert(): ?\Alert;

    public function getCustomAlert(string $key): ?\Alert;
}
