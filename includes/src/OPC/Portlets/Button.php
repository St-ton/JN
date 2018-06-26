<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace OPC\Portlets;

use OPC\PortletInstance;

/**
 * Class Button
 * @package OPC\Portlets
 */
class Button extends \OPC\Portlet
{
    /**
     * @param PortletInstance $instance
     * @return string
     */
    public function getPreviewHtml(PortletInstance $instance): string
    {
        // general
        $text          = $instance->getProperty('btn-text');
        $type          = $instance->getProperty('btn-type');
        $size          = $instance->getProperty('btn-size');
        $alignment     = $instance->getProperty('btn-alignment');
        $fullWidthflag = $instance->getProperty('btn-full-width-flag');

        // icon
        $iconFlag      = $instance->getProperty('btn-icon-flag');
        $icon          = $instance->getProperty('btn-icon');
        $iconAlignment = $instance->getProperty('btn-icon-alignment');

        $instance->addClass("btn")
                 ->addClass("btn-$type")
                 ->addClass("btn-$size")
                 ->addClass(!empty($fullWidthflag) ? 'btn-block' : '');

        $attributes    = $instance->getAttributeString();
        $dataAttribute = $instance->getDataAttributeString();

        $previewButton = "<a ";
        $wrapperClass  = "";
        if (!empty($alignment)) {
            $wrapperClass = $alignment !== 'inline' ? 'text-' . $alignment : 'inline-block';
        }
        $previewButton .= $attributes . '>';

        if (!empty($iconFlag) && $icon !== '') {
            if ($iconAlignment === 'left') {
                $previewButton .= "<i class='$icon' style='top:2px'></i> $text</a>";
            } else {
                $previewButton .= "$text <i class='$icon' style='top:2px'></i></a>";
            }
        } else {
            $previewButton .= "$text</a>";
        }

        return "<div class='" . $wrapperClass . "' $dataAttribute>" . $previewButton . "</div>";
    }

    /**
     * @param PortletInstance $instance
     * @return string
     */
    public function getFinalHtml(PortletInstance $instance): string
    {
        // general
        $text          = $instance->getProperty('btn-text');
        $type          = $instance->getProperty('btn-type');
        $size          = $instance->getProperty('btn-size');
        $alignment     = $instance->getProperty('btn-alignment');
        $fullWidthflag = $instance->getProperty('btn-full-width-flag');

        // icon
        $iconFlag      = $instance->getProperty('btn-icon-flag');
        $icon          = $instance->getProperty('btn-icon');
        $iconAlignment = $instance->getProperty('btn-icon-alignment');

        // URL
        $linkFlag       = $instance->getProperty('btn-link-flag');
        $linkUrl        = $instance->getProperty('btn-link-url');
        $linkTitle      = $instance->getProperty('btn-link-title');
        $linkNewTabFlag = $instance->getProperty('btn-link-new-tab-flag');

        $instance->addClass("btn")
                 ->addClass("btn-$type")
                 ->addClass("btn-$size")
                 ->addClass(!empty($fullWidthflag) ? 'btn-block' : '');

        $attributes = $instance->getAttributeString();

        $previewButton = "<a ";

        if (!empty($linkFlag) && !empty($linkUrl)) {
            $previewButton .= ' href="' . $linkUrl . '" title="' . $linkTitle . '" ';
            $previewButton .= !empty($linkNewTabFlag) ? ' target="_blank" ' : '';
        }

        $wrapperClass = "";

        if (!empty($alignment)) {
            $wrapperClass = $alignment !== 'inline' ? 'text-' . $alignment : 'inline-block';
        }

        $previewButton .= $attributes . '>';

        if (!empty($iconFlag) && $icon !== '') {
            if ($iconAlignment === 'left') {
                $previewButton .= "<i class='$icon' style='top:2px'></i> $text</a>";
            } else {
                $previewButton .= "$text <i class='$icon' style='top:2px'></i></a>";
            }
        } else {
            $previewButton .= "$text</a>";
        }

        return "<div class='" . $wrapperClass . "'>" . $previewButton . "</div>";
    }

    /**
     * @return string
     */
    public function getButtonHtml(): string
    {
        return '<img class="fa" src="' . $this->getDefaultIconSvgUrl() . '"></i><br>Button';
    }

    /**
     * @return array
     */
    public function getPropertyDesc(): array
    {
        return [
            'btn-text'              => [
                'label'      => 'Text',
                'default'    => 'Hey there!',
                'dspl_width' => 50,
            ],
            'btn-type'              => [
                'label'      => 'Typ',
                'type'       => 'select',
                'options'    => [
                    'default' => 'Standard',
                    'primary' => 'Primär',
                    'success' => 'Erfolg',
                    'info'    => 'Info',
                    'warning' => 'Warnung',
                    'danger'  => 'Gefahr',
                ],
                'default'    => 'default',
                'dspl_width' => 50,
            ],
            'btn-size'              => [
                'label'      => 'Größe',
                'type'       => 'select',
                'options'    => [
                    'xs' => 'XS',
                    'sm' => 'S',
                    'md' => 'M',
                    'lg' => 'L',
                ],
                'default'    => 'md',
                'dspl_width' => 50,
            ],
            'btn-alignment'         => [
                'label'      => 'Ausrichtung',
                'type'       => 'select',
                'options'    => [
                    'inline' => 'ohne',
                    'left'   => 'links',
                    'right'  => 'rechts',
                    'center' => 'mittig',
                ],
                'default'    => 'inline',
                'dspl_width' => 50,
            ],
            'btn-full-width-flag'   => [
                'label' => 'gesamte Breite nutzen',
                'type'  => 'checkbox',
            ],
            'btn-icon-flag'         => [
                'label'   => 'Icon?',
                'type'    => 'radio',
                'options' => [
                    'true'  => 'ja',
                    'false' => 'nein',
                ],
                'default' => 'false',
                'inline'  => true,
            ],
            'btn-icon-alignment'    => [
                'label'                => 'Iconausrichtung',
                'type'                 => 'select',
                'options'              => [
                    'left'  => 'links',
                    'right' => 'rechts'
                ],
                'collapseControlStart' => true,
                'showOnProp'           => 'btn-icon-flag',
                'showOnPropValue'      => 'true',
                'dspl_width'           => 50,
            ],
            'btn-icon'              => [
                'label'              => 'Icon',
                'type'               => 'icon',
                'collapseControlEnd' => true,
                'dspl_width'         => 100,
            ],
            'btn-link-flag'         => [
                'label'      => 'Link?',
                'type'       => 'radio',
                'options'    => [
                    'true'  => 'ja',
                    'false' => 'nein',
                ],
                'dspl_width' => 100,
            ],
            'btn-link-url'          => [
                'label'                => 'URL',
                'collapseControlStart' => true,
                'showOnProp'           => 'btn-link-flag',
                'showOnPropValue'      => 'true',
                'dspl_width'           => 50,
            ],
            'btn-link-title'        => [
                'label'      => 'Linktitel',
                'dspl_width' => 50,
            ],
            'btn-link-new-tab-flag' => [
                'label'              => 'In neuem Tab öffnen?',
                'type'               => 'checkbox',
                'dspl_width'         => 50,
                'collapseControlEnd' => true,
            ]

        ];
    }

    /**
     * @return array
     */
    public function getPropertyTabs(): array
    {
        return [
            'Icon'      => [
                'btn-icon-flag',
                'btn-icon-alignment',
                'btn-icon',
            ],
            'Url'       => [
                'btn-link-flag',
                'btn-link-url',
                'btn-link-title',
                'btn-link-new-tab-flag'
            ],
            'Styles'    => 'styles',
            'Animation' => 'animations',
        ];
    }
}
