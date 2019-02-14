<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Sitemap\SchemaRenderers;

/**
 * Class DefaultSchemaRenderer
 * @package JTL\Sitemap\SchemaRenderers
 */
final class DefaultSchemaRenderer extends AbstractSchemaRenderer
{
    /**
     * @param string[] $sitemapFiles
     * @return string
     */
    public function buildIndex(array $sitemapFiles): string
    {
        $xml  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach ($sitemapFiles as $url) {
            $xml .= "<sitemap>\n<loc>" . $url . "</loc>\n";
            if ($this->config['sitemap']['sitemap_insert_lastmod'] === 'Y') {
                $xml .= '<lastmod>' . \date('Y-m-d') . '</lastmod>' . "\n";
            }
            $xml .= '</sitemap>' . "\n";
        }
        $xml .= '</sitemapindex>' . "\n";

        return $xml;
    }

    /**
     * @return string
     */
    public function buildHeader(): string
    {
        $xml  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"';
        if ($this->config['sitemap']['sitemap_googleimage_anzeigen'] === 'Y') {
            $xml .= ' xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"';
        }

        $xml .= ' xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"' . "\n";
        $xml .= '  xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9' . "\n";
        $xml .= '  http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">' . "\n";

        return $xml;
    }

    /**
     * @return string
     */
    public function buildFooter(): string
    {
        return '</urlset>';
    }
}
