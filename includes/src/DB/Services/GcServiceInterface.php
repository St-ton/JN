<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace DB\Services;

/**
 * Interface GcServiceInterface
 * @package DB\Services
 */
interface GcServiceInterface
{
    /**
     * @return $this
     */
    public function run(): GcServiceInterface;
}
