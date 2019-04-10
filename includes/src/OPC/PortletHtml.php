<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace JTL\OPC;

use JTL\Plugin\Plugin;
use JTL\Shop;

/**
 * Trait PortletHtml
 * @package JTL\OPC
 */
trait PortletHtml
{
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
     * @throws \Exception
     */
    public function getFinalHtml(PortletInstance $instance): string
    {
        return $this->getFinalHtmlFromTpl($instance);
    }

    /**
     * @param PortletInstance $instance
     * @return string
     * @throws \Exception
     */
    public function getConfigPanelHtml(PortletInstance $instance): string
    {
        return $this->getAutoConfigPanelHtml($instance);
    }

    /**
     * @return string
     */
    public function getButtonHtml(): string
    {
        return '<img alt="" class="fa" src="' . $this->getDefaultIconSvgUrl() . '"><br>' . $this->getTitle();
    }

    /**
     * @param string $faType
     * @return string
     */
    public function getFontAwesomeButtonHtml(string $faType): string
    {
        return '<i class="fa fa-' . $faType . '"></i><br>' . $this->getTitle();
    }

    /**
     * @return string
     */
    final public function getTemplatePath(): string
    {
        $plugin = $this->getPlugin();

        if ($plugin !== null) {
            /** @var Plugin $plugin */
            return $plugin->getPaths()->getPortletsPath() . 'templates/';
        }

        return \PFAD_ROOT . \PFAD_INCLUDES . 'src/OPC/templates/' . $this->getClass() . '/';
    }

    /**
     * @return string
     */
    final public function getTemplateUrl(): string
    {
        $plugin = $this->getPlugin();

        if ($plugin !== null) {
            /** @var Plugin $plugin */
            return $plugin->getPaths()->getPortletsUrl() . 'templates/';
        }

        return Shop::getURL() . '/' . \PFAD_INCLUDES . 'src/OPC/templates/' . $this->getClass() . '/';
    }

    /**
     * @param PortletInstance $instance
     * @return string
     * @throws \Exception
     */
    final protected function getPreviewHtmlFromTpl(PortletInstance $instance): string
    {
        return $this->getHtmlFromTpl($instance, true);
    }

    /**
     * @param PortletInstance $instance
     * @return string
     * @throws \Exception
     */
    final protected function getFinalHtmlFromTpl(PortletInstance $instance): string
    {
        return $this->getHtmlFromTpl($instance, false);
    }

    /**
     * @param PortletInstance $instance
     * @param bool $isPreview
     * @return string
     * @throws \SmartyException
     */
    final protected function getHtmlFromTpl(PortletInstance $instance, bool $isPreview): string
    {
        $smarty  = $isPreview ? \getFrontendSmarty() : Shop::Smarty();
        $tplPath = $this->getTemplatePath() . $this->getClass() . '.tpl';

        if (\file_exists($tplPath) === false) {
            $tplPath = \PFAD_ROOT . \PFAD_INCLUDES . 'src/OPC/templates/OPC/GenericPortlet.tpl';
        }

        return $smarty
            ->assign('isPreview', $isPreview)
            ->assign('portlet', $this)
            ->assign('instance', $instance)
            ->fetch($tplPath);
    }

    /**
     * @param PortletInstance $instance
     * @return string
     * @throws \Exception
     */
    final protected function getConfigPanelHtmlFromTpl(PortletInstance $instance): string
    {
        return Shop::Smarty()
            ->assign('portlet', $this)
            ->assign('instance', $instance)
            ->fetch($this->getTemplatePath() . 'configpanel.tpl');
    }

    /**
     * @param PortletInstance $instance
     * @param string $id
     * @param array $extraAssigns
     * @return string
     * @throws \Exception
     */
    final protected function getConfigPanelSnippet(PortletInstance $instance, $id, $extraAssigns = []): string
    {
        $smarty = Shop::Smarty();

        foreach ($extraAssigns as $name => $val) {
            $smarty->assign($name, $val);
        }

        return $smarty
            ->assign('portlet', $this)
            ->assign('instance', $instance)
            ->fetch(\PFAD_ROOT . \PFAD_INCLUDES . 'src/OPC/templates/OPC/config.' . $id . '.tpl');
    }

    /**
     * @param PortletInstance $instance
     * @return string
     * @throws \SmartyException
     */
    final protected function getAutoConfigPanelHtml(PortletInstance $instance): string
    {
        $desc = $this->getPropertyDesc();
        $tabs = $this->getPropertyTabs();

        foreach ($tabs as $tabname => $propnames) {
            if (\is_string($propnames)) {
                if ($propnames === 'styles') {
                    $tabs[$tabname] = $this->getStylesPropertyDesc();
                } elseif ($propnames === 'animations') {
                    $tabs[$tabname] = $this->getAnimationsPropertyDesc();
                }
            } else {
                foreach ($propnames as $i => $propname) {
                    $tabs[$tabname][$propname] = $desc[$propname];
                    unset($tabs[$tabname][$i], $desc[$propname]);
                }
            }
        }

        if (\count($desc) > 0) {
            $tabs = [__('general') => $desc] + $tabs;
        }

        return Shop::Smarty()
            ->assign('portlet', $this)
            ->assign('instance', $instance)
            ->assign('tabs', $tabs)
            ->fetch(\PFAD_ROOT . \PFAD_INCLUDES . 'src/OPC/templates/OPC/autoconfig-panel.tpl');
    }

    /**
     * @param PortletInstance $instance
     * @param string $tag
     * @param string $innerHtml
     * @return string
     */
    final protected function getPreviewRootHtml(
        PortletInstance $instance,
        string $tag = 'div',
        string $innerHtml = ''
    ): string {
        $attributes    = $instance->getAttributeString();
        $dataAttribute = $instance->getDataAttributeString();

        return '<' . $tag . ' ' . $attributes . ' ' . $dataAttribute. '>' . $innerHtml . '</' . $tag . '>';
    }

    /**
     * @param PortletInstance $instance
     * @param string $tag
     * @param string $innerHtml
     * @return string
     */
    final protected function getFinalRootHtml(
        PortletInstance $instance,
        string $tag = 'div',
        string $innerHtml = ''
    ): string {
        $attributes = $instance->getAttributeString();

        return '<' . $tag . ' ' . $attributes . '>' . $innerHtml . '</' . $tag . '>';
    }

    /**
     * @return string
     */
    final protected function getDefaultIconSvgUrl(): string
    {
        $path = $this->getTemplatePath() . 'icon.svg';
        $url  = $this->getTemplateUrl() . 'icon.svg';

        if (\file_exists($path) === false) {
            return Shop::getURL() . '/' . \PFAD_INCLUDES . 'src/OPC/templates/OPC/generic.icon.svg';
        }

        return $url;
    }

    /**
     * @return string
     */
    final public function getDefaultPreviewImageUrl(): string
    {
        return Shop::getURL() . '/' . \PFAD_GFX . 'keinBild.gif';
    }
}
