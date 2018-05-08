<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace OPC\Portlets;

class ProductStream extends \OPC\Portlet
{
    public function getPreviewHtml($instance)
    {
        $attributes    = $instance->getAttributeString();
        $dataAttribute = $instance->getDataAttributeString();
        $style         = $instance->getProperty('listStyle');

        return "<div $attributes $dataAttribute>"
            . "<img src='" . PFAD_TEMPLATES . "Evo/portlets/preview.productstream.$style.png' "
            . "style='width:98%;filter:grayscale(50%) opacity(60%)'>"
            . "<div style='color:#5cbcf6;font-size:40px;font-weight:bold;margin:0;margin-top:-1em;line-height:1em;'>
                Produktliste</div>"
            . "</div>";
    }

    public function getFinalHtml($instance)
    {
        return "";//"<h$level>$text</h$level>";
    }

    public function getButtonHtml()
    {
        return '<img class="fa" src="' . \Shop::getURL() . '/' . PFAD_TEMPLATES
            . 'Evo/themes/base/images/opc/Icon-ProductStream.svg"><br>Product<br>Stream';
    }

    public function getConfigPanelHtml($instance)
    {
        return $this->getAutoConfigPanelHtml($instance);
    }

    public function getPropertyDesc()
    {
        return [
            'listStyle'  => [
                'type'    => 'select',
                'options' => ['gallery'],
                'default' => 'gallery',
            ],
            'filters' => [
                'type'    => 'filter',
                'default' => [],
            ],
        ];
    }

    public function getPropertyTabs()
    {
        return [
            'Styles' => 'styles',
        ];
    }
}