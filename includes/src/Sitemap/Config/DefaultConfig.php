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
        $res = [
            new Base($this->db, $this->config, $this->baseURL, $this->baseImageURL),
//            new Product($this->db, $this->config, $this->baseURL, $this->baseImageURL)
        ];
        if ($this->config['sitemap']['sitemap_kategorien_anzeigen'] === 'Y') {
            $res[] = new Category($this->db, $this->config, $this->baseURL, $this->baseImageURL);
        }
        if ($this->config['sitemap']['sitemap_hersteller_anzeigen'] === 'Y') {
            $res[] = new Manufacturer($this->db, $this->config, $this->baseURL, $this->baseImageURL);
        }
        if ($this->config['sitemap']['sitemap_tags_anzeigen'] === 'Y') {
            $res[] = new Attribute($this->db, $this->config, $this->baseURL, $this->baseImageURL);
        }
        if ($this->config['sitemap']['sitemap_tags_anzeigen'] === 'Y') {
            $res[] = new Tag($this->db, $this->config, $this->baseURL, $this->baseImageURL);
        }
        if ($this->config['sitemap']['sitemap_livesuche_anzeigen'] === 'Y') {
            $res[] = new LiveSearch($this->db, $this->config, $this->baseURL, $this->baseImageURL);
        }
        if ($this->config['sitemap']['sitemap_seiten_anzeigen'] === 'Y') {
            $res[] = new Page($this->db, $this->config, $this->baseURL, $this->baseImageURL);
        }
        if ($this->config['sitemap']['sitemap_newskategorien_anzeigen'] === 'Y') {
            $res[] = new NewsCategory($this->db, $this->config, $this->baseURL, $this->baseImageURL);
        }
        if ($this->config['sitemap']['sitemap_news_anzeigen'] === 'Y') {
            $res[] = new NewsItem($this->db, $this->config, $this->baseURL, $this->baseImageURL);
        }

        return $res;
    }
}
