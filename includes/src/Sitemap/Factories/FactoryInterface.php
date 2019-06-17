<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Sitemap\Factories;

use Generator;
use JTL\DB\DbInterface;
use JTL\Language\LanguageModel;

/**
 * Interface FactoryInterface
 * @package JTL\Sitemap\Factories
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
     * @param array           $languages
     * @param LanguageModel[] $customerGroups
     * @return Generator
     */
    public function getCollection(array $languages, array $customerGroups): Generator;
}
