<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Sitemap\ItemRenderers;

use JTL\Sitemap\Items\ItemInterface;

/**
 * Interface RendererInterface
 * @package Sitemap\ItemRenderers
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

    /**
     * @inheritdoc
     */
    public function flush(): string;
}
