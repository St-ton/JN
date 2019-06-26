<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace JTL\OPC\Portlets;

use JTL\OPC\InputType;
use JTL\OPC\Portlet;

/**
 * Class Gallery
 * @package JTL\OPC\Portlets
 */
class Gallery extends Portlet
{
    /**
     * @return array
     */
    public function getPropertyDesc(): array
    {
        return [
            'height' => [
                'type'    => InputType::NUMBER,
                'label'   => 'Höhe der Vorschaubilder',
                'default' => 250,
                'width'   => 50,
            ],
            'images' => [
                'type'       => InputType::IMAGE_SET,
                'label'      => 'Bilder-Liste',
                'default'    => [],
                'useColumns' => true,
            ],
        ];
    }

    /**
     * @return array
     */
    public function getPropertyTabs(): array
    {
        return [
            'Images' => ['images'],
            'Styles' => 'styles',
        ];
    }
}
