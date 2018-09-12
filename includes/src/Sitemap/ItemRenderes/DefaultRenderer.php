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
