<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Sitemap\Config;

use Sitemap\Factories\FactoryInterface;

/**
 * Interface ConfigInterface
 * @package Sitemap
 */
interface ConfigInterface
{
    /**
     * @return FactoryInterface[]
     */
    public function getFactories(): array;
}
