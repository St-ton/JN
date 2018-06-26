<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace OPC\Portlets;

use OPC\PortletInstance;

/**
 * Class Row
 * @package OPC\Portlets
 */
class Row extends \OPC\Portlet
{
    /**
     * @param PortletInstance $instance
     * @return string
     * @throws \Exception
     */
    public function getPreviewHtml(PortletInstance $instance): string
    {
        $instance->addClass($instance->getProperty('class'));

        return $this->getPreviewHtmlFromTpl($instance);
    }

    /**
     * @param PortletInstance $instance
     * @return string
     * @throws \Exception
     */
    public function getFinalHtml(PortletInstance $instance): string
    {
        $instance->addClass($instance->getProperty('class'));

        return $this->getFinalHtmlFromTpl($instance);
    }

    /**
     * @return string
     */
    public function getButtonHtml(): string
    {
        return '<i class="fa fa-columns"></i><br>Spalten';
    }

    /**
     * @return array
     */
    public function getPropertyDesc(): array
    {
        return [
            'layout-xs' => [
                'label'          => '<i class="fa fa-mobile"></i> Layout XS',
                'type'           => 'text',
                'default'        => '6+6',
                'dspl_width'     => 50,
                'layoutCollapse' => [
                    'layout-sm' => [
                        'label' => '<i class="fa fa-tablet"></i> Layout S',
                        'type'  => 'text',
                    ],
                    'layout-md' => [
                        'label' => '<i class="fa fa-laptop"></i> Layout M',
                        'type'  => 'text',
                    ],
                    'layout-lg' => [
                        'label' => '<i class="fa fa-desktop"></i> Layout L',
                        'type'  => 'text',
                    ],
                ]
            ],
            'class'     => [
                'label'      => 'CSS Klasse',
                'dspl_width' => 50,
            ],
        ];
    }

    /**
     * @return array
     */
    public function getPropertyTabs(): array
    {
        return [
            'Styles'    => 'styles',
            'Animation' => 'animations',
        ];
    }

    /**
     * @param PortletInstance $instance
     * @return array
     */
    public function getLayouts(PortletInstance $instance): array
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

            for ($x = 0; $x <= $i; ++$x) {
                $sumXS = !empty($layoutXS[$x]) ? ($sumXS + $layoutXS[$x]) : $sumXS;
                $sumSM = !empty($layoutSM[$x]) ? ($sumSM + $layoutSM[$x]) : $sumSM;
                $sumMD = !empty($layoutMD[$x]) ? ($sumMD + $layoutMD[$x]) : $sumMD;
                $sumLG = !empty($layoutLG[$x]) ? ($sumLG + $layoutLG[$x]) : $sumLG;
            }

            $colLayout = [
                'xs'      => $layoutXS[$i] ?? '',
                'sm'      => $layoutSM[$i] ?? '',
                'md'      => $layoutMD[$i] ?? '',
                'lg'      => $layoutLG[$i] ?? '',
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

    /**
     * @param array $colLayout
     * @return string
     */
    public function getColClasses(array $colLayout): string
    {
        $result = '';
        foreach ($colLayout as $size => $value) {
            if (!empty($value) && is_array($value) === false) {
                $result .= "col-$size-$value ";
            }
        }

        return $result;
    }

    /**
     * @param array $colLayout
     * @return string
     */
    public function getDividers(array $colLayout): string
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
