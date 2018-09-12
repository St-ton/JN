<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Sitemap\SchemaRenderers;

/**
 * Class AbstractSchemaRenderer
 * @package Sitemap\SchemaRenderers
 */
abstract class AbstractSchemaRenderer implements SchemaRendererInterface
{
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
}
