<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . PFAD_PORTLETS . 'class.PortletBase.php';

/**
 * Class PortletColumn
 */
class PortletColumn extends PortletBase
{
    public function getPreviewContent($settings = null)
    {
        $layout = isset($settings['layout']) ? $settings['layout'] : '6,6';
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

    public function getSettingsHTML($settings)
    {
        return $this->oSmarty
            ->assign('settings', $settings)
            ->fetch('tpl_inc/portlets/settings.column.tpl');
    }

    public function getInitialSettings()
    {
        return [
            'layout' => '6,6',
        ];
    }
}