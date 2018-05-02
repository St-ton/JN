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
     * @return string
     */
    final public function getStyleString()
    {
        $styleString = '';

        if (!empty($this->properties['style']) && is_array($this->properties['style'])) {
            foreach ($this->properties['style'] as $name => $value) {
                Shop::dbg($name, true);
                if (trim($value) !== '') {
                    if (stripos($name, 'margin-') !== false ||
                        stripos($name, 'padding-') !== false ||
                        stripos($name, '-width') !== false ||
                        stripos($name, '-height') !== false
                    ) {
                        $styleString .= $name . ':' . htmlspecialchars($value, ENT_QUOTES) . 'px;';
                    } else {
                        $styleString .= $name . ':' . htmlspecialchars($value, ENT_QUOTES) . ';';
                    }
                }
            }
        }

        return $styleString !== '' ? 'style="' . $styleString . '"' : '';
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
        $class = !empty($propDesc['class']) ? ' '.$propDesc['class'] : '';

        $prop = $instance->hasProperty($propname)
            ? $instance->getProperty($propname)
            : $propDesc['default'];


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
                    . " id='config-$propname'>";
                break;
            case 'email':
                $res .= "<input type='email' class='form-control$class' name='$propname' value='$prop'"
                    . " id='config-$propname'>";
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

                foreach ($propDesc['options'] as $option) {
                    $selected = $prop === $option ? " selected" : "";
                    $res     .= "<option value='$option' $selected>$option</option>";
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
                    . "<script>$('#$propname').colorpicker({format: 'rgba'});</script>";
                break;
            case 'text':
            default:
                $res .= "<input type='text' class='form-control$class' name='$propname' value='$prop'"
                    . " id='config-$propname'>";
                break;
        }

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
            'border-top' => [
                'label' => 'border-top',
                'type' => 'number',
                'default'=> '',
                'class'=> 'css-input-grid',
                'dspl_width' => 25
            ],
            'border-right' => [
                'label' => 'border-right',
                'type' => 'number',
                'default'=> '',
                'class'=> 'css-input-grid',
                'dspl_width' => 25
            ],
            'border-bottom' => [
                'label' => 'border-bottom',
                'type' => 'number',
                'default'=> '',
                'class'=> 'css-input-grid',
                'dspl_row_close'=> true,
                'dspl_width' => 25
            ],
            'border-left' => [
                'label' => 'border-left',
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
                'dspl_row_close'=> true,
                'dspl_width' => 25
            ],
            'padding-left' => [
                'label' => 'padding-left',
                'type' => 'number',
                'default'=> '',
                'class'=> 'css-input-grid',
                'dspl_width' => 25
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
                'dspl_width' => 50
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