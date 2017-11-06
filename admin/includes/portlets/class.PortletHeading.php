<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . PFAD_PORTLETS . 'class.PortletBase.php';

/**
 * Class PortletHeading
 */
class PortletHeading extends PortletBase
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
        return Shop::Smarty()
            ->assign('properties', $this->properties)
            ->fetch('tpl_inc/portlets/settings.heading.tpl');
    }
}