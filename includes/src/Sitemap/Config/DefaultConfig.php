<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Sitemap\Config;

use DB\DbInterface;
use Sitemap\Factories\Attribute;
use Sitemap\Factories\Base;
use Sitemap\Factories\Category;
use Sitemap\Factories\LiveSearch;
use Sitemap\Factories\Manufacturer;
use Sitemap\Factories\NewsCategory;
use Sitemap\Factories\NewsItem;
use Sitemap\Factories\Page;
use Sitemap\Factories\Product;
use Sitemap\Factories\Tag;

/**
 * Class DefaultConfig
 * @package Sitemap
 */
final class DefaultConfig implements ConfigInterface
{
    /**
     * @var DbInterface
     */
    private $db;

    /**
     * @var array
     */
    private $config;

    /**
     * @var string
     */
    private $baseURL;

    /**
     * @var string
     */
    private $baseImageURL;

    /**
     * DefaultConfig constructor.
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

    /**
     * @inheritdoc
     */
    public function getFactories(): array
    {
        return [
            new Base($this->db, $this->config, $this->baseURL, $this->baseImageURL),
            new Product($this->db, $this->config, $this->baseURL, $this->baseImageURL),
            new Page($this->db, $this->config, $this->baseURL, $this->baseImageURL),
            new Category($this->db, $this->config, $this->baseURL, $this->baseImageURL),
            new Tag($this->db, $this->config, $this->baseURL, $this->baseImageURL),
            new Manufacturer($this->db, $this->config, $this->baseURL, $this->baseImageURL),
            new LiveSearch($this->db, $this->config, $this->baseURL, $this->baseImageURL),
            new Attribute($this->db, $this->config, $this->baseURL, $this->baseImageURL),
            new NewsItem($this->db, $this->config, $this->baseURL, $this->baseImageURL),
            new NewsCategory($this->db, $this->config, $this->baseURL, $this->baseImageURL)
        ];
    }
}
