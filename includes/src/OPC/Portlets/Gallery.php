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
            'galleryStyle' => [
                'type'    => InputType::SELECT,
                'label'   => 'Layout',
                'default' => 'grid',
                'width'   => 50,
                'options' => [
                    'grid'      => 'Gitter',
                    'alternate' => 'Alternierend',
                    'columns'   => 'Spalten',
                ],
            ],
            'images' => [
                'type'    => InputType::IMAGE_SET,
                'label'   => __('imageList'),
                'default' => [],
            ],
        ];
    }

    /**
     * @return array
     */
    public function getPropertyTabs(): array
    {
        return [
            __('Styles') => 'styles',
        ];
    }
}
