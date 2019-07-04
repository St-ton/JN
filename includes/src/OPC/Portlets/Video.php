<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace JTL\OPC\Portlets;

use JTL\OPC\InputType;
use JTL\OPC\Portlet;

/**
 * Class Video
 * @package JTL\OPC\Portlets
 */
class Video extends Portlet
{
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
            'video-responsive'  => [
                'type'    => InputType::RADIO,
                'label'   => __('embedResponsive'),
                'default' => true,
                'options' => [
                    false => __('no'),
                    true  => __('yes'),
                ],
            ],
            'video-width'       => [
                'type'       => InputType::NUMBER,
                'label'      => __('width'),
                'default'    => 600,
                'width'      => 50,
            ],
            'video-height'      => [
                'type'       => InputType::NUMBER,
                'label'      => __('height'),
                'default'    => 338,
                'width'      => 50,
            ],
            'video-vendor'      => [
                'label'   => __('source'),
                'type'    => InputType::SELECT,
                'default' => 'youtube',
                'options' => [
                    'youtube' => __('YouTube'),
                    'vimeo'   => __('Vimeo'),
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
                            'label'      => __('start'),
                            'type'       => InputType::NUMBER,
                            'width'      => 50,
                        ],
                        'video-yt-end'      => [
                            'label'      => __('end'),
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
                            'width'      => 50,
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
                            'width'      => 50,
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
                            'width'        => 50,
                            'color-format' => '#',
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
                        'video-local-url'   => [
                            'label'                => __('videoURL'),
                            'type'                 => InputType::VIDEO,
                            'width'                => 50,
                        ],
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
