<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace OPC\Portlets;

use OPC\PortletInstance;

/**
 * Class Video
 * @package OPC\Portlets
 */
class Video extends \OPC\Portlet
{
    /**
     * @param PortletInstance $instance
     * @return string
     * @throws \Exception
     */
    public function getPreviewHtml(PortletInstance $instance): string
    {
        return $this->getPreviewHtmlFromTpl($instance);
    }

    /**
     * @param PortletInstance $instance
     * @return string
     * @throws \Exception
     */
    public function getFinalHtml(PortletInstance $instance): string
    {
        return $this->getFinalHtmlFromTpl($instance);
    }

    /**
     * @return string
     */
    public function getButtonHtml(): string
    {
        return '<i class="fa fa-film"></i><br/> Video';
    }

    /**
     * @return array
     */
    public function getPropertyDesc(): array
    {
        return [
            // general
            'video-title'       => [
                'label'      => 'Titel',
                'dspl_width' => 50,
            ],
            'video-class'       => [
                'label'      => 'CSS Klasse',
                'dspl_width' => 50,
            ],
            'video-responsive'  => [
                'label'   => 'responsive einbetten?',
                'type'    => 'radio',
                'inline'  => true,
                'options' => [
                    true  => 'ja',
                    false => 'nein',
                ],
                'default' => true,
            ],
            'video-width'       => [
                'label'      => 'Breite',
                'type'       => 'number',
                'default'    => 600,
                'dspl_width' => 50,
            ],
            'video-height'      => [
                'label'      => 'Höhe',
                'type'       => 'number',
                'default'    => 338,
                'dspl_width' => 50,
            ],
            'video-vendor'      => [
                'label'   => 'Quelle',
                'type'    => 'select',
                'options' => [
                    'youtube' => 'YouTube',
                    'vimeo'   => 'Vimeo',
                    'local'   => 'lokales Video'
                ],
                'default' => 'youtube',
            ],
            'video-yt-id'       => [
                'label'                => 'Video ID',
                'default'              => 'xITQHgJ3RRo',
                'help'                 => 'Bitte nur die ID des Videos eingeben. Bsp.: xITQHgJ3RRo',
                'collapseControlStart' => true,
                'showOnProp'           => 'video-vendor',
                'showOnPropValue'      => 'youtube',
            ],
            'video-yt-start'    => [
                'label'      => 'Start',
                'type'       => 'number',
                'dspl_width' => 50,
            ],
            'video-yt-end'      => [
                'label'      => 'Ende',
                'type'       => 'number',
                'dspl_width' => 50,
            ],
            'video-yt-controls' => [
                'label'      => 'Steuerelemente anzeigen?',
                'type'       => 'radio',
                'inline'     => true,
                'options'    => [
                    '1' => 'ja',
                    '0' => 'nein',
                ],
                'default'    => '1',
                'dspl_width' => 50,
            ],
            'video-yt-rel'      => [
                'label'      => 'ähnliche Videos anzeigen?',
                'type'       => 'radio',
                'inline'     => true,
                'options'    => [
                    '1' => 'ja',
                    '0' => 'nein',
                ],
                'default'    => '0',
                'dspl_width' => 50,
            ],
            'video-yt-color'    => [
                'label'        => 'Farbe',
                'type'         => 'radio',
                'inline'       => true,
                'options'      => [
                    'white' => 'weiß',
                    'red'   => 'rot',
                ],
                'default'      => 'white',
                'dspl_width'   => 50,
                'color-format' => '#',
            ],
            'video-yt-playlist' => [
                'label'              => 'Playlist',
                'help'               => 'Geben Sie die Video-IDs durch Komma getrennt ein . Bsp.: xITQHgJ3RRo,sNYv0JgrUlw',
                'collapseControlEnd' => true,
            ],

            'video-vim-id'     => [
                'label'                => 'Video ID',
                'default'              => '141374353',
                'nonempty'             => true,
                'help'                 => 'Bitte nur die ID des Videos eingeben. Bsp.: 141374353',
                'collapseControlStart' => true,
                'showOnProp'           => 'video-vendor',
                'showOnPropValue'      => 'vimeo',
            ],
            'video-vim-loop'   => [
                'label'      => 'Video nach Ablauf wiederholen?',
                'type'       => 'radio',
                'inline'     => true,
                'options'    => [
                    '1' => 'ja',
                    '0' => 'nein',
                ],
                'default'    => '0',
                'dspl_width' => 50,
            ],
            'video-vim-img'    => [
                'label'      => 'Bild anzeigen?',
                'type'       => 'radio',
                'inline'     => true,
                'options'    => [
                    '1' => 'ja',
                    '0' => 'nein',
                ],
                'default'    => '0',
                'dspl_width' => 50,
            ],
            'video-vim-title'  => [
                'label'      => 'Titel anzeigen?',
                'type'       => 'radio',
                'inline'     => true,
                'options'    => [
                    '1' => 'ja',
                    '0' => 'nein',
                ],
                'default'    => '1',
                'dspl_width' => 50,
            ],
            'video-vim-byline' => [
                'label'      => 'Verfasserangabe anzeigen?',
                'type'       => 'radio',
                'inline'     => true,
                'options'    => [
                    '1' => 'ja',
                    '0' => 'nein',
                ],
                'default'    => '0',
                'dspl_width' => 50,
            ],
            'video-vim-color'  => [
                'label'              => 'Farbe',
                'type'               => 'color',
                'default'            => '#ffffff',
                'dspl_width'         => 50,
                'collapseControlEnd' => true,
            ],
            'video-local-url'  => [
                'label'                => 'Video URL',
                'type'                 => 'video',
                'collapseControlStart' => true,
                'showOnProp'           => 'video-vendor',
                'showOnPropValue'      => 'local',
                'dspl_width'           => 50,
                'collapseControlEnd'   => true,
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
