<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class PortletHeading
 */
class PortletHeading extends CMSPortlet
{
    /**
     * @return string
     */
    public function getPreviewHtml()
    {
        $level = $this->properties['level'];
        $text  = $this->properties['text'];

        return "<h$level>$text</h$level>";
    }

    /**
     * @return string
     */
    public function getFinalHtml()
    {
        return $this->getPreviewHtml();
    }

    /**
     * @return array
     */
    public function getDefaultProps()
    {
        return [
            'level' => 1,
            'text'  => 'Heading Title'
        ];
    }

    /**
     * @return string
     */
    public function getConfigPanelHtml()
    {
        return (new JTLSmarty(true))
            ->assign('properties', $this->properties)
            ->fetch('portlets/settings.heading.tpl');
    }
}