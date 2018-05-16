<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace OPC\Portlets;

use OPC\PortletInstance;

class Gallery extends \OPC\Portlet
{
    public function getPreviewHtml($instance)
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
            if(empty($slide['width']['xs'])) $slide['width']['xs'] = 12;
            if(empty($slide['width']['sm'])) $slide['width']['sm'] = $slide['width']['xs'];
            if(empty($slide['width']['md'])) $slide['width']['md'] = $slide['width']['sm'];
            if(empty($slide['width']['lg'])) $slide['width']['lg'] = $slide['width']['md'];
            $slide['img_attr'] = $instance->getImageAttributes($slide['url'], null, null, $slide['width']);
        }
        $instance->setProperty('gllry_images',$images);
        $id = !empty($instance->getProperty('id')) ? $instance->getProperty('id') : uniqid('gllry_');
        $instance->setAttribute('id',$id);

        $instance
            ->addClass('row')
            ->addClass('gal-container');

        return $this->getPreviewHtmlFromTpl($instance);
    }

    public function getFinalHtml($instance)
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
            if(empty($slide['width']['xs'])) $slide['width']['xs'] = 12;
            if(empty($slide['width']['sm'])) $slide['width']['sm'] = $slide['width']['xs'];
            if(empty($slide['width']['md'])) $slide['width']['md'] = $slide['width']['sm'];
            if(empty($slide['width']['lg'])) $slide['width']['lg'] = $slide['width']['md'];
            $slide['img_attr'] = $instance->getImageAttributes($slide['url'], null, null, $slide['width']);
        }

        $instance->setProperty('gllry_images',$images);
        $id = !empty($instance->getProperty('id')) ? $instance->getProperty('id') : uniqid('gllry_');
        $instance->setAttribute('id',$id);

        $instance
            ->addClass('row')
            ->addClass('gal-container');

        return $this->getFinalHtmlFromTpl($instance);
    }

    public function getButtonHtml()
    {
        return '<img class="fa" src="' . $this->getDefaultIconSvgUrl() . '"></i><br>Gallery';
    }

    public function getConfigPanelHtml($instance)
    {
        return $this->getAutoConfigPanelHtml($instance);
    }

    public function getPropertyDesc()
    {
        return [
            'gllry_height' => [
                'label'      => 'height of preview',
                'type'       => 'number',
                'default'    => 250,
                'dspl_width' => 50,
            ],
            'id'           => [
                'label'      => 'id',
                'dspl_width' => 50,
            ],
            'class'        => [
                'label'      => 'CSS Class',
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

    public function getPropertyTabs()
    {
        return [
            'Images' => ['gllry_images'],
            'Styles' => 'styles',
        ];
    }
}