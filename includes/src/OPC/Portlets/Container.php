<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace OPC\Portlets;

use function Functional\unique;
use OPC\PortletInstance;

/**
 * Class Container
 * @package OPC\Portlets
 */
class Container extends \OPC\Portlet
{
    /**
     * @param PortletInstance $instance
     * @return string
     */
    public function getPreviewHtml(PortletInstance $instance): string
    {
        $instance->setProperty('uid', \uniqid('cntr-', false));
        $instance->setStyle('min-height', $instance->getProperty('min-height'));
        $instance->setStyle('position', 'relative');
        if ($instance->getProperty('background-flag') === 'image' && !empty($instance->getProperty('src'))) {
            $name = \explode('/', $instance->getProperty('src'));
            $name = \end($name);

            $instance->setStyle(
                'background',
                'url("' . \Shop::getURL() . '/' . PFAD_MEDIAFILES . 'Bilder/.xs/' . $name . '")'
            );

            $instance->setStyle('background-size', 'cover');
            $instance->getImageAttributes(\Shop::getURL() . '/' . PFAD_MEDIAFILES . 'Bilder/.xs/' . $name);
        }
        if ($instance->getProperty('background-flag') === 'video') {
            $instance->setStyle('overflow', 'hidden');
            $instance->setStyle('position', 'relative');

            $name = \explode('/', $instance->getProperty('video-poster'));
            $name = \end($name);

            $instance->setProperty(
                'video-poster-url',
                \Shop::getURL() . '/' . PFAD_MEDIAFILES . 'Bilder/.xs/' . $name);
        }
        if (!empty($instance->getProperty("class"))) {
            $instance->addClass($instance->getProperty("class"));
        }


        return $this->getPreviewHtmlFromTpl($instance);
    }


    /**
     * @param PortletInstance $instance
     * @return string
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
            $instance->setAttribute('data-image-src', \Shop::getURL() . '/' . PFAD_MEDIAFILES . 'Bilder/.lg/' . $name);

            $instance->getImageAttributes(\Shop::getURL() . '/' . PFAD_MEDIAFILES . 'Bilder/.xs/' . $name);
        }
        if (!empty($instance->getProperty("class"))) {
            $instance->addClass($instance->getProperty("class"));
        }
        if ($instance->getProperty('background-flag') === 'video') {
            $instance->setStyle('overflow', 'hidden');

            $name = \explode('/', $instance->getProperty('video-poster'));
            $name = \end($name);

            $instance->setProperty(
                'video-poster-url',
                \Shop::getURL() . '/' . PFAD_MEDIAFILES . 'Bilder/.xs/' . $name);

            $name = \explode('/', $instance->getProperty('video-src'));
            $name = \end($name);

            $instance->setProperty(
                'video-src-url',
                \Shop::getURL() . '/' . PFAD_MEDIAFILES . 'Videos/' . $name);
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
            'class'           => [
                'label'      => 'CSS Klasse',
                'dspl_width' => 50,
            ],
            'background-flag' => [
                'label'   => 'Hintergrund nutzen?',
                'type'    => 'radio',
                'options' => [
                    'image' => 'mitlaufendes Bild (parallax)',
                    'video' => 'Hintergrundvideo',
                    'false' => 'einfacher Container',
                ],
                'default' => 'false',
                'inline'  => true,
            ],
            'src'             => [
                'type'                 => 'image',
                'collapseControlStart' => true,
                'showOnProp'           => 'background-flag',
                'showOnPropValue'      => 'image',
                'dspl_width'           => 50,
                'collapseControlEnd' => true,
            ],
            'min-height'      => [
                'label'              => 'Mindesthöhe in px',
                'type'               => 'number',
                'default'            => 300,
                'dspl_width'         => 50,
            ],
            'video-src'       => [
                'label'                => 'Video',
                'type'                 => 'video',
                'collapseControlStart' => true,
                'showOnProp'           => 'background-flag',
                'showOnPropValue'      => 'video',
                'dspl_width'           => 100,
            ],
            'video-poster'    => [
                'label'              => 'Platzhalterbild',
                'type'               => 'image',
                'dspl_width'         => 100,
                'collapseControlEnd' => true,
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
