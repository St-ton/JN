<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace OPC\Portlets;

use OPC\PortletInstance;

class Countdown extends \OPC\Portlet
{
    public function getPreviewHtml($instance)
    {
        $instance->addClass('countdown');
        $instance->addClass($instance->getProperty('class'));

        return $this->getPreviewHtmlFromTpl($instance);
    }

    public function getFinalHtml($instance)
    {
        $instance->addClass('countdown');
        $instance->addClass($instance->getProperty('class'));

        return $this->getFinalHtmlFromTpl($instance);
    }

    public function getButtonHtml()
    {
        return '<i class="fa fa-bell"></i><br/> Countdown';
    }

    public function getConfigPanelHtml($instance)
    {
        return $this->getAutoConfigPanelHtml($instance);
    }

    public function getPropertyDesc()
    {
        return [
            'uid' => [
                'type' => 'hidden',
                'default' => uniqid(),
            ],
            'date' => [
                'label' => 'Zieldatum',
                'type' => 'date',
                'dspl_width' => 50,
            ],
            'time' => [
                'label' => 'Zielzeit',
                'type' => 'time',
                'dspl_width' => 50,
            ],
            'class' => [
                'label' => 'CSS Class',
            ],
            'expired-text' => [
                'label' => 'text nach Ablauf',
                'type' => 'richtext',
            ]
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