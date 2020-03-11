<?php

namespace JTL\Services\JTL;

use JTL\Smarty\JTLSmarty;

/**
 * Interface CaptchaService
 * @package JTL\Services\JTL
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
     * @param JTLSmarty $smarty
     * @return string
     */
    public function getHeadMarkup($smarty): string;

    /**
     * @param JTLSmarty $smarty
     * @return string
     */
    public function getBodyMarkup($smarty): string;

    /**
     * @param  array $requestData
     * @return bool
     */
    public function validate(array $requestData): bool;
}
