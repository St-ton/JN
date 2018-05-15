<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace OPC\Portlets;

use OPC\PortletInstance;

class Row extends \OPC\Portlet
{
    public function getPreviewHtml($instance)
    {
        return $this->getPreviewHtmlFromTpl($instance);
    }

    public function getFinalHtml($instance)
    {
        return $this->getFinalHtmlFromTpl($instance);
    }

    public function getButtonHtml()
    {
        return '<i class="fa fa-columns"></i><br>Spalten';
    }

    public function getConfigPanelHtml($instance)
    {
        return $this->getAutoConfigPanelHtml($instance);
    }

    public function getPropertyDesc()
    {
        return [
            'layout-xs' => [
                'label'   => 'Layout XS',
                'type'    => 'text',
                'default' => '6+6',
                'dspl_width' => 50,
                'layoutCollapse' => [
                    'layout-sm' => [
                        'label'   => 'Layout SM',
                        'type'    => 'text',
                    ],
                    'layout-md' => [
                        'label'   => 'Layout MD',
                        'type'    => 'text',
                    ],
                    'layout-lg' => [
                        'label'   => 'Layout LG',
                        'type'    => 'text',
                    ],
                ]
            ],
            'border-color' => [
                'label'   => 'noob color',
               'type'    => 'color',
               'default' => 'blue',
               'dspl_width' => 50,
            ],
        ];
    }

    public function getPropertyTabs()
    {
        return [
            'Styles' => 'styles',
        ];
    }

    /**
     * @param PortletInstance $instance
     * @return array
     */
    public function getLayouts($instance)
    {
        $layoutXS = explode('+', $instance->getProperty('layout-xs'));
        $layoutSM = explode('+', $instance->getProperty('layout-sm'));
        $layoutMD = explode('+', $instance->getProperty('layout-md'));
        $layoutLG = explode('+', $instance->getProperty('layout-lg'));
        $colCount = max(count($layoutXS), count($layoutSM), count($layoutMD), count($layoutLG));

        $colLayouts = array_fill(0, $colCount, '');

        foreach ($colLayouts as $i => &$colLayout) {
            $sumXS = 0;
            $sumSM = 0;
            $sumMD = 0;
            $sumLG = 0;

            for ($x=0;$x<=$i;++$x) {
                $sumXS = !empty($layoutXS[$x]) ? ($sumXS+$layoutXS[$x]) : $sumXS;
                $sumSM = !empty($layoutSM[$x]) ? ($sumSM+$layoutSM[$x]) : $sumSM;
                $sumMD = !empty($layoutMD[$x]) ? ($sumMD+$layoutMD[$x]) : $sumMD;
                $sumLG = !empty($layoutLG[$x]) ? ($sumLG+$layoutLG[$x]) : $sumLG;
            }

            $colLayout = [
                'xs' => isset($layoutXS[$i]) ? $layoutXS[$i] : '',
                'sm' => isset($layoutSM[$i]) ? $layoutSM[$i] : '',
                'md' => isset($layoutMD[$i]) ? $layoutMD[$i] : '',
                'lg' => isset($layoutLG[$i]) ? $layoutLG[$i] : '',
                'divider' => [
                    'xs' => $sumXS === 0 ? false : ($sumXS % 12 === 0),
                    'sm' => $sumSM === 0 ? false : ($sumSM % 12 === 0),
                    'md' => $sumMD === 0 ? false : ($sumMD % 12 === 0),
                    'lg' => $sumLG === 0 ? false : ($sumLG % 12 === 0),
                ],
            ];

        }

        return $colLayouts;
    }

    public function getColClasses($colLayout)
    {
        $result = '';

        foreach ($colLayout as $size => $value) {
            if (!empty($value) && is_array($value) === false) {
                $result .= "col-$size-$value ";
            }
        }

        return $result;
    }

    public function getDividers($colLayout)
    {
        $result = '';

        foreach ($colLayout['divider'] as $size => $value) {
            if (!empty($value)) {
                $result .= '<div class="clearfix visible-' . $size . '-block"></div>';
            }
        }

        return $result;
    }
}