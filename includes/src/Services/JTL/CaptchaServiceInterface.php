<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 * @package       jtl-shop
 * @since         5.0
 */

namespace Services\JTL;

/**
 * Interface CaptchaService
 * @package Services\JTL
 */
interface CaptchaServiceInterface
{
    /**
     * @return bool
     */
    public function isConfigured(): bool;

    /**
     * @return bool
     */
    public function isEnabled(): bool;

    /**
     * @param \JTLSmarty $smarty
     * @return string
     */
    public function getHeadMarkup($smarty): string;

    /**
     * @param \JTLSmarty $smarty
     * @return string
     */
    public function getBodyMarkup($smarty): string;

    /**
     * @param  array $requestData
     * @return bool
     */
    public function validate(array $requestData): bool;
}
