<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Sitemap\Factories;

use DB\DbInterface;

/**
 * Class AbstractGenerator
 * @package Sitemap\Generators
 */
abstract class AbstractGenerator implements GeneratorInterface
{
    /**
     * @var DbInterface
     */
    protected $db;

    /**
     * @var array
     */
    protected $config;

    /**
     * AbstractGenerator constructor.
     * @param DbInterface $db
     * @param array       $config
     */
    public function __construct(DbInterface $db, array $config)
    {
        $this->db     = $db;
        $this->config = $config;
    }
}
