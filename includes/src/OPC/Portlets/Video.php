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
                'label'      => 'Titel',
                'width'      => 50,
            ],
            'video-class'       => [
                'label'      => 'CSS Klasse',
                'width'      => 50,
            ],
            'video-responsive'  => [
                'type'    => InputType::RADIO,
                'label'   => 'responsive einbetten?',
                'default' => true,
                'options' => [
                    false => 'nein',
                    true  => 'ja',
                ],
            ],
            'video-width'       => [
                'type'       => InputType::NUMBER,
                'label'      => 'Breite',
                'default'    => 600,
                'width'      => 50,
            ],
            'video-height'      => [
                'type'       => InputType::NUMBER,
                'label'      => 'Höhe',
                'default'    => 338,
                'width'      => 50,
            ],
            'video-vendor'      => [
                'label'   => 'Quelle',
                'type'    => InputType::SELECT,
                'default' => 'youtube',
                'options' => [
                    'youtube' => 'YouTube',
                    'vimeo'   => 'Vimeo',
                    'local'   => 'lokales Video',
                ],
                'childrenFor' => [
                    'youtube' => [
                        'video-yt-hint'     => [
                            'label'                => 'Hinweis',
                            'type'                 => InputType::HINT,
                            'class'                => 'danger',
                            'text'                 => 'In ihren Datenschutzerklärungen sollten sie darauf hinweisen, ' .
                            'dass YouTube-Videos im „erweiterten Datenschutzmodus“ in ihren Seiten eingebettet sind. ' .
                            'Die Nutzer sollten erfahren, dass der Aufruf der Seiten zu einer Verbindungsaufnahme mit '.
                            'YouTube und dem DoubleClick-Netzwerk führt. Man sollte ihnen auch nicht verschweigen, ' .
                            'dass schon ein Klick auf das Video weitere Datenverarbeitungsvorgänge auslösen kann, ' .
                            'auf die der Website-Betreiber keinen Einfluss mehr hat.',
                        ],
                        'video-yt-id'       => [
                            'label'   => 'Video ID',
                            'default' => 'xITQHgJ3RRo',
                            'help'    => 'Bitte nur die ID des Videos eingeben. Bsp.: xITQHgJ3RRo',
                        ],
                        'video-yt-start'    => [
                            'label'      => 'Start',
                            'type'       => InputType::NUMBER,
                            'width'      => 50,
                        ],
                        'video-yt-end'      => [
                            'label'      => 'Ende',
                            'type'       => InputType::NUMBER,
                            'width'      => 50,
                        ],
                        'video-yt-controls' => [
                            'label'      => 'Steuerelemente anzeigen?',
                            'type'       => InputType::RADIO,
                            'inline'     => true,
                            'options'    => [
                                '1' => 'ja',
                                '0' => 'nein',
                            ],
                            'default'    => '1',
                            'width'      => 50,
                        ],
                        'video-yt-rel'      => [
                            'label'      => 'ähnliche Videos anzeigen?',
                            'type'       => InputType::RADIO,
                            'inline'     => true,
                            'options'    => [
                                '1' => 'ja',
                                '0' => 'nein',
                            ],
                            'default'    => '0',
                            'width'      => 50,
                        ],
                        'video-yt-color'    => [
                            'label'        => 'Farbe',
                            'type'         => InputType::RADIO,
                            'inline'       => true,
                            'options'      => [
                                'white' => 'weiß',
                                'red'   => 'rot',
                            ],
                            'default'      => 'white',
                            'width'        => 50,
                            'color-format' => '#',
                        ],
                        'video-yt-playlist' => [
                            'label'              => 'Playlist',
                            'help'               => 'Geben Sie die Video-IDs durch Komma getrennt ein. ' .
                                'Bsp.: xITQHgJ3RRo,sNYv0JgrUlw',
                        ],
                    ],
                    'vimeo'   => [
                        'video-vim-id'      => [
                            'label'                => 'Video ID',
                            'default'              => '141374353',
                            'nonempty'             => true,
                            'help'                 => 'Bitte nur die ID des Videos eingeben. Bsp.: 141374353',
                        ],
                        'video-vim-loop'    => [
                            'label'      => 'Video nach Ablauf wiederholen?',
                            'type'       => InputType::RADIO,
                            'inline'     => true,
                            'options'    => [
                                '1' => 'ja',
                                '0' => 'nein',
                            ],
                            'default'    => '0',
                            'width'      => 50,
                        ],
                        'video-vim-img'     => [
                            'label'      => 'Bild anzeigen?',
                            'type'       => InputType::RADIO,
                            'inline'     => true,
                            'options'    => [
                                '1' => 'ja',
                                '0' => 'nein',
                            ],
                            'default'    => '0',
                            'width'      => 50,
                        ],
                        'video-vim-title'   => [
                            'label'      => 'Titel anzeigen?',
                            'type'       => InputType::RADIO,
                            'inline'     => true,
                            'options'    => [
                                '1' => 'ja',
                                '0' => 'nein',
                            ],
                            'default'    => '1',
                            'width'      => 50,
                        ],
                        'video-vim-byline'  => [
                            'label'      => 'Verfasserangabe anzeigen?',
                            'type'       => InputType::RADIO,
                            'inline'     => true,
                            'options'    => [
                                '1' => 'ja',
                                '0' => 'nein',
                            ],
                            'default'    => '0',
                            'width'      => 50,
                        ],
                        'video-vim-color'   => [
                            'label'              => 'Farbe',
                            'type'               => InputType::COLOR,
                            'default'            => '#ffffff',
                            'width'              => 50,
                        ],
                    ],
                    'local'   => [
                        'video-local-url'   => [
                            'label'                => 'Video URL',
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
            'Styles'    => 'styles',
            'Animation' => 'animations',
        ];
    }
}
