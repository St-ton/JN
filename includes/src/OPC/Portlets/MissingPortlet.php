<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace OPC\Portlets;

use OPC\Portlet;
use OPC\PortletInstance;
use Plugin\Extension;

/**
 * Class MissingPortlet
 * @package OPC\Portlets
 */
class MissingPortlet extends Portlet
{
    /**
     * @var string
     */
    protected $missingClass = '';

    /**
     * @var null|Extension
     */
    protected $inactivePlugin = null;

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
     * @return Extension|null
     */
    public function getInactivePlugin(): ?Extension
    {
        return $this->inactivePlugin;
    }

    /**
     * @param Extension|null $inactivePlugin
     * @return MissingPortlet
     */
    public function setInactivePlugin(?Extension $inactivePlugin): MissingPortlet
    {
        $this->inactivePlugin = $inactivePlugin;

        return $this;
    }
}
