<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Sitemap\Renderes;

use Sitemap\Items\ItemInterface;

/**
 * Interface RendererInterface
 * @package Sitemap\Renderes
 */
interface RendererInterface
{
    /**
     * @param ItemInterface $item
     * @return string
     */
    public function renderItem(ItemInterface $item): string;
}
