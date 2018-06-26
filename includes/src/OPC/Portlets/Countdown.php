<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace OPC\Portlets;

use OPC\PortletInstance;

/**
 * Class Countdown
 * @package OPC\Portlets
 */
class Countdown extends \OPC\Portlet
{
    /**
     * @param PortletInstance $instance
     * @return string
     * @throws \Exception
     */
    public function getPreviewHtml(PortletInstance $instance): string
    {
        $instance->setProperty('uid', uniqid('cntdwn-', false));
        $instance->addClass('countdown');
        $instance->addClass($instance->getProperty('class'));

        return $this->getPreviewHtmlFromTpl($instance);
    }

    /**
     * @param PortletInstance $instance
     * @return string
     * @throws \Exception
     */
    public function getFinalHtml(PortletInstance $instance): string
    {
        $instance->addClass('countdown');
        $instance->addClass($instance->getProperty('class'));

        return $this->getFinalHtmlFromTpl($instance);
    }

    /**
     * @return string
     */
    public function getButtonHtml(): string
    {
        return '<i class="fa fa-bell"></i><br/> Countdown';
    }

    /**
     * @return array
     */
    public function getPropertyDesc(): array
    {
        return [
            'date'         => [
                'label'      => 'Zieldatum',
                'type'       => 'date',
                'dspl_width' => 50,
                'required'   => true,
            ],
            'time'         => [
                'label'      => 'Zielzeit',
                'type'       => 'time',
                'dspl_width' => 50,
            ],
            'class'        => [
                'label' => 'CSS Klasse',
            ],
            'expired-text' => [
                'label' => 'Text nach Ablauf',
                'type'  => 'richtext',
            ]
        ];
    }

    /**
     * @return array
     */
    public function getPropertyTabs(): array
    {
        return [
            'Styles'    => 'styles',
            'Animation' => 'animations',
        ];
    }
}
