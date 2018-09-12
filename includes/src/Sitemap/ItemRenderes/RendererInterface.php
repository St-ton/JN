<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Sitemap\ItemRenderes;

use Sitemap\Items\ItemInterface;

/**
 * Interface RendererInterface
 * @package Sitemap\ItemRenderes
 */
interface RendererInterface
{
    /**
     * @return array
     */
    public function getConfig(): array;

    /**
     * @param array $config
     */
    public function setConfig(array $config): void;

    /**
     * @param ItemInterface $item
     * @return string
     */
    public function renderItem(ItemInterface $item): string;
}
