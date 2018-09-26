<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Sitemap\Factories;

use DB\DbInterface;

/**
 * Interface FactoryInterface
 * @package Sitemap\Generators
 */
interface FactoryInterface
{
    /**
     * FactoryInterface constructor.
     * @param DbInterface $db
     * @param array       $config
     * @param string      $baseURL
     * @param string      $baseImageURL
     */
    public function __construct(DbInterface $db, array $config, string $baseURL, string $baseImageURL);

    /**
     * @param array $languages
     * @param array $customerGroups
     * @return \Generator
     */
    public function getCollection(array $languages, array $customerGroups): \Generator;
}
