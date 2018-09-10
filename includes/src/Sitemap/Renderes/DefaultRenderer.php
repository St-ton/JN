<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Sitemap\Renderes;

use Sitemap\Items\ItemInterface;

/**
 * Class DefaultRenderer
 * @package Sitemap\Renderes
 */
class DefaultRenderer implements RendererInterface
{
    /**
     * @var string
     */
    protected $baseURL;

    /**
     * DefaultRenderer constructor.
     * @param string $baseURL
     */
    public function __construct(string $baseURL)
    {
        $this->baseURL = $baseURL;
    }

    /**
     * @inheritdoc
     */
    public function renderItem(ItemInterface $item): string
    {
        $res = "  <url>\n" .
            '     <loc>' . $this->baseURL . $item->getLocation() . "</loc>\n";
        if (!empty($item->getImage())) {
            $res .=
                "     <image:image>\n" .
                '        <image:loc>' . $item->getImage() . "</image:loc>\n" .
                "     </image:image>\n";
        }
        if (!empty($item->getLastModificationTime())) {
            $res .= '     <lastmod>' . $item->getLastModificationTime() . "</lastmod>\n";
        }
        if (!empty($item->getChangeFreq())) {
            $res .= '     <changefreq>' . $item->getChangeFreq() . "</changefreq>\n";
        }
        if (!empty($item->getPriority())) {
            $res .= '     <priority>' . $item->getPriority() . "</priority>\n";
        }
        $res .= "  </url>\n";

        return $res;
    }
}
