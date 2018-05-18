<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace OPC\Portlets;

use OPC\PortletInstance;

class Flipcard extends \OPC\Portlet
{
    public function getPreviewHtml($instance)
    {
        $instance->addClass('flip');
        $instance->addClass($instance->getProperty('flip-style'));
        $instance->addClass($instance->getProperty('class'));

        return $this->getPreviewHtmlFromTpl($instance);
    }

    public function getFinalHtml($instance)
    {
        $instance->addClass('flip');
        $instance->addClass($instance->getProperty('flip-style'));
        $instance->addClass($instance->getProperty('class'));

        return $this->getFinalHtmlFromTpl($instance);
    }

    public function getButtonHtml()
    {
        return '<i class="fa fa-clone"></i><br/> Flipcard';
    }

    public function getConfigPanelHtml($instance)
    {
        return $this->getAutoConfigPanelHtml($instance);
    }

    public function getPropertyDesc()
    {
        return [
            'uid'        => [
                'label'      => 'ID',
                'default'    => uniqid('flp-', false),
                'dspl_width' => 50,
            ],
            'class'      => [
                'label'      => 'CSS Class',
                'dspl_width' => 50,
            ],
            'flip-style' => [
                'label'   => 'Richtung',
                'type'    => 'radio',
                'inline'  => true,
                'options' => [
                    'vertical'   => 'flip_v',
                    'horizontal' => 'flip_h',
                ],
                'default' => 'flip_v',
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