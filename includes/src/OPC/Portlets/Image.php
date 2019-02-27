<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace JTL\OPC\Portlets;

use JTL\OPC\Portlet;
use JTL\OPC\PortletInstance;

/**
 * Class Image
 * @package JTL\OPC\Portlets
 */
class Image extends Portlet
{
    /**
     * @return bool|string
     */
    public function getRoundedProp(PortletInstance $instance)
    {
        switch($instance->getProperty('shape')) {
            case 'normal':
                return false;
            case 'rounded':
                return true;
            case 'circle':
                return 'circle';
            default:
                return false;
        }
    }

    /**
     * @param PortletInstance $instance
     * @return bool
     */
    public function getThumbnailProp(PortletInstance $instance): bool
    {
        return $instance->getProperty('shape') === 'thumbnail';
    }

    /**
     * @return string
     */
    public function getButtonHtml(): string
    {
        return $this->getFontAwesomeButtonHtml('image');
    }

    /**
     * @return array
     */
    public function getPropertyDesc(): array
    {
        return [
            'src'        => [
                'label'   => 'Bild',
                'type'    => 'image',
                'default' => '',
            ],
            'shape'      => [
                'label'      => 'Form',
                'type'       => 'select',
                'options'    => [
                    'normal'    => 'normal',
                    'rounded'   => 'abgerundete Ecken',
                    'circle'    => 'Kreis',
                    'thumbnail' => 'Als Thumbnail',
                ],
                'dspl_width' => 50,
            ],
            'responsive' => [
                'label'      => 'Responsives Bild?',
                'type'       => 'radio',
                'options'    => [
                    true  => 'ja',
                    false => 'nein',
                ],
                'default'    => true,
                'inline'     => true,
                'dspl_width' => 50,
            ],
            'alt'        => [
                'label' => 'Alternativtext',
            ],
        ];
    }

    /**
     * @return array
     */
    public function getPropertyTabs(): array
    {
        return [
            'Styles' => 'styles',
        ];
    }
}
