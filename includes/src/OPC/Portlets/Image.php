<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace OPC\Portlets;

use OPC\PortletInstance;

/**
 * Class Image
 * @package OPC\Portlets
 */
class Image extends \OPC\Portlet
{
    /**
     * @param PortletInstance $instance
     * @param bool            $preview
     * @return string
     */
    public function getHtml(PortletInstance $instance, $preview = false): string
    {
        $instance->setImageAttributes();
        if (!empty($instance->getProperty('responsive'))) {
            $instance->addClass('img-responsive');
        }
        if (!empty($instance->getProperty('shape'))) {
            $instance->addClass($instance->getProperty('shape'));
        }

        return '<img '
            . $instance->getAttributeString()
            . ($preview ? ' ' . $instance->getDataAttributeString() : '')
            . '>';
    }

    /**
     * @param PortletInstance $instance
     * @return string
     */
    public function getPreviewHtml(PortletInstance $instance): string
    {
        return $this->getHtml($instance, true);
    }

    /**
     * @param PortletInstance $instance
     * @return string
     */
    public function getFinalHtml(PortletInstance $instance): string
    {
        return $this->getHtml($instance);
    }

    /**
     * @return string
     */
    public function getButtonHtml(): string
    {
        return '<i class="fa fa-image"></i><br/> Bild';
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
                    '',
                    'img-rounded' => 'abgerundete Ecken',
                    'img-circle' => 'Kreis',
                    'img-thumbnail' => 'mit Rahmen'
                ],
                'dspl_width' => 50,
            ],
            'responsive' => [
                'label'      => 'responsives Bild?',
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
            'title'      => [
                'label' => 'title',
            ]
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
