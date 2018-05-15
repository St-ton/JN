<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace OPC\Portlets;

class Tabs extends \OPC\Portlet
{
    public function getPreviewHtml($inst)
    {
        return $this->getPreviewHtmlFromTpl($inst);
    }

    public function getFinalHtml($inst)
    {
        return $this->getFinalHtmlFromTpl($inst);
    }

    public function getButtonHtml()
    {
        return '<img class="fa" src="' . \Shop::getURL() . '/'
            . PFAD_TEMPLATES
            . 'Evo/themes/base/images/opc/Icon-Tab.svg">
            <br/> Tabs';
    }

    public function getPropertyDesc()
    {
        return [
            'tabs' => [
                'label'   => 'Tabs',
                'type'    => 'textlist',
                'default' => ['Tab eins', 'Tab zwei', 'Tab drei'],
            ],
        ];
    }

    public function getPropertyTabs()
    {
        return [
            'Styles'    => 'styles',
            'Animation' => 'animations',
        ];
    }
}