<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace OPC\Portlets;

use OPC\PortletInstance;

/**
 * Class Gallery
 * @package OPC\Portlets
 */
class Gallery extends \OPC\Portlet
{
    /**
     * @param PortletInstance $instance
     * @return string
     * @throws \Exception
     */
    public function getPreviewHtml(PortletInstance $instance): string
    {
        $instance->setProperty('uid', uniqid('gllry-', false));
        $images = $instance->getProperty('gllry_images');
        unset($images['NEU']);
        if (!empty($images)) {
            usort(
                $images,
                function ($a, $b) {
                    return $a['nSort'] > $b['nSort'];
                }
            );
        }
        foreach ($images as &$slide) {
            if (empty($slide['width']['xs'])) {
                $slide['width']['xs'] = 12;
            }
            if (empty($slide['width']['sm'])) {
                $slide['width']['sm'] = $slide['width']['xs'];
            }
            if (empty($slide['width']['md'])) {
                $slide['width']['md'] = $slide['width']['sm'];
            }
            if (empty($slide['width']['lg'])) {
                $slide['width']['lg'] = $slide['width']['md'];
            }
            $slide['img_attr'] = $instance->getImageAttributes($slide['url'], null, null, $slide['width']);
        }
        unset($slide);
        $instance->setProperty('gllry_images', $images);
        $id = !empty($instance->getProperty('id')) ? $instance->getProperty('id') : uniqid('gllry_', false);
        $instance->setAttribute('id', $id);

        $instance->addClass('row')
                 ->addClass('gllry-container');

        return $this->getPreviewHtmlFromTpl($instance);
    }

    /**
     * @param PortletInstance $instance
     * @return string
     * @throws \Exception
     */
    public function getFinalHtml(PortletInstance $instance): string
    {
        $images = $instance->getProperty('gllry_images');
        unset($images['NEU']);
        if (!empty($images)) {
            usort(
                $images,
                function ($a, $b) {
                    return $a['nSort'] > $b['nSort'];
                }
            );
        }
        foreach ($images as &$slide) {
            if (empty($slide['width']['xs'])) {
                $slide['width']['xs'] = 12;
            }
            if (empty($slide['width']['sm'])) {
                $slide['width']['sm'] = $slide['width']['xs'];
            }
            if (empty($slide['width']['md'])) {
                $slide['width']['md'] = $slide['width']['sm'];
            }
            if (empty($slide['width']['lg'])) {
                $slide['width']['lg'] = $slide['width']['md'];
            }
            $slide['img_attr'] = $instance->getImageAttributes($slide['url'], null, null, $slide['width']);
        }
        unset($slide);
        $instance->setProperty('gllry_images', $images);

        $instance->addClass('row')
                 ->addClass('gllry-container');

        return $this->getFinalHtmlFromTpl($instance);
    }

    /**
     * @return string
     */
    public function getButtonHtml(): string
    {
        return '<img class="fa" src="' . $this->getDefaultIconSvgUrl() . '"></i><br>Gallery';
    }

    /**
     * @return array
     */
    public function getPropertyDesc(): array
    {
        return [
            'gllry_height' => [
                'label'      => 'Höhe der Vorschaubilder',
                'type'       => 'number',
                'default'    => 250,
                'dspl_width' => 50,
            ],
            'class'        => [
                'label'      => 'CSS Klasse',
                'dspl_width' => 50,
            ],
            'gllry_images' => [
                'label'      => 'Bilder',
                'type'       => 'image-set',
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
            'Images' => ['gllry_images'],
            'Styles' => 'styles',
        ];
    }
}
