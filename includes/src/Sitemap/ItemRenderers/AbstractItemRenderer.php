<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Sitemap\ItemRenderers;

use JTL\Sitemap\Items\ItemInterface;

/**
 * Class AbstractItemRenderer
 * @package JTL\Sitemap\ItemRenderers
 */
abstract class AbstractItemRenderer implements RendererInterface
{
    /**
     * @var int
     */
    protected $lastID;

    /**
     * @var ItemInterface[]
     */
    protected $queue = [];

    /**
     * @var array
     */
    protected $config;

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

    /**
     * @inheritdoc
     */
    public function flush(): string
    {
        return '';
    }
}
