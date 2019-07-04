<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace JTL\OPC\Portlets;

use JTL\OPC\Portlet;
use JTL\OPC\PortletInstance;

/**
 * Class Row
 * @package JTL\OPC\Portlets
 */
class Row extends Portlet
{
    /**
     * @return string
     */
    public function getButtonHtml(): string
    {
        return $this->getFontAwesomeButtonHtml('fas fa-columns');
    }

    /**
     * @return array
     */
    public function getPropertyDesc(): array
    {
        return [
            'layout-xs' => [
                'label'   => '<i class="fa fa-mobile"></i>' . __('layoutXS'),
                'default' => '6+6',
                'width'   => 25,
            ],
            'layout-sm' => [
                'label' => '<i class="fa fa-tablet"></i>' . __('layoutS'),
                'width' => 25,
            ],
            'layout-md' => [
                'label' => '<i class="fa fa-laptop"></i>' . __('layoutM'),
                'width' => 25,
            ],
            'layout-lg' => [
                'label' => '<i class="fa fa-desktop"></i>' . __('layoutL'),
                'width' => 25,
            ],
        ];
    }

    /**
     * @return array
     */
    public function getPropertyTabs(): array
    {
        return [
            __('Styles')    => 'styles',
            __('Animation') => 'animations',
        ];
    }

    /**
     * @param PortletInstance $instance
     * @return array
     */
    public function getLayouts(PortletInstance $instance): array
    {
        $layoutXS = \explode('+', $instance->getProperty('layout-xs'));
        $layoutSM = \explode('+', $instance->getProperty('layout-sm'));
        $layoutMD = \explode('+', $instance->getProperty('layout-md'));
        $layoutLG = \explode('+', $instance->getProperty('layout-lg'));
        $colCount = \max(\count($layoutXS), \count($layoutSM), \count($layoutMD), \count($layoutLG));

        $colLayouts = \array_fill(0, $colCount, '');

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
                    'xs' => $sumXS > 0 && $sumXS % 12 === 0,
                    'sm' => $sumSM > 0 && $sumSM % 12 === 0,
                    'md' => $sumMD > 0 && $sumMD % 12 === 0,
                    'lg' => $sumLG > 0 && $sumLG % 12 === 0,
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
            if (!empty($value) && \is_array($value) === false) {
                $result .= 'col-' . $size . '-' . $value . ' ';
            }
        }

        return $result;
    }
}
