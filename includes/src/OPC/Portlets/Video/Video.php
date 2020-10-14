<?php declare(strict_types=1);

namespace JTL\OPC\Portlets\Video;

use JTL\OPC\InputType;
use JTL\OPC\Portlet;
use JTL\OPC\PortletInstance;

/**
 * Class Video
 * @package JTL\OPC\Portlets
 */
class Video extends Portlet
{
    /**
     * @param PortletInstance $instance
     * @return string
     */
    public function getPreviewImageUrl(PortletInstance $instance): string
    {
        $vendor = $instance->getProperty('video-vendor');

        if ($vendor === 'youtube') {
            $videoId = $instance->getProperty('video-yt-id');
            $srcUrl  = 'http://i3.ytimg.com/vi/' . $videoId . '/maxresdefault.jpg';
        } elseif ($vendor === 'vimeo') {
            $videoId  = $instance->getProperty('video-vim-id');
            $videoXML = \unserialize(\file_get_contents('http://vimeo.com/api/v2/video/' . $videoId . '.php'));
            $srcUrl   = $videoXML[0]['thumbnail_large'];
        }

        $localPath = \PFAD_ROOT . \STORAGE_VIDEO_THUMBS . $videoId . '.jpg';
        $localUrl  = \Shop::getURL() . '/' . \STORAGE_VIDEO_THUMBS . $videoId . '.jpg';

        if (!\is_file($localPath)) {
            \file_put_contents($localPath, \file_get_contents($srcUrl));
        }

        return $localUrl;
    }

    /**
     * @return string
     */
    public function getPreviewOverlayUrl(): string
    {
        return \Shop::getURL() . '/' . \PFAD_INCLUDES . 'src/OPC/Portlets/Video/preview.svg';
    }

    /**
     * @return string
     */
    public function getButtonHtml(): string
    {
        return $this->getFontAwesomeButtonHtml('fas fa-film');
    }

    /**
     * @return array
     */
    public function getPropertyDesc(): array
    {
        return [
            // general
            'video-title'       => [
                'label'      => __('title'),
                'width'      => 50,
            ],
            'video-class'       => [
                'label'      => __('cssClass'),
                'width'      => 50,
            ],
            'video-width'       => [
                'type'       => InputType::NUMBER,
                'label'      => __('widthPx'),
                'default'    => 600,
                'width'      => 33,
            ],
            'video-height'      => [
                'type'       => InputType::NUMBER,
                'label'      => __('heightPx'),
                'default'    => 338,
                'width'      => 33,
            ],
            'video-responsive'  => [
                'type'    => InputType::RADIO,
                'label'   => __('embedResponsive'),
                'default' => true,
                'options' => [
                    true  => __('yes'),
                    false => __('no'),
                ],
                'width'      => 33,
            ],
            'video-vendor'      => [
                'label'   => __('source'),
                'type'    => InputType::SELECT,
                'default' => 'youtube',
                'options' => [
                    'youtube' => 'YouTube',
                    'vimeo'   => 'Vimeo',
                    'local'   => __('localVideo'),
                ],
                'childrenFor' => [
                    'youtube' => [
                        'video-yt-hint'     => [
                            'label'                => __('note'),
                            'type'                 => InputType::HINT,
                            'class'                => 'danger',
                            'text'                 => __('youtubeNote'),
                        ],
                        'video-yt-id'       => [
                            'label'   => __('videoID'),
                            'default' => 'xITQHgJ3RRo',
                            'help'    => __('videoIDHelpYoutube'),
                        ],
                        'video-yt-start'    => [
                            'label'      => __('startSec'),
                            'type'       => InputType::NUMBER,
                            'width'      => 50,
                        ],
                        'video-yt-end'      => [
                            'label'      => __('endSec'),
                            'type'       => InputType::NUMBER,
                            'width'      => 50,
                        ],
                        'video-yt-controls' => [
                            'label'      => __('showControls'),
                            'type'       => InputType::RADIO,
                            'inline'     => true,
                            'options'    => [
                                '1' => __('yes'),
                                '0' => __('no'),
                            ],
                            'default'    => '1',
                            'width'      => 33,
                        ],
                        'video-yt-rel'      => [
                            'label'      => __('showSimilarVideos'),
                            'type'       => InputType::RADIO,
                            'inline'     => true,
                            'options'    => [
                                '1' => __('yes'),
                                '0' => __('no'),
                            ],
                            'default'    => '0',
                            'width'      => 33,
                        ],
                        'video-yt-color'    => [
                            'label'        => __('color'),
                            'type'         => InputType::RADIO,
                            'inline'       => true,
                            'options'      => [
                                'white' => __('white'),
                                'red'   => __('red'),
                            ],
                            'default'      => 'white',
                            'width'        => 33,
                            'color-format' => '#',
                            'desc'         => __('colorYtDesc'),
                        ],
                        'video-yt-playlist' => [
                            'label'              => __('playlist'),
                            'help'               => __('playlistHelp'),
                        ],
                    ],
                    'vimeo'   => [
                        'video-vim-id'      => [
                            'label'                => __('videoID'),
                            'default'              => '141374353',
                            'nonempty'             => true,
                            'help'                 => __('videoIDHelpVimeo'),
                        ],
                        'video-vim-loop'    => [
                            'label'      => __('repeatVideo'),
                            'type'       => InputType::RADIO,
                            'inline'     => true,
                            'options'    => [
                                '1' => __('yes'),
                                '0' => __('no'),
                            ],
                            'default'    => '0',
                            'width'      => 50,
                        ],
                        'video-vim-img'     => [
                            'label'      => __('showImage'),
                            'type'       => InputType::RADIO,
                            'inline'     => true,
                            'options'    => [
                                '1' => __('yes'),
                                '0' => __('no'),
                            ],
                            'default'    => '0',
                            'width'      => 50,
                        ],
                        'video-vim-title'   => [
                            'label'      => __('showTitle'),
                            'type'       => InputType::RADIO,
                            'inline'     => true,
                            'options'    => [
                                '1' => __('yes'),
                                '0' => __('no'),
                            ],
                            'default'    => '1',
                            'width'      => 50,
                        ],
                        'video-vim-byline'  => [
                            'label'      => __('showAuthorInformation'),
                            'type'       => InputType::RADIO,
                            'inline'     => true,
                            'options'    => [
                                '1' => __('yes'),
                                '0' => __('no'),
                            ],
                            'default'    => '0',
                            'width'      => 50,
                        ],
                        'video-vim-color'   => [
                            'label'              => __('color'),
                            'type'               => InputType::COLOR,
                            'default'            => '#ffffff',
                            'width'              => 50,
                        ],
                    ],
                    'local'   => [
                        'video-local-url'      => [
                            'label'    => __('videoURL'),
                            'type'                 => InputType::VIDEO,
                            'width'                => 50,
                        ],
                        'video-local-loop' => [
                            'label'   => __('repeatVideo'),
                            'type'    => InputType::CHECKBOX,
                            'width'   => 50,
                        ],
                        'video-local-autoplay' => [
                            'label'   => __('autoplayVideo'),
                            'type'    => InputType::CHECKBOX,
                            'width'   => 33,
                        ],
                        'video-local-mute'    => [
                            'label'   => __('muteVideo'),
                            'type'    => InputType::CHECKBOX,
                            'width'   => 33,
                        ],
                        'video-local-controls' => [
                            'label'   => __('showControls'),
                            'type'    => InputType::CHECKBOX,
                            'width'   => 33,
                            'default' => '1',
                        ]
                    ]
                ]
            ],
        ];
    }

    /**
     * @return array
     */
    public function getPropertyTabs(): array
    {
        return [
            __('Styles')    => 'styles',
            __('Animation') => 'animations',
        ];
    }
}
