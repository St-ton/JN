<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Portlets;

/**
 * Class PortletImage
 */
class PortletGallery extends \OPCPortlet
{
    /**
     * @return string
     */
    public function getButton()
    {
        return '<img class="fa" src="' . \Shop::getURL() .'/'
            . PFAD_TEMPLATES
            . 'Evo/themes/base/images/cms_live_editor/Icon-ImageGallery.svg"></i>
            <br/>  Gallery';
    }

    public function getPreviewHtml()
    {
        unset($this->properties['gllry_images']['NEU']);
        if (!empty($this->properties['gllry_images'])) {
            usort(
                $this->properties['gllry_images'],
                function ($a, $b) {
                    return $a['nSort'] > $b['nSort'];
                }
            );
        }
        $this->properties['attr']['id'] = empty($this->properties['attr']['id']) ? uniqid('gllry_') : $this->properties['attr']['id'];

        foreach ($this->properties['gllry_images'] as &$slide) {
            $slide['srcStr'] = $this->getSrcString($slide['url'],
                [   'lg' => floatval($slide['width']['lg']/12),
                    'md' => floatval($slide['width']['md']/12),
                    'sm' => floatval($slide['width']['sm']/12),
                    'xs' => floatval($slide['width']['xs']/12)]);
        }

        $this
            ->addClass('row')
            ->addClass('gal-container');

        return (new \JTLSmarty(true))
            ->assign('properties', $this->properties)
            ->assign('attribString', $this->getAttribString())
            ->assign('styleString', $this->getStyleString())
            ->assign('popupEnabled', false)
            ->fetch('portlets/final.gallery.tpl');
    }

    public function getFinalHtml()
    {
        unset($this->properties['gllry_images']['NEU']);
        if (!empty($this->properties['gllry_images'])) {
            usort(
                $this->properties['gllry_images'],
                function ($a, $b) {
                    return $a['nSort'] > $b['nSort'];
                }
            );
        }
        $this->properties['attr']['id'] = empty($this->properties['attr']['id']) ? uniqid('gllry_') : $this->properties['attr']['id'];

        foreach ($this->properties['gllry_images'] as &$slide) {
            $slide['srcStr'] = $this->getSrcString($slide['url'],
                [   'lg' => floatval($slide['width']['lg']/12),
                    'md' => floatval($slide['width']['md']/12),
                    'sm' => floatval($slide['width']['sm']/12),
                    'xs' => floatval($slide['width']['xs']/12)]);
        }

        $this
            ->addClass('row')
            ->addClass('gal-container');

        return \Shop::Smarty()
            ->assign('properties', $this->properties)
            ->assign('attribString', $this->getAttribString())
            ->assign('styleString', $this->getStyleString())
            ->assign('popupEnabled', true)
            ->fetch('portlets/final.gallery.tpl');
    }

    public function getConfigPanelHtml()
    {
        return (new \JTLSmarty(true))
            ->assign('properties', $this->properties)
            ->fetch('portlets/settings.gallery.tpl');
    }

    public function getDefaultProps()
    {
        return [
            // general
            'gllry_height' => '250',
            'gllry_images' => [],
            // attributes
            'attr' => [
                'id'                 => '',
                'class'              => '',
                'data-wow-duration'  => '',
                'data-wow-delay'     => '',
                'data-wow-offset'    => '',
                'data-wow-iteration' => '',
            ],
            // animation
            'animation-style'     => '',
            // style
            'style' => [
                'color'               => '',
                'margin-top'          => '',
                'margin-right'        => '',
                'margin-bottom'       => '',
                'margin-left'         => '',
                'background-color'    => '',
                'padding-top'         => '',
                'padding-right'       => '',
                'padding-bottom'      => '',
                'padding-left'        => '',
                'border'              => '0',
                'border-top-width'    => '',
                'border-right-width'  => '',
                'border-bottom-width' => '',
                'border-left-width'   => '',
                'border-style'        => '',
                'border-color'        => '',
            ],
        ];
    }
}