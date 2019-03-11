<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace JTL\OPC\Portlets;

use JTL\OPC\InputType;
use JTL\OPC\Portlet;
use JTL\OPC\PortletInstance;

/**
 * Class Countdown
 * @package JTL\OPC\Portlets
 */
class Countdown extends Portlet
{
    /**
     * @param PortletInstance $instance
     * @return string
     * @throws \Exception
     */
    public function getPreviewHtml(PortletInstance $instance): string
    {
        $instance->setProperty('uid', \uniqid('cntdwn-', false));
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
                'type'       => InputType::DATE,
                'dspl_width' => 50,
                'required'   => true,
            ],
            'time'         => [
                'label'      => 'Zielzeit',
                'type'       => InputType::TIME,
                'dspl_width' => 50,
            ],
            'class'        => [
                'label' => 'CSS Klasse',
            ],
            'expired-text' => [
                'label' => 'Text nach Ablauf',
                'type'  => InputType::RICHTEXT,
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
