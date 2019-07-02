<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace JTL\OPC\Portlets;

use JTL\OPC\InputType;
use JTL\OPC\Portlet;

/**
 * Class Button
 * @package JTL\OPC\Portlets
 */
class Button extends Portlet
{
    /**
     * @return array
     */
    public function getPropertyDesc(): array
    {
        return [
            'label' => [
                'label'   => 'Label',
                'default' => 'Hey there!',
                'width'   => 50,
            ],
            'url' => [
                'label' => 'URL',
                'width' => 50,
            ],
            'style' => [
                'type'    => InputType::SELECT,
                'label'   => 'Stil',
                'default' => 'primary',
                'options' => [
                    'primary' => 'Primär',
                    'success' => 'Erfolg',
                    'info'    => 'Info',
                    'warning' => 'Warnung',
                    'danger'  => 'Gefahr',
                ],
                'width'   => 50,
            ],
            'new-tab' => [
                'type'       => InputType::CHECKBOX,
                'label'      => 'In neuem Tab öffnen',
                'width'      => 50,
            ],
            'size' => [
                'type'       => InputType::SELECT,
                'label'      => 'Größe',
                'default'    => 'md',
                'options'    => [
                    'sm' => 'S',
                    'md' => 'M',
                    'lg' => 'L',
                ],
                'width' => 50,
            ],
            'link-title' => [
                'label'      => 'Linktitel',
                'width'      => 50,
            ],
            'align' => [
                'type'       => InputType::SELECT,
                'label'      => 'Ausrichtung',
                'options'    => [
                    'block'  => 'gesamte Breite nutzen',
                    'left'   => 'links',
                    'right'  => 'rechts',
                    'center' => 'zentriert',
                ],
                'default'    => 'left',
                'width'      => 50,
            ],
            'use-icon' => [
                'type'     => InputType::CHECKBOX,
                'label'    => 'Button mit Icon versehen',
                'children' => [
                    'icon-align'    => [
                        'type'    => InputType::SELECT,
                        'label'   => 'Iconausrichtung',
                        'options' => [
                            'left'  => 'links',
                            'right' => 'rechts'
                        ],
                    ],
                    'icon' => [
                        'type'  => InputType::ICON,
                        'label' => 'Icon',
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public function getPropertyTabs(): array
    {
        return [
            'Icon'      => [
                'use-icon',
            ],
            'Styles'    => 'styles',
            'Animation' => 'animations',
        ];
    }
}
