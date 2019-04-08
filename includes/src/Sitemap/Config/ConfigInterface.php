<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Sitemap\Config;

use JTL\Sitemap\Factories\FactoryInterface;

/**
 * Interface ConfigInterface
 * @package JTL\Sitemap\Config
 */
interface ConfigInterface
{
    /**
     * @return FactoryInterface[]
     */
    public function getFactories(): array;
}
