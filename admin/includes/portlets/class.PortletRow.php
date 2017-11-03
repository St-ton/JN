<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . PFAD_PORTLETS . 'class.PortletBase.php';

/**
 * Class PortletRow
 */
class PortletRow extends PortletBase
{
    public function getPreviewHtml()
    {
        $layout = isset($this->properties['layout']) ? $this->properties['layout'] : '6,6';
        $layout = explode(',', $layout);

        $res = '<div class="row">';

        foreach ($layout as $i => $col) {
            $res .= '<div class="col-xs-' . $col . ' jle-subarea"></div>';
        }

        $res .= '</div>';

        return $res;
    }

    public function getHTMLContent($portletData)
    {
        $settings = $portletData['settings'];
        $subareas = $portletData['subAreas'];
        $layout   = isset($settings['layout']) ? $settings['layout'] : '6,6';
        $layout   = explode(',', $layout);

        $res = '<div class="row">';

        foreach ($layout as $i => $col) {
            $subArea  = $subareas[$i];
            $res     .= '<div class="col-xs-' . $col . ' jle-subarea">';

            foreach ($subArea as $subPortlet) {
                $portlet        = PortletBase::createInstance($subPortlet['portletId'], $this->oSmarty, $this->oDB);
                $subPortletHtml = $portlet->getHTMLContent($subPortlet);
                $res           .= $subPortletHtml;
            }

            $res .= '</div>';
        }

        $res .= '</div>';

        return $res;
    }

    public function getConfigPanelHtml()
    {
        return Shop::Smarty()
            ->assign('properties', $this->properties)
            ->fetch('tpl_inc/portlets/settings.row.tpl');
    }

    public function getDefaultProps()
    {
        return [
            'layout' => '6,6',
        ];
    }
}