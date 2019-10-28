<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace JTL\OPC\Portlets\MissingPortlet;

use JTL\OPC\Portlet;
use JTL\OPC\PortletInstance;
use JTL\Plugin\PluginInterface;

/**
 * Class MissingPortlet
 * @package JTL\OPC\Portlets
 */
class MissingPortlet extends Portlet
{
    /**
     * @var string
     */
    protected $missingClass = '';

    /**
     * @var null|PluginInterface
     */
    protected $inactivePlugin;

    /**
     * @param PortletInstance $instance
     * @return string
     * @throws \Exception
     */
    public function getPreviewHtml(PortletInstance $instance): string
    {
        return $this->getPreviewHtmlFromTpl($instance);
    }

    /**
     * @param PortletInstance $instance
     * @return string
     */
    public function getFinalHtml(PortletInstance $instance): string
    {
        return '';
    }

    /**
     * @param PortletInstance $instance
     * @return string
     * @throws \Exception
     */
    public function getConfigPanelHtml(PortletInstance $instance): string
    {
        return $this->getConfigPanelHtmlFromTpl($instance);
    }

    /**
     * @return string
     */
    public function getMissingClass(): string
    {
        return $this->missingClass;
    }

    /**
     * @param string $missingClass
     * @return MissingPortlet
     */
    public function setMissingClass(string $missingClass): self
    {
        $this->missingClass = $missingClass;

        return $this;
    }

    /**
     * @return PluginInterface|null
     */
    public function getInactivePlugin(): ?PluginInterface
    {
        return $this->inactivePlugin;
    }

    /**
     * @param PluginInterface|null $inactivePlugin
     * @return MissingPortlet
     */
    public function setInactivePlugin(?PluginInterface $inactivePlugin): MissingPortlet
    {
        $this->inactivePlugin = $inactivePlugin;

        return $this;
    }
}
