<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Sitemap\Factories;

use DB\DbInterface;

/**
 * Class AbstractFactory
 * @package Sitemap\Generators
 */
abstract class AbstractFactory implements FactoryInterface
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
     * AbstractFactory constructor.
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
