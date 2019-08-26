<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace JTL\OPC\Portlets\Countdown;

use JTL\OPC\InputType;
use JTL\OPC\Portlet;

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
        return $this->getFontAwesomeButtonHtml('far fa-calendar-alt');
    }

    /**
     * @return array
     */
    public function getPropertyDesc(): array
    {
        return [
            'date'         => [
                'label'    => __('countdownDate'),
                'type'     => InputType::DATE,
                'width'    => 50,
                'required' => true,
            ],
            'time'         => [
                'label' => __('countdownTime'),
                'type'  => InputType::TIME,
                'width' => 50,
            ],
            'expired-text' => [
                'label' => __('textAfterCountdownFinished'),
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
            __('Styles')    => 'styles',
            __('Animation') => 'animations',
        ];
    }
}
