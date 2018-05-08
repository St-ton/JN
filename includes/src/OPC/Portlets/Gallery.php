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

        $instance->setProperty('gllry_images',$images);
        $id = !empty($instance->getProperty('id')) ? $instance->getProperty('id') : uniqid('gllry_');
        $instance->setAttribute('id',$id);

        /*foreach ($images as &$slide) {
            $slide['srcStr'] = $this->getSrcString($slide['url'],
                [   'lg' => floatval($slide['width']['lg']/12),
                    'md' => floatval($slide['width']['md']/12),
                    'sm' => floatval($slide['width']['sm']/12),
                    'xs' => floatval($slide['width']['xs']/12)]);
        }*/

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

        $instance->setProperty('gllry_images',$images);
        $id = !empty($instance->getProperty('id')) ? $instance->getProperty('id') : uniqid('gllry_');
        $instance->setAttribute('id',$id);

        /*foreach ($images as &$slide) {
            $slide['srcStr'] = $this->getSrcString($slide['url'],
                [   'lg' => floatval($slide['width']['lg']/12),
                    'md' => floatval($slide['width']['md']/12),
                    'sm' => floatval($slide['width']['sm']/12),
                    'xs' => floatval($slide['width']['xs']/12)]);
        }*/

        $instance
            ->addClass('row')
            ->addClass('gal-container');

        return $this->getFinalHtmlFromTpl($instance);
    }

    public function getButtonHtml()
    {
        return '<img class="fa" src="' . \Shop::getURL() .'/'
            . PFAD_TEMPLATES
            . 'Evo/themes/base/images/opc/Icon-ImageGallery.svg"></i>
            <br/>  Gallery';
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
                'label' => 'Bilder',
                'type'  => 'image-set',
                'default' => [],
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