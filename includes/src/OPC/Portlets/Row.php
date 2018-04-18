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
                'collapse' => [
                    'layout-sm' => [
                        'label'   => 'Layout SM',
                        'type'    => 'text',
                        'default' => '6+6',
                    ],
                    'layout-md' => [
                        'label'   => 'Layout MD',
                        'type'    => 'text',
                        'default' => '6+6',
                    ],
                    'layout-lg' => [
                        'label'   => 'Layout LG',
                        'type'    => 'text',
                        'default' => '6+6',
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
            $colLayout = [
                'xs' => isset($layoutXS[$i]) ? $layoutXS[$i] : '',
                'sm' => isset($layoutSM[$i]) ? $layoutSM[$i] : '',
                'md' => isset($layoutMD[$i]) ? $layoutMD[$i] : '',
                'lg' => isset($layoutLG[$i]) ? $layoutLG[$i] : '',
            ];
        }

        return $colLayouts;
    }

    public function getColClasses($colLayout)
    {
        $result = '';

        foreach ($colLayout as $size => $value) {
            if (!empty($value)) {
                $result .= "col-$size-$value ";
            }
        }

        return $result;
    }
}