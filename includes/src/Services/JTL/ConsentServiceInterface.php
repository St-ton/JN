<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Services\JTL;

/**
 * Interface ConsentServiceInterface
 * @package JTL\Services\JTL
 */
interface ConsentServiceInterface
{
    public function register();

    public function hasConsent(): bool;
}
