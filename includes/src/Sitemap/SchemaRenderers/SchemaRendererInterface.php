<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Sitemap\SchemaRenderers;

/**
 * Interface SchemaRendererInterface
 * @package Sitemap\SchemaRenderers
 */
interface SchemaRendererInterface
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
     * @param string[] $sitemapFiles
     * @return string
     */
    public function buildIndexSchema(array $sitemapFiles): string;

    /**
     * @return string
     */
    public function buildXMLHeader(): string;

    /**
     * @return string
     */
    public function buildXMLFooter(): string;
}
