<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace JTL\OPC;

/**
 * Trait PortletStyles
 * @package JTL\OPC
 */
trait PortletStyles
{
    /**
     * @return array
     */
    public function getStylesPropertyDesc(): array
    {
        return [
            // TODO: Support these options for both bootstrap versions
//            'hidden-xs'        => [
//                'type'       => InputType::CHECKBOX,
//                'label'      => '<i class="fa fa-mobile"></i> ' . __('Visibility') . ' XS',
//                'option'     => __('hide'),
//                'width'      => 25,
//            ],
//            'hidden-sm'        => [
//                'type'       => InputType::CHECKBOX,
//                'label'      => '<i class="fa fa-tablet"></i> ' . __('Visibility') . ' S',
//                'option'     => __('hide'),
//                'width'      => 25,
//            ],
//            'hidden-md'        => [
//                'type'       => InputType::CHECKBOX,
//                'label'      => '<i class="fa fa-laptop"></i> ' . __('Visibility') . ' M',
//                'option'     => __('hide'),
//                'width'      => 25,
//            ],
//            'hidden-lg'        => [
//                'type'       => InputType::CHECKBOX,
//                'label'      => '<i class="fa fa-desktop"></i> ' . __('Visibility') . ' L',
//                'option'     => __('hide'),
//                'width'      => 25,
//            ],
            'color'            => [
                'type'    => InputType::COLOR,
                'label'   => __('Font color'),
                'default' => '',
                'width'   => 50,
            ],
            'background-color' => [
                'label'   => __('Background color'),
                'type'    => InputType::COLOR,
                'default' => '',
                'width'   => 50,
            ],
            'font-size'        => [
                'label'   => __('Font size'),
                'default' => '',
            ],
            'margin-top'       => [
                'label'      => 'margin-top',
                'type'       => InputType::NUMBER,
                'default'    => '',
                'class'      => 'css-input-grid',
                'width'      => 25
            ],
            'margin-right'     => [
                'label'      => 'margin-right',
                'type'       => InputType::NUMBER,
                'default'    => '',
                'class'      => 'css-input-grid',
                'width'      => 25
            ],
            'margin-bottom'    => [
                'label'      => 'margin-bottom',
                'type'       => InputType::NUMBER,
                'default'    => '',
                'class'      => 'css-input-grid',
                'width'      => 25
            ],
            'margin-left'      => [
                'label'      => 'margin-left',
                'type'       => InputType::NUMBER,
                'default'    => '',
                'class'      => 'css-input-grid',
                'width'      => 25
            ],
            'padding-top'      => [
                'label'      => 'padding-top',
                'type'       => InputType::NUMBER,
                'default'    => '',
                'class'      => 'css-input-grid',
                'width'      => 25
            ],
            'padding-right'    => [
                'label'      => 'padding-right',
                'type'       => InputType::NUMBER,
                'default'    => '',
                'class'      => 'css-input-grid',
                'width'      => 25
            ],
            'padding-bottom'   => [
                'label'      => 'padding-bottom',
                'type'       => InputType::NUMBER,
                'default'    => '',
                'class'      => 'css-input-grid',
                'width'      => 25
            ],
            'padding-left'     => [
                'label'      => 'padding-left',
                'type'       => InputType::NUMBER,
                'default'    => '',
                'class'      => 'css-input-grid',
                'width'      => 25
            ],
            'border-width'     => [
                'label'      => 'border-width',
                'type'       => InputType::NUMBER,
                'default'    => '',
                'class'      => 'css-input-grid',
                'width'      => 50
            ],
            'border-style'     => [
                'label'      => 'border-style',
                'type'       => InputType::SELECT,
                'options'    => [
                    '',
                    'hidden'  => 'hidden',
                    'dotted'  => 'dotted',
                    'dashed'  => 'dashed',
                    'solid'   => 'solid',
                    'double'  => 'double',
                    'groove'  => 'groove',
                    'ridge'   => 'ridge',
                    'inset'   => 'inset',
                    'outset'  => 'outset',
                    'initial' => 'initial',
                    'inherit' => 'inherit',
                ],
                'width' => 50
            ],
            'border-color'     => [
                'label' => 'border-color',
                'type'  => InputType::COLOR,
                'width' => 100,
            ]
        ];
    }
}
