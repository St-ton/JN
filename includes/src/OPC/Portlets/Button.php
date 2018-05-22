<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace OPC\Portlets;

use OPC\PortletInstance;

class Button extends \OPC\Portlet
{
    public function getPreviewHtml($instance)
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

        return "<div class='" . $wrapperClass . "' $dataAttribute>" . $previewButton . "</div>";
    }

    public function getFinalHtml($instance)
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

        $attributes    = $instance->getAttributeString();

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

    public function getButtonHtml()
    {
        return '<img class="fa" src="' . \Shop::getURL() . '/'
            . PFAD_TEMPLATES
            . 'Evo/themes/base/images/opc/Icon-Button.svg">
            <br/> Button';
    }

    public function getConfigPanelHtml($instance)
    {
        return $this->getAutoConfigPanelHtml($instance);
    }

    public function getPropertyDesc()
    {
        return [
            'btn-text'              => [
                'label'      => 'Text',
                'default'    => 'Hey there',
                'dspl_width' => 50,
            ],
            'btn-type'              => [
                'label'      => 'Type',
                'type'       => 'select',
                'options'    => [
                    'default',
                    'primary',
                    'success',
                    'info',
                    'warning',
                    'danger',
                ],
                'default'    => 'default',
                'dspl_width' => 50,
            ],
            'btn-size'              => [
                'label'      => 'Size',
                'type'       => 'select',
                'options'    => [
                    'xs',
                    'sm',
                    'md',
                    'lg',
                ],
                'default'    => 'md',
                'dspl_width' => 50,
            ],
            'btn-alignment'         => [
                'label'      => 'alignment',
                'type'       => 'select',
                'options'    => [
                    'inline',
                    'left',
                    'right',
                    'center',
                ],
                'default'    => 'inline',
                'dspl_width' => 50,
            ],
            'btn-full-width-flag'   => [
                'label' => 'Full width?',
                'type'  => 'checkbox',
            ],
            'btn-icon-flag'         => [
                'label' => 'Icon?',
                'type' => 'radio',
                'options'    => [
                    'ja' => 'true',
                    'nein' => 'false',
                ],
                'default' => 'false',
                'inline' => true,
            ],
            'btn-icon-alignment'    => [
                'label'                => 'icon alignment',
                'type'                 => 'select',
                'options'              => [
                    'left',
                    'right'
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
                'label'      => 'link?',
                'type' => 'radio',
                'options'    => [
                    'ja' => 'true',
                    'nein' => 'false',
                ],
                'dspl_width' => 100,
            ],
            'btn-link-url'          => [
                'label'                => 'url',
                'collapseControlStart' => true,
                'showOnProp'           => 'btn-link-flag',
                'showOnPropValue'      => 'true',
                'dspl_width'           => 50,
            ],
            'btn-link-title'        => [
                'label'      => 'link title',
                'dspl_width' => 50,
            ],
            'btn-link-new-tab-flag' => [
                'label'              => 'open in new tab?',
                'type'               => 'checkbox',
                'dspl_width'         => 50,
                'collapseControlEnd' => true,
            ]

        ];
    }

    public function getPropertyTabs()
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