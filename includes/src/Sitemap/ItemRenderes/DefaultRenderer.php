<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Sitemap\ItemRenderes;

use Sitemap\Items\ItemInterface;

/**
 * Class DefaultRenderer
 * @package Sitemap\ItemRenderes
 */
final class DefaultRenderer implements RendererInterface
{
    /**
     * @var array
     */
    private $config;

    /**
     * @inheritdoc
     */
    public function renderItem(ItemInterface $item): string
    {
        $res = "  <url>\n" .
            '     <loc>' . $item->getLocation() . "</loc>\n";
        if (!empty($item->getImage())) {
            $res .=
                "     <image:image>\n" .
                '        <image:loc>' . $item->getImage() . "</image:loc>\n" .
                "     </image:image>\n";
        }
        if ($this->config['sitemap']['sitemap_insert_lastmod'] === 'Y' && !empty($item->getLastModificationTime())) {
            $res .= '     <lastmod>' . $item->getLastModificationTime() . "</lastmod>\n";
        }
        if ($this->config['sitemap']['sitemap_insert_changefreq'] === 'Y' && !empty($item->getChangeFreq())) {
            $res .= '     <changefreq>' . $item->getChangeFreq() . "</changefreq>\n";
        }
        if ($this->config['sitemap']['sitemap_insert_priority'] === 'Y' && !empty($item->getPriority())) {
            $res .= '     <priority>' . $item->getPriority() . "</priority>\n";
        }
        $res .= "  </url>\n";

        return $res;
    }

    /**
     * @return string
     */
    public function buildXMLHeader(): string
    {
        $head = '<?xml version="1.0" encoding="UTF-8"?>
            <urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"';

        if ($this->config['sitemap']['sitemap_googleimage_anzeigen'] === 'Y') {
            $head .= ' xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"';
        }

        $head .= ' xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9
            http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">';

        return $head;
    }

    /**
     * @return array
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * @param array $config
     */
    public function setConfig(array $config): void
    {
        $this->config = $config;
    }
}
