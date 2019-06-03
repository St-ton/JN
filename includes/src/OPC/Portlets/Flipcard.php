<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace JTL\OPC\Portlets;

use JTL\OPC\InputType;
use JTL\OPC\Portlet;

/**
 * Class Flipcard
 * @package JTL\OPC\Portlets
 */
class Flipcard extends Portlet
{
    /**
     * @return string
     */
    public function getButtonHtml(): string
    {
        return $this->getFontAwesomeButtonHtml('clone');
    }

    /**
     * @return array
     */
    public function getPropertyDesc(): array
    {
        return [
            'flip-dir' => [
                'type'    => InputType::RADIO,
                'label'   => 'Richtung',
                'options' => [
                    'flipcard-v' => 'vertikal',
                    'flipcard-h' => 'horizontal',
                ],
                'default' => 'flipcard-v',
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
