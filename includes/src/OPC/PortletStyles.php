<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace OPC;

/**
 * Trait PortletStyles
 * @package OPC
 */
trait PortletStyles
{
    /**
     * @return array
     */
    public function getStylesPropertyDesc()
    {
        return [
            'hidden-xs'        => [
                'label'      => '<i class="fa fa-mobile"></i> Sichtbarkeit XS',
                'option'    => 'ausblenden',
                'type'       => 'checkbox',
                'dspl_width' => 25,
            ],
            'hidden-sm'        => [
                'label'      => '<i class="fa fa-tablet"></i> Sichtbarkeit S',
                'option'     => 'ausblenden',
                'type'       => 'checkbox',
                'dspl_width' => 25,
            ],
            'hidden-md'        => [
                'label'      => '<i class="fa fa-laptop"></i> Sichtbarkeit M',
                'option'     => 'ausblenden',
                'type'       => 'checkbox',
                'dspl_width' => 25,
            ],
            'hidden-lg'        => [
                'label'      => '<i class="fa fa-desktop"></i> Sichtbarkeit L',
                'option'     => 'ausblenden',
                'type'       => 'checkbox',
                'dspl_width' => 25,
            ],
            'color'            => [
                'label'   => 'Schriftfarbe',
                'type'    => 'color',
                'default' => '',
            ],
            'background-color' => [
                'label'   => 'Hintergrundfarbe',
                'type'    => 'color',
                'default' => '',
            ],
            'font-size'        => [
                'label'   => 'Schriftgröße',
                'default' => '',
            ],
            'margin-top'       => [
                'label'      => 'margin-top',
                'type'       => 'number',
                'default'    => '',
                'class'      => 'css-input-grid',
                'dspl_width' => 25
            ],
            'margin-right'     => [
                'label'      => 'margin-right',
                'type'       => 'number',
                'default'    => '',
                'class'      => 'css-input-grid',
                'dspl_width' => 25
            ],
            'margin-bottom'    => [
                'label'      => 'margin-bottom',
                'type'       => 'number',
                'default'    => '',
                'class'      => 'css-input-grid',
                'dspl_width' => 25
            ],
            'margin-left'      => [
                'label'      => 'margin-left',
                'type'       => 'number',
                'default'    => '',
                'class'      => 'css-input-grid',
                'dspl_width' => 25
            ],
            'padding-top'      => [
                'label'      => 'padding-top',
                'type'       => 'number',
                'default'    => '',
                'class'      => 'css-input-grid',
                'dspl_width' => 25
            ],
            'padding-right'    => [
                'label'      => 'padding-right',
                'type'       => 'number',
                'default'    => '',
                'class'      => 'css-input-grid',
                'dspl_width' => 25
            ],
            'padding-bottom'   => [
                'label'      => 'padding-bottom',
                'type'       => 'number',
                'default'    => '',
                'class'      => 'css-input-grid',
                'dspl_width' => 25
            ],
            'padding-left'     => [
                'label'      => 'padding-left',
                'type'       => 'number',
                'default'    => '',
                'class'      => 'css-input-grid',
                'dspl_width' => 25
            ],
            'border-width'     => [
                'label'      => 'border-width',
                'type'       => 'number',
                'default'    => '',
                'class'      => 'css-input-grid',
                'dspl_width' => 50
            ],
            'border-style'     => [
                'label'      => 'border-style',
                'type'       => 'select',
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
                'dspl_width' => 50
            ],
            'border-color'     => [
                'label'      => 'border-color',
                'type'       => 'color',
                'dspl_width' => 100
            ]
        ];
    }
}