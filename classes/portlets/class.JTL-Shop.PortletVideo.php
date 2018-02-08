<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class PortletHeading
 */
class PortletVideo extends CMSPortlet
{
    /**
     * @return string
     */
    public function getButton()
    {
        return '<i class="fa fa-film"></i><br/> Video';
    }

    /**
     * @return string
     */
    public function getPreviewHtml($renderLinks = false)
    {
        $this->properties['video-yt-playlist'] = !empty($this->properties['video-yt-playlist'])
            ? $this->properties['video-yt-playlist']
            : $this->properties['video-yt-id'];

        return (new JTLSmarty(true))
            ->assign('properties', $this->properties)
            ->assign('styleString', $this->getStyleString())
            ->assign('attribString', $this->getAttribString())
            ->fetch('portlets/final.video.tpl');
    }

    /**
     * @return string
     */
    public function getFinalHtml()
    {
        $this->properties['video-yt-playlist'] = !empty($this->properties['video-yt-playlist'])
            ? $this->properties['video-yt-playlist']
            : $this->properties['video-yt-id'];

        return Shop::Smarty()->assign('properties', $this->properties)
            ->assign('styleString', $this->getStyleString())
            ->assign('attribString', $this->getAttribString())
            ->fetch('portlets/final.video.tpl');
    }

    /**
     * @return array
     */
    public function getDefaultProps()
    {
        return [
            // general
            'video-title'          => '',
            'video-vendor'         => 'youtube',
            'video-yt-id'          => 'xITQHgJ3RRo',
            'video-yt-responsive'  => '1',
            'video-yt-width'       => '600',
            'video-yt-height'      => '338',
            'video-yt-start'       => '',
            'video-yt-end'         => '',
            'video-yt-autoplay'    => '0',
            'video-yt-controls'    => '1',
            'video-yt-loop'        => '0',
            'video-yt-rel'         => '1',
            'video-yt-color'       => 'white',
            'video-yt-playlist'    => '',

            'video-vim-id'         => '141374353',
            'video-vim-responsive' => '1',
            'video-vim-width'      => '600',
            'video-vim-height'     => '338',
            'video-vim-autoplay'   => '0',
            'video-vim-loop'       => '0',
            'video-vim-img'        => '0',
            'video-vim-title'      => '1',
            'video-vim-byline'     => '1',
            'video-vim-color'      => '#ffffff',
            // animation
            'animation-style'      => '',
            // attributes
            'attr' => [
                'class'              => '',
                'data-wow-duration'  => '',
                'data-wow-delay'     => '',
                'data-wow-offset'    => '',
                'data-wow-iteration' => '',
            ],
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
                'border-top-width'    => '',
                'border-right-width'  => '',
                'border-bottom-width' => '',
                'border-left-width'   => '',
                'border-style'        => '',
                'border-color'        => '',
            ],
        ];
    }

    /**
     * @return string
     */
    public function getConfigPanelHtml()
    {
        return (new JTLSmarty(true))
            ->assign('properties', $this->properties)
            ->fetch('portlets/settings.video.tpl');
    }
}