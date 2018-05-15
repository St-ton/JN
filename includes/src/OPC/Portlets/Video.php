<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace OPC\Portlets;

use OPC\PortletInstance;

class Video extends \OPC\Portlet
{
    public function getPreviewHtml($instance)
    {
        $ret = '<div '.$instance->getAttributeString(). ' ' . $instance->getDataAttributeString().'>';
        if($instance->getProperty('video-vendor') === 'youtube') {
            $ret .= '<img src="https://img.youtube.com/vi/' . $instance->getProperty('video-yt-id') . '/maxresdefault.jpg" alt="YouTube Video" class="img-responsive" style="width: 100%;"/>';
        } elseif($instance->getProperty('video-vendor') === 'vimeo') {
            $imgid = $instance->getProperty('video-vim-id');
            $hash  = unserialize(file_get_contents("http://vimeo.com/api/v2/video/$imgid.php"));
            $ret   .= '<img src="' . $hash[0]['thumbnail_large'] . '" alt="Vimeo Video" class="img-responsive" style="width: 100%;"/>';
        }
        $ret .='</div>';

        return $ret;
    }

    public function getFinalHtml($instance)
    {
        return $this->getFinalHtmlFromTpl($instance);
    }

    public function getButtonHtml()
    {
        return '<i class="fa fa-film"></i><br/> Video';
    }

    public function getConfigPanelHtml($instance)
    {
        return $this->getAutoConfigPanelHtml($instance);
    }

    public function getPropertyDesc()
    {
        return [
            // general
            'video-title'         => [
                'label'      => 'Titel',
                'dspl_width' => 50,
            ],
            'video-class'         => [
                'label'      => 'Class',
                'dspl_width' => 50,
            ],
            'video-vendor'        => [
                'label'   => 'Quelle',
                'type'    => 'select',
                'options' => [
                    'youtube',
                    'vimeo',
                    'upload'
                ],
                'default' => 'youtube',
            ],
            'video-yt-id'         => [
                'label'                => 'Video ID',
                'default'              => 'xITQHgJ3RRo',
                'help'                 => 'Bitte nur die ID des Videos eingeben. Bsp.: xITQHgJ3RRo',
                'collapseControlStart' => true,
                'showOnProp'           => 'video-vendor',
                'showOnPropValue'      => 'youtube',
            ],
            'video-yt-responsive' => [
                'label'   => 'responsive einbetten?',
                'type'    => 'checkbox',
                'default' => true,
            ],
            'video-yt-width'      => [
                'label'      => 'width',
                'type'       => 'number',
                'default'    => 600,
                'dspl_width' => 50,
            ],
            'video-yt-height'     => [
                'label'      => 'height',
                'type'       => 'number',
                'default'    => 338,
                'dspl_width' => 50,
            ],
            'video-yt-start'      => [
                'label'      => 'Start',
                'type'       => 'number',
                'dspl_width' => 50,
            ],
            'video-yt-end'        => [
                'label'      => 'Ende',
                'type'       => 'number',
                'dspl_width' => 50,
            ],
            'video-yt-controls'   => [
                'label'      => 'Steuerelemente anzeigen?',
                'type'       => 'radio',
                'inline'     => true,
                'options'    => [
                    'ja'   => '1',
                    'nein' => '0',
                ],
                'default'    => '1',
                'dspl_width' => 50,
            ],
            'video-yt-rel'        => [
                'label'      => 'ähnliche Videos anzeigen?',
                'type'       => 'radio',
                'inline'     => true,
                'options'    => [
                    'ja'   => '1',
                    'nein' => '0',
                ],
                'default'    => '0',
                'dspl_width' => 50,
            ],
            'video-yt-color'      => [
                'label'      => 'Farbe',
                'type'       => 'radio',
                'inline'     => true,
                'options'    => [
                    'weiß' => 'white',
                    'rot'  => 'red'
                ],
                'default'    => 'white',
                'dspl_width' => 50,
            ],
            'video-yt-playlist'   => [
                'label'              => 'Playlist',
                'help'               => 'Geben Sie die Video-IDs durch Komma getrennt ein . Bsp.: xITQHgJ3RRo,sNYv0JgrUlw',
                'collapseControlEnd' => true,
            ],

            'video-vim-id'         => [
                'label'                => 'Video ID',
                'default'              => '141374353',
                'help'                 => 'Bitte nur die ID des Videos eingeben. Bsp.: 141374353',
                'collapseControlStart' => true,
                'showOnProp'           => 'video-vendor',
                'showOnPropValue'      => 'vimeo',
            ],
            'video-vim-responsive' => [
                'label'   => 'responsive einbetten?',
                'type'    => 'checkbox',
                'default' => true,
            ],
            'video-vim-width'      => [
                'label'      => 'width',
                'type'       => 'number',
                'default'    => 600,
                'dspl_width' => 50,
            ],
            'video-vim-height'     => [
                'label'      => 'height',
                'type'       => 'number',
                'default'    => 338,
                'dspl_width' => 50,
            ],
            'video-vim-loop'       => [
                'label'      => 'Video nach ablauf wiederholen?',
                'type'       => 'radio',
                'inline'     => true,
                'options'    => [
                    'ja'   => '1',
                    'nein' => '0',
                ],
                'default'    => '0',
                'dspl_width' => 50,
            ],
            'video-vim-img'        => [
                'label'      => 'Bild anzeigen?',
                'type'       => 'radio',
                'inline'     => true,
                'options'    => [
                    'ja'   => '1',
                    'nein' => '0',
                ],
                'default'    => '0',
                'dspl_width' => 50,
            ],
            'video-vim-title'      => [
                'label'      => 'Titel anzeigen?',
                'type'       => 'radio',
                'inline'     => true,
                'options'    => [
                    'ja'   => '1',
                    'nein' => '0',
                ],
                'default'    => '1',
                'dspl_width' => 50,
            ],
            'video-vim-byline'     => [
                'label'      => 'Verfasserangabe anzeigen?',
                'type'       => 'radio',
                'inline'     => true,
                'options'    => [
                    'ja'   => '1',
                    'nein' => '0',
                ],
                'default'    => '0',
                'dspl_width' => 50,
            ],
            'video-vim-color'      => [
                'label'              => 'Farbe',
                'type'               => 'color',
                'default'            => '#ffffff',
                'dspl_width'         => 50,
                'collapseControlEnd' => true,
            ],
        ];
    }

    public function getPropertyTabs()
    {
        return [
            'Styles'    => 'styles',
            'Animation' => 'animations',
        ];
    }
}