<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace OPC;

abstract class Portlet implements \JsonSerializable
{
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
     * @param PortletInstance $instance
     * @return string
     * @throws \Exception
     */
    final protected function getPreviewHtmlFromTpl($instance)
    {
        return (new \JTLSmarty(true))
            ->assign('portlet', $this)
            ->assign('instance', $instance)
            ->fetch('portlets/' . $this->getClass() . '/preview.tpl');
    }

    /**
     * @param PortletInstance $instance
     * @return string
     * @throws \Exception
     */
    final protected function getFinalHtmlFromTpl($instance)
    {
        return \Shop::Smarty()
            ->assign('portlet', $this)
            ->assign('instance', $instance)
            ->fetch('portlets/' . $this->getClass() . '/final.tpl');
    }

    /**
     * @param PortletInstance $instance
     * @return string
     * @throws \Exception
     */
    final protected function getConfigPanelHtmlFromTpl($instance)
    {
        return (new \JTLSmarty(true))
            ->assign('portlet', $this)
            ->assign('instance', $instance)
            ->fetch('portlets/' . $this->getClass() . '/configpanel.tpl');
    }

    /**
     * @param PortletInstance $instance
     * @return string
     */
    final protected function getAutoConfigPanelHtml($instance)
    {
        $desc = $this->getPropertyDesc();
        $tabs = $this->getPropertyTabs();

        foreach ($tabs as $tabname => $propnames) {
            if (is_string($propnames)) {
                if ($propnames === 'styles') {
                    $tabs[$tabname] = $this->getStylesPropertyDesc();
                }
                if ($propnames === 'animations') {
                    $tabs[$tabname] = $this->getAnimationsPropertyDesc();
                }
            } else {
                foreach ($propnames as $i => $propname) {
                    $tabs[$tabname][$i] = $desc[$propname];
                    unset($desc[$propname]);
                }
            }
        }

        if (count($desc) > 0) {
            $tabs = ['Allgemein' => $desc] + $tabs;
        }

        $res  = '';
        $res .= "<ul class='nav nav-tabs'>";
        $i    = 0;

        foreach ($tabs as $tabname => $props) {
            $tabid  = preg_replace('/[^A-Za-z0-9\-]/', '', $tabname);
            $active = $i === 0 ? " class='active'" : "";
            $res   .= "<li$active>";
            $res   .= "<a href='#$tabid' data-toggle='tab'>$tabname</a></li>";
            $i ++;
        }

        $res .= "</ul>";
        $res .= "<div class='tab-content'>";
        $i    = 0;

        foreach ($tabs as $tabname => $props) {
            $tabid  = preg_replace('/[^A-Za-z0-9\-]/', '', $tabname);
            $active = $i === 0 ? " active" : "";
            $res   .= "<div class='tab-pane$active' id='$tabid'>";
            $res   .= "<div class='row'>";

            foreach ($props as $propname => $propDesc) {
                $containerId = !empty($propDesc['collapse']) ? $propname : null;
                $res        .= $this->getAutoConfigProp($instance, $propname, $propDesc, $containerId);

                if (!empty($propDesc['collapse'])) {
                    $res .= "<div class='collapse' id='collapseContainer$containerId'><div class='row'> ";
                    foreach ($propDesc['collapse'] as $colapsePropname => $collapsePropdesc) {
                        $res .= $this->getAutoConfigProp($instance, $colapsePropname, $collapsePropdesc);
                    }
                    $res .= "</div></div></div>"; // row, collapse, col-xs-*
                }
            }
            $i++;
            $res .= "</div></div>"; // row, tab-pane
        }
        $res .= "</div>";

        return $res;
    }

    protected function getAutoConfigProp(PortletInstance $instance, $propname, $propDesc, $containerId = null)
    {
        $res   = '';
        $label = $propDesc['label'] ?? $propname;
        $type  = $propDesc['type'] ?? 'text';
        $class = !empty($propDesc['class']) ? ' ' . $propDesc['class'] : '';

        $prop = $instance->hasProperty($propname)
            ? $instance->getProperty($propname)
            : $propDesc['default'];

        $placeholder = !empty($propDesc['placeholder']) ? " placeholder='" . $propDesc['placeholder'] . "'" : "";
        $help        = !empty($propDesc['help']) ? "<span class='help-block'>" . $propDesc['help'] . "</span>" : '';

        $displ = 12;
        //$res .= !empty($propDesc['dspl_row_open']) ? "<div class='col-xs-6'><div class='row'>" : "";
        if (!empty($propDesc['dspl_width'])) {
            $displ = round(12 * ($propDesc['dspl_width'] * 0.01));
        }
        $res .= "<div class='col-xs-$displ'>";
        $res .= "<div class='form-group'><label for='config-$propname'>$label</label>";

        if (!empty($propDesc['collapse'])) {
            $res .= '<a title="more" class="pull-right" role="button" data-toggle="collapse"
                       href="#collapseContainer' . $containerId . '"">
                        <i class="fa fa-gears"></i>
                    </a>';
        }

        switch ($type) {
            case 'number':
                $res .= "<input type='number' class='form-control$class' name='$propname' value='$prop'"
                    . " id='config-$propname'$placeholder>";
                break;
            case 'email':
                $res .= "<input type='email' class='form-control$class' name='$propname' value='$prop'"
                    . " id='config-$propname'$placeholder>";
                break;
            case 'date':
                $res .= "<input type='date' class='form-control$class' name='$propname' value='$prop'"
                    . " id='config-$propname'>";
                break;
            case 'password':
                $res .= "<input type='password' class='form-control$class' name='$propname' value='$prop'"
                    . " id='config-$propname'>";
                break;
            case 'checkbox':
                $res .= "<div class='checkbox$class'><label><input type='checkbox' name='" . $propname . "' value='1'";
                $res .= $prop === "1" ? " checked" : "";
                $res .= ">$propname</label></div>";
                break;
            case 'radio':
                foreach ($propDesc['options'] as $option) {
                    $selected = $prop === $option ? " checked" : "";
                    $res     .= "<div class='radio$class'><label><input type='radio' name='$propname' value='$option' $selected>$option</label></div>";
                }
                break;
            case 'select':
                $res .= "<select class='form-control$class' name='$propname'>";
                foreach ($propDesc['options'] as $name => $option) {
                    if (stripos($name,'optgroup') !== false) {
                        $res .= "<optgroup label='" . $option['label'] . "'>";
                        foreach ($option['options'] as $gr_option) {
                            $selected = ($prop === $gr_option) ? " selected" : "";
                            $res     .= "<option value='$gr_option' $selected>$gr_option</option>";
                        }
                        $res .= "</optgroup>";
                    } else {
                        $selected = ($prop === $option) ? " selected" : "";
                        $res     .= "<option value='$option' $selected>$option</option>";
                    }
                }

                $res .= '</select>';
                break;
            case 'image':
                $previewImgUrl = empty($prop) ? \Shop::getURL() . '/gfx/keinBild.gif' : $prop;

                $res .= "<input type='hidden' name='$propname' value='$prop'>"
                    . "<button type='button' class='btn btn-default image-btn' "
                    . "onclick='opc.selectImageProp(\"$propname\")'>"
                    . "<img src='$previewImgUrl' alt='Chosen image' id='preview-img-$propname'>"
                    . "</button>";
                break;
            case 'richtext':
                $res .= "<textarea name='text' id='textarea-$propname' class='form-control$class'>"
                    . htmlspecialchars($prop)
                    . "</textarea>"
                    . "<script>CKEDITOR.replace('textarea-$propname', {baseFloatZIndex: 9000});"
                    . "opc.setConfigSaveCallback(function() {"
                    . "$('#textarea-$propname').val(CKEDITOR.instances['textarea-$propname'].getData());"
                    . "})</script>";
                break;
            case 'color':
                $res .= "  <div id='$propname' class='input-group colorpicker-component$class'>
                                <input class='form-control' name='$propname' value='$prop'>
                                <span class='input-group-addon'><i></i></span></div>"
                    . "<script>$('#$propname').colorpicker({format: 'rgba',colorSelectors: {
                    '#ffffff': '#ffffff',
                    '#777777': '#777777',
                    '#337ab7': '#337ab7',
                    '#5cb85c': '#5cb85c',
                    '#5bc0de': '#5bc0de',
                    '#f0ad4e': '#f0ad4e',
                    '#d9534f': '#d9534f',
                    '#000000': '#000000'
                }});</script>";
                break;
            case 'text':
            default:
                $res .= "<input type='text' class='form-control$class' name='$propname' value='$prop'"
                    . " id='config-$propname'$placeholder>";
                break;
        }

        $res .= $help;
        $res .= !empty($containerId) ? "</div>" : "</div></div>";
        //$res .= !empty($propDesc['dspl_row_close']) ? "</div></div>" : "";

        return $res;
    }

    /**
     * @return array
     */
    final public function getDefaultProps()
    {
        $defProps = [];

        foreach ($this->getPropertyDesc() as $name => $propDesc) {
            $defProps[$name] = $propDesc['default'];
        }

        return $defProps;
    }

    /**
     * @param PortletInstance $instance
     * @return string
     */
    abstract public function getPreviewHtml($instance);

    /**
     * @param PortletInstance $instance
     * @return string
     */
    abstract public function getFinalHtml($instance);

    /**
     * @param PortletInstance $instance
     * @return string
     */
    public function getConfigPanelHtml($instance)
    {
        return $this->getAutoConfigPanelHtml($instance);
    }

    /**
     * @return string
     */
    public function getButtonHtml()
    {
        return $this->getTitle();
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
                'default'=> '',
                'class'=> 'css-input-grid',
                'dspl_width' => 25
            ],
            'margin-right' => [
                'label' => 'margin-right',
                'type' => 'number',
                'default'=> '',
                'class'=> 'css-input-grid',
                'dspl_width' => 25
            ],
            'margin-bottom' => [
                'label' => 'margin-bottom',
                'type' => 'number',
                'default'=> '',
                'class'=> 'css-input-grid',
                'dspl_width' => 25
            ],
            'margin-left' => [
                'label' => 'margin-left',
                'type' => 'number',
                'default'=> '',
                'class'=> 'css-input-grid',
                'dspl_width' => 25
            ],
            'padding-top' => [
                'label' => 'padding-top',
                'type' => 'number',
                'default'=> '',
                'class'=> 'css-input-grid',
                'dspl_width' => 25
            ],
            'padding-right' => [
                'label' => 'padding-right',
                'type' => 'number',
                'default'=> '',
                'class'=> 'css-input-grid',
                'dspl_width' => 25
            ],
            'padding-bottom' => [
                'label' => 'padding-bottom',
                'type' => 'number',
                'default'=> '',
                'class'=> 'css-input-grid',
                'dspl_width' => 25
            ],
            'padding-left' => [
                'label' => 'padding-left',
                'type' => 'number',
                'default'=> '',
                'class'=> 'css-input-grid',
                'dspl_width' => 25
            ],
            'border-width' => [
                'label' => 'border-width',
                'type' => 'number',
                'default'=> '',
                'class'=> 'css-input-grid',
                'dspl_width' => 50
            ],
            'border-style' => [
                'label' => 'border-style',
                'type' => 'select',
                'options' => [
                    'none',
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
                'label' => 'border-color',
                'type'=> 'color',
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
                            'none',
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