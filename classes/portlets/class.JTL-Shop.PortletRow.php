<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class PortletRow
 */
class PortletRow extends CMSPortlet
{
    /**
     * @return string
     */
    public function getButton()
    {
        return '<i class="fa fa-columns"></i> Spalten';
    }

    public function getPreviewHtml()
    {
        $this->properties['attr']['class'] .= " row";

        $res = '<div ' . $this->getAttribString() . ' ' . $this->getStyleString() . '>';

        $layoutLg = explode('+', $this->properties['layout-lg']);
        $layoutMd = explode('+', $this->properties['layout-md']);
        $layoutSm = explode('+', $this->properties['layout-sm']);
        $layoutXs = explode('+', $this->properties['layout-xs']);

        // TODO Editor: weiter zusammenfassen?
        foreach ($layoutLg as $i => $col) {
            $addDividerMD = false;
            $addDividerSM = false;
            $addDividerXS = false;
            $res .= '<div class="cle-area col-lg-' . $col;

            if (!empty($layoutMd[$i])) {
                $res .= ' col-md-' . $layoutMd[$i];
                $sum = 0;
                for ($x=0;$x<=$i;++$x) {
                    $sum += $layoutMd[$x];
                }
                $addDividerMD = $sum % 12 === 0 ? true : false;
            }
            if (!empty($layoutSm[$i])) {
                $res .= ' col-sm-' . $layoutSm[$i];
                $sum = 0;
                for ($x=0;$x<=$i;++$x) {
                    $sum += $layoutSm[$x];
                }
                $addDividerSM = $sum % 12 === 0 ? true : false;
            }
            if (!empty($layoutXs[$i])) {
                $res .= ' col-xs-' . $layoutXs[$i];
                $sum = 0;
                for ($x=0;$x<=$i;++$x) {
                    $sum += $layoutXs[$x];
                }
                $addDividerXS = $sum % 12 === 0 ? true : false;
            }
            $res .= '"></div>';

            $res .= $addDividerMD ? '<div class="clearfix visible-md-block"></div>' : '';

            if ($addDividerSM) {
                $res .= '<div class="clearfix visible-sm-block';
                $res .= empty($this->properties['layout-md']) ? ' visible-md-block' : '';
                $res .= '"></div>';
            }

            if ($addDividerXS) {
                $res .= '<div class="clearfix visible-xs-block';
                $res .= empty($this->properties['layout-sm']) ? ' visible-sm-block' : '';
                $res .= empty($this->properties['layout-md']) ? ' visible-md-block' : '';
                $res .= '"></div>';
            }
        }

        $res .= '</div>';

        return $res;
    }

    public function getFinalHtml()
    {
        $this->properties['attr']['class'] .= " row";

        $res = '<div ' . $this->getAttribString() . ' ' . $this->getStyleString() . '>';

        $layoutLg = explode('+', $this->properties['layout-lg']);
        $layoutMd = explode('+', $this->properties['layout-md']);
        $layoutSm = explode('+', $this->properties['layout-sm']);
        $layoutXs = explode('+', $this->properties['layout-xs']);

        foreach ($layoutLg as $i => $col) {
            $subArea = $this->subAreas[$i];
            $addDividerMD = false;
            $addDividerSM = false;
            $addDividerXS = false;
            $res         .= '<div class="col-lg-' . $col;

            if (!empty($layoutMd[$i])) {
                $res .= ' col-md-' . $layoutMd[$i];
                $sum = 0;
                for ($x=0;$x<=$i;++$x) {
                    $sum += $layoutMd[$x];
                }
                $addDividerMD = $sum % 12 === 0 ? true : false;
            }
            if (!empty($layoutSm[$i])) {
                $res .= ' col-sm-' . $layoutSm[$i];
                $sum = 0;
                for ($x=0;$x<=$i;++$x) {
                    $sum += $layoutSm[$x];
                }
                $addDividerSM = $sum % 12 === 0 ? true : false;
            }
            if (!empty($layoutXs[$i])) {
                $res .= ' col-xs-' . $layoutXs[$i];
                $sum = 0;
                for ($x=0;$x<=$i;++$x) {
                    $sum += $layoutXs[$x];
                }
                $addDividerXS = $sum % 12 === 0 ? true : false;
            }
            $res .= '">';
            foreach ($subArea as $subPortlet) {
                $portlet        = CMS::getInstance()->createPortlet($subPortlet['portletId'])
                    ->setProperties($subPortlet['properties'])
                    ->setSubAreas($subPortlet['subAreas']);
                $subPortletHtml = $portlet->getFinalHtml();
                $res           .= $subPortletHtml;
            }
            $res .= '</div>';

            $res .= $addDividerMD ? '<div class="clearfix visible-md-block"></div>' : '';
            if ($addDividerSM) {
                $res .= '<div class="clearfix visible-sm-block';
                $res .= empty($this->properties['layout-md']) ? ' visible-md-block' : '';
                $res .= '"></div>';
            }
            if ($addDividerXS) {
                $res .= '<div class="clearfix visible-xs-block';
                $res .= empty($this->properties['layout-sm']) ? ' visible-sm-block' : '';
                $res .= empty($this->properties['layout-md']) ? ' visible-md-block' : '';
                $res .= '"></div>';
            }
        }
        $res .= '</div>';

        return $res;
    }

    public function getConfigPanelHtml()
    {
        return (new JTLSmarty(true))
            ->assign('properties', $this->properties)
            ->fetch('portlets/settings.row.tpl');
    }

    public function getDefaultProps()
    {
        return [
            // general
            'layout-xs' => '',
            'layout-sm' => '',
            'layout-md' => '',
            'layout-lg' => '6+6',

            // animation
            'animation-style'     => '',
            // attributes
            'attr' => [
                'class'               => '',
                'data-wow-duration'  => '',
                'data-wow-delay'     => '',
                'data-wow-offset'    => '',
                'data-wow-iteration' => '',
            ],
            // style
            'style' => [
                'color'               => '',
                'margin-top'          => '',
                'margin-right'        => '',
                'margin-bottom'       => '',
                'margin-left'         => '',
                'background-color'    => '',
                'padding-top'         => '',
                'padding-right'       => '',
                'padding-bottom'      => '',
                'padding-left'        => '',
                'border-top-width'    => '',
                'border-right-width'  => '',
                'border-bottom-width' => '',
                'border-left-width'   => '',
                'border-style'        => '',
                'border-color'        => '',
            ],
        ];
    }
}