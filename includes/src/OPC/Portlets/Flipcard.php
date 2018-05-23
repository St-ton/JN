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
        $instance->setProperty('uid', uniqid('flp-', false));
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
            'class'      => [
                'label'      => 'CSS Class',
                'dspl_width' => 50,
            ],
            'flip-style' => [
                'label'      => 'Richtung',
                'type'       => 'radio',
                'inline'     => true,
                'options'    => [
                    'flip_v' => 'vertical',
                    'flip_h' => 'horizontal',
                ],
                'default'    => 'flip_v',
                'dspl_width' => 50,
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