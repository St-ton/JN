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
     * @return string
     */
    public function getButtonHtml(): string
    {
        return $this->getFontAwesomeButtonHtml('bell');
    }

    /**
     * @return array
     */
    public function getPropertyDesc(): array
    {
        return [
            'date'         => [
                'label'    => 'Zieldatum',
                'type'     => InputType::DATE,
                'width'    => 50,
                'required' => true,
            ],
            'time'         => [
                'label' => 'Zielzeit',
                'type'  => InputType::TIME,
                'width' => 50,
            ],
            'class'     => [
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
