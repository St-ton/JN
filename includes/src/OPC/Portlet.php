<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace OPC;

/**
 * Class Portlet
 * @package OPC
 */
abstract class Portlet implements \JsonSerializable
{
    use PortletHtml;

    /**
     * @var int
     */
    protected $id = 0;

    /**
     * @var null|\Plugin
     */
    protected $plugin = null;

    /**
     * @var string
     */
    protected $title = '';

    /**
     * @var string
     */
    protected $class = '';

    /**
     * @var string
     */
    protected $group = '';

    /**
     * @var bool
     */
    protected $active = false;

    /**
     * Portlet constructor.
     */
    final public function __construct()
    {
    }

    /**
     * @return array
     */
    final public function getDefaultProps()
    {
        $defProps = [];

        foreach ($this->getPropertyDesc() as $name => $propDesc) {
            $defProps[$name] = $propDesc['default'] ?? '';
        }

        return $defProps;
    }

    /**
     * @return array
     */
    public function getPropertyDesc()
    {
        return [];
    }

    public function getStylesPropertyDesc()
    {
        return [
            'color' => [
                'label'   => 'Schriftfarbe',
                'type'    => 'color',
                'default' => '',
            ],
            'background-color' => [
                'label'   => 'Hintergrundfarbe',
                'type'    => 'color',
                'default' => '',
            ],
            'font-size' => [
                'label'   => 'Schriftgröße',
                'default' => '',
            ],
            'margin-top' => [
                'label' => 'margin-top',
                'type' => 'number',
                'default' => '',
                'class' => 'css-input-grid',
                'dspl_width' => 25
            ],
            'margin-right' => [
                'label' => 'margin-right',
                'type' => 'number',
                'default' => '',
                'class' => 'css-input-grid',
                'dspl_width' => 25
            ],
            'margin-bottom' => [
                'label' => 'margin-bottom',
                'type' => 'number',
                'default' => '',
                'class' => 'css-input-grid',
                'dspl_width' => 25
            ],
            'margin-left' => [
                'label' => 'margin-left',
                'type' => 'number',
                'default' => '',
                'class' => 'css-input-grid',
                'dspl_width' => 25
            ],
            'padding-top' => [
                'label' => 'padding-top',
                'type' => 'number',
                'default' => '',
                'class' => 'css-input-grid',
                'dspl_width' => 25
            ],
            'padding-right' => [
                'label' => 'padding-right',
                'type' => 'number',
                'default' => '',
                'class' => 'css-input-grid',
                'dspl_width' => 25
            ],
            'padding-bottom' => [
                'label' => 'padding-bottom',
                'type' => 'number',
                'default' => '',
                'class' => 'css-input-grid',
                'dspl_width' => 25
            ],
            'padding-left' => [
                'label' => 'padding-left',
                'type' => 'number',
                'default' => '',
                'class' => 'css-input-grid',
                'dspl_width' => 25
            ],
            'border-width' => [
                'label' => 'border-width',
                'type' => 'number',
                'default' => '',
                'class' => 'css-input-grid',
                'dspl_width' => 50
            ],
            'border-style' => [
                'label' => 'border-style',
                'type' => 'select',
                'options' => [
                    '',
                    'hidden',
                    'dotted',
                    'dashed',
                    'solid',
                    'double',
                    'groove',
                    'ridge',
                    'inset',
                    'outset',
                    'initial',
                    'inherit'
                ],
                'dspl_width' => 50
            ],
            'border-color' => [
                'label'      => 'border-color',
                'type'       => 'color',
                'dspl_width' => 100
            ]
        ];
    }

    public function getAnimationsPropertyDesc()
    {
        return [
            'animation-style'    => [
                'label'      => 'animation-style',
                'type'       => 'select',
                'options'    => [
                    'optgroup1' => [
                        'label'   => 'Attention Seekers',
                        'options' => [
                            '',
                            'bounce',
                            'flash',
                            'pulse',
                            'rubberBand',
                            'shake',
                            'swing',
                            'tada',
                            'wobble',
                            'jello',
                        ],
                    ],
                    'optgroup2' => [
                        'label'   => 'Bouncing Entrances',
                        'options' => [
                            'bounceIn',
                            'bounceInDown',
                            'bounceInLeft',
                            'bounceInRight',
                            'bounceInUp',
                        ],
                    ],
                    'optgroup3' => [
                        'label'   => 'Fading Entrances',
                        'options' => [
                            'fadeIn',
                            'fadeInDown',
                            'fadeInDownBig',
                            'fadeInLeft',
                            'fadeInLeftBig',
                        ],
                    ],
                    'optgroup4' => [
                        'label'   => 'Flippers',
                        'options' => [
                            'flip',
                            'flipInX',
                            'flipInY',
                        ],
                    ],
                    'optgroup5' => [
                        'label'   => 'lightspeed',
                        'options' => [
                            'lightSpeedIn',
                        ],
                    ],
                    'optgroup6' => [
                        'label'   => 'Rotating Entrances',
                        'options' => [
                            'rotateIn',
                            'rotateInDownLeft',
                            'rotateInDownRight',
                            'rotateInUpLeft',
                            'rotateInUpRight',
                        ],
                    ],
                    'optgroup7' => [
                        'label'   => 'Sliding Entrances',
                        'options' => [
                            'slideInUp',
                            'slideInDown',
                            'slideInLeft',
                            'slideInRight',
                        ],
                    ],
                    'optgroup8' => [
                        'label'   => 'Zoom Entrances',
                        'options' => [
                            'zoomIn',
                            'zoomInDown',
                            'zoomInLeft',
                            'zoomInRight',
                            'zoomInUp',
                        ],
                    ],
                    'optgroup9' => [
                        'label'   => 'Specials',
                        'options' => [
                            'hinge',
                            'rollIn',
                        ],
                    ],
                ],
                'dspl_width' => 50,
            ],
            'data-wow-duration'  => [
                'label'       => 'duration',
                'help'        => 'Change the animation duration.',
                'placeholder' => '1s',
                'dspl_width'  => 50,
            ],
            'data-wow-delay'     => [
                'label'      => 'Delay',
                'help'       => 'Delay before the animation starts.',
                'dspl_width' => 50,
            ],
            'data-wow-offset'    => [
                'label'       => 'Offset',
                'type'        => 'number',
                'placeholder' => 200,
                'help'        => 'Distance to start the animation.',
                'dspl_width'  => 50,
            ],
            'data-wow-Iteration' => [
                'label'      => 'iteration',
                'type'       => 'number',
                'help'       => 'The animation number times is repeated.',
                'dspl_width' => 50,
            ]
        ];
    }

    /**
     * @return array
     */
    public function getPropertyTabs()
    {
        return [];
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getPluginId()
    {
        return $this->plugin === null ? 0 : $this->plugin->kPlugin;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @return string
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * @param int $id
     * @return Portlet
     */
    public function setId(int $id) : Portlet
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @param int $pluginId
     * @return Portlet
     */
    public function setPluginId(int $pluginId) : Portlet
    {
        if ($pluginId > 0) {
            $this->plugin = new \Plugin($pluginId);
        } else {
            $this->plugin = null;
        }

        return $this;
    }

    /**
     * @param string $title
     * @return Portlet
     */
    public function setTitle(string $title) : Portlet
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @param string $class
     * @return Portlet
     */
    public function setClass(string $class) : Portlet
    {
        $this->class = $class;

        return $this;
    }

    /**
     * @param string $group
     * @return Portlet
     */
    public function setGroup(string $group) : Portlet
    {
        $this->group = $group;

        return $this;
    }

    /**
     * @return null|\Plugin
     */
    public function getPlugin()
    {
        return $this->plugin;
    }

    /**
     * @param bool $active
     * @return Portlet
     */
    public function setActive(bool $active) : Portlet
    {
        $this->active = (bool)$active;

        return $this;
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'id'           => $this->getId(),
            'pluginId'     => $this->getPluginId(),
            'title'        => $this->getTitle(),
            'class'        => $this->getClass(),
            'group'        => $this->getGroup(),
            'active'       => $this->isActive(),
            'defaultProps' => $this->getDefaultProps(),
        ];
    }
}
