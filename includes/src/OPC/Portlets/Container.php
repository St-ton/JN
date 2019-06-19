<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace JTL\OPC\Portlets;

use JTL\OPC\InputType;
use JTL\OPC\Portlet;
use JTL\OPC\PortletInstance;
use JTL\Shop;

/**
 * Class Container
 * @package JTL\OPC\Portlets
 */
class Container extends Portlet
{
    /**
     * @param PortletInstance $instance
     * @return string
     * @throws \Exception
     */
    public function getPreviewHtml(PortletInstance $instance): string
    {
        $instance->setStyle('min-height', $instance->getProperty('min-height'));
        $instance->setStyle('position', 'relative');

        if ($instance->getProperty('background-flag') === 'image' && !empty($instance->getProperty('src'))) {
            $name = \explode('/', $instance->getProperty('src'));
            $name = \end($name);

            $instance->setStyle(
                'background',
                'url("' . Shop::getURL() . '/' . \PFAD_MEDIAFILES . 'Bilder/.xs/' . $name . '")'
            );

            $instance->setStyle('background-size', 'cover');
            $instance->getImageAttributes(Shop::getURL() . '/' . \PFAD_MEDIAFILES . 'Bilder/.xs/' . $name);
        }

        if ($instance->getProperty('background-flag') === 'video') {
            $instance->setStyle('overflow', 'hidden');
            $instance->setStyle('position', 'relative');

            $name = \explode('/', $instance->getProperty('video-poster'));
            $name = \end($name);

            $instance->setProperty(
                'video-poster-url',
                Shop::getURL() . '/' . \PFAD_MEDIAFILES . 'Bilder/.xs/' . $name
            );
        }

        if (!empty($instance->getProperty('class'))) {
            $instance->addClass($instance->getProperty('class'));
        }

        return $this->getPreviewHtmlFromTpl($instance);
    }


    /**
     * @param PortletInstance $instance
     * @return string
     * @throws \Exception
     */
    public function getFinalHtml(PortletInstance $instance): string
    {
        $instance->setStyle('min-height', $instance->getProperty('min-height'));
        $instance->setStyle('position', 'relative');

        if ($instance->getProperty('background-flag') === 'image' && !empty($instance->getProperty('src'))) {
            $name = \explode('/', $instance->getProperty('src'));
            $name = \end($name);

            $instance->addClass('parallax-window');
            $instance->setAttribute('data-parallax', 'scroll');
            $instance->setAttribute('data-z-index', '1');
            $instance->setAttribute('data-image-src', Shop::getURL() . '/' . \PFAD_MEDIAFILES . 'Bilder/.lg/' . $name);
            $instance->getImageAttributes(Shop::getURL() . '/' . \PFAD_MEDIAFILES . 'Bilder/.xs/' . $name);
        }

        if (!empty($instance->getProperty('class'))) {
            $instance->addClass($instance->getProperty('class'));
        }

        if ($instance->getProperty('background-flag') === 'video') {
            $instance->setStyle('overflow', 'hidden');

            $name = \explode('/', $instance->getProperty('video-poster'));
            $name = \end($name);

            $instance->setProperty(
                'video-poster-url',
                Shop::getURL() . '/' . \PFAD_MEDIAFILES . 'Bilder/.xs/' . $name
            );

            $name = \explode('/', $instance->getProperty('video-src'));
            $name = \end($name);

            $instance->setProperty(
                'video-src-url',
                Shop::getURL() . '/' . \PFAD_MEDIAFILES . 'Videos/' . $name
            );
        }


        return $this->getFinalHtmlFromTpl($instance);
    }

    /**
     * @return string
     */
    public function getButtonHtml(): string
    {
        return '<i class="fa fa-object-group"></i><br/> Container';
    }

    /**
     * @return array
     */
    public function getPropertyDesc(): array
    {
        return [
            'min-height'      => [
                'type'               => InputType::NUMBER,
                'label'              => 'Mindesthöhe in px',
                'default'            => 300,
                'width'         => 50,
            ],
            'background-flag' => [
                'type'    => InputType::RADIO,
                'label'   => 'Hintergrund nutzen?',
                'options' => [
                    'image' => 'mitlaufendes Bild (parallax)',
                    'video' => 'Hintergrundvideo',
                    'false' => 'einfacher Container',
                    'boxed' => 'boxed'
                ],
                'default' => 'false',
                'inline'  => true,
                'width'         => 50,
                'childrenFor' => [
                    'image' => [
                        'src'  => [
                            'label' => 'Hintergrundbild',
                            'type'  => InputType::IMAGE,
                        ],
                    ],
                    'video' => [
                        'video-src'       => [
                            'type'  => InputType::VIDEO,
                            'label' => 'Video',
                            'width' => 50,
                        ],
                        'video-poster'    => [
                            'type'  => InputType::IMAGE,
                            'label' => 'Platzhalterbild',
                            'width' => 50,
                        ],
                    ],
                ],
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
