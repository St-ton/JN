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
     * @var string
     */
    protected $baseURL;

    /**
     * @var string
     */
    protected $baseImageURL;

    /**
     * AbstractGenerator constructor.
     * @param DbInterface $db
     * @param array       $config
     * @param string      $baseURL
     * @param string      $baseImageURL
     */
    public function __construct(DbInterface $db, array $config, string $baseURL, string $baseImageURL)
    {
        $this->db           = $db;
        $this->config       = $config;
        $this->baseURL      = $baseURL;
        $this->baseImageURL = $baseImageURL;
    }
}
