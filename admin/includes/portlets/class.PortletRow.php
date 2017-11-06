<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . PFAD_PORTLETS . 'class.PortletBase.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'editpage_inc.php';

/**
 * Class PortletRow
 */
class PortletRow extends PortletBase
{
    public function getPreviewHtml()
    {
        $layout = $this->properties['layout'];
        $layout = explode(',', $layout);

        $res = '<div class="row">';

        foreach ($layout as $i => $col) {
            $res .= '<div class="col-xs-' . $col . ' jle-subarea"></div>';
        }

        $res .= '</div>';

        return $res;
    }

    public function getFinalHtml()
    {
        $layout = $this->properties['layout'];
        $layout = explode(',', $layout);

        $res = '<div class="row">';

        foreach ($layout as $i => $col) {
            $subArea  = $this->subAreas[$i];
            $res     .= '<div class="col-xs-' . $col . ' jle-subarea">';

            foreach ($subArea as $subPortlet) {
                $portlet        = createPortlet($subPortlet['portletId'])
                    ->setProperties($subPortlet['properties'])
                    ->setSubAreas($subPortlet['subAreas']);
                $subPortletHtml = $portlet->getFinalHtml();
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