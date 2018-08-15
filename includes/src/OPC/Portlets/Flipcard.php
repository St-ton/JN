<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace OPC\Portlets;

use OPC\PortletInstance;

/**
 * Class Flipcard
 * @package OPC\Portlets
 */
class Flipcard extends \OPC\Portlet
{
    /**
     * @param PortletInstance $instance
     * @return string
     * @throws \Exception
     */
    public function getPreviewHtml(PortletInstance $instance): string
    {
        $instance->setProperty('uid', \uniqid('flp-', false));
        $instance->addClass('flip');
        $instance->addClass($instance->getProperty('flip-style'));
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
        $instance->addClass('flip');
        $instance->addClass($instance->getProperty('flip-style'));
        $instance->addClass($instance->getProperty('class'));

        return $this->getFinalHtmlFromTpl($instance);
    }

    /**
     * @return string
     */
    public function getButtonHtml(): string
    {
        return '<i class="fa fa-clone"></i><br/> Flipcard';
    }

    /**
     * @return array
     */
    public function getPropertyDesc(): array
    {
        return [
            'class'      => [
                'label'      => 'CSS Klasse',
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
