<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Sitemap\Factories;

use DB\DbInterface;
use Tightenco\Collect\Support\Collection;

/**
 * Interface GeneratorInterface
 * @package Sitemap\Generators
 */
interface GeneratorInterface
{
    /**
     * GeneratorInterface constructor.
     * @param DbInterface $db
     * @param array       $config
     */
    public function __construct(DbInterface $db, array $config);

    /**
     * @param array $languages
     * @param array $customerGroups
     * @return Collection
     */
    public function getCollection(array $languages, array $customerGroups): Collection;
}
