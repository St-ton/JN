<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace OPC;

/**
 * Trait PortletHtml
 * @package OPC
 */
trait PortletHtml
{
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
     * @param string $id
     * @param array $extraAssigns
     * @return string
     * @throws \Exception
     */
    final protected function getConfigPanelSnippet($instance, $id, $extraAssigns = [])
    {
        return (new \JTLSmarty(true))
            ->assign('portlet', $this)
            ->assign('instance', $instance)
            ->assign($extraAssigns)
            ->fetch("portlets/OPC/config.$id.tpl");
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
                } elseif ($propnames === 'animations') {
                    $tabs[$tabname] = $this->getAnimationsPropertyDesc();
                }
            } else {
                foreach ($propnames as $i => $propname) {
                    $tabs[$tabname][$propname] = $desc[$propname];
                    unset($tabs[$tabname][$i]);
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
                $containerId = !empty($propDesc['layoutCollapse']) ? $propname : null;
                $cllpsID = uniqid();
                if (!empty($propDesc['collapseControlStart'])) {
                    $res .= "<script>
                                $(function(){
                                    $('[name=\"" . $propDesc['showOnProp'] . "\"]').click(function(e){
                                        if ($(e.target).val() == '" . $propDesc['showOnPropValue'] . "'){
                                            $('#collapseContainer$cllpsID').show();
                                        }else{
                                            $('#collapseContainer$cllpsID').hide();
                                        }
                                    });
                                    if ($('[name=\"" . $propDesc['showOnProp'] . "\"]').val() == '" . $propDesc['showOnPropValue'] . "'){
                                        $('#collapseContainer$cllpsID').show();
                                    }
                                });
                            </script>";
                    ;
                    $res .= "<div class='collapse' id='collapseContainer$cllpsID'>";
                }

                $res .= $this->getAutoConfigProp($instance, $propname, $propDesc, $containerId);

                if (!empty($propDesc['layoutCollapse'])) {
                    $res .= "<div class='collapse' id='collapseContainer$containerId'><div class='row'> ";
                    foreach ($propDesc['layoutCollapse'] as $colapsePropname => $collapsePropdesc) {
                        $res .= $this->getAutoConfigProp($instance, $colapsePropname, $collapsePropdesc);
                    }
                    $res .= "</div></div></div>"; // row, collapse, col-xs-*
                }

                if (!empty($propDesc['collapseControlEnd'])) {
                    $res .= "</div>"; // collapse
                }
            }
            $i++;
            $res .= "</div></div>"; // row, tab-pane
        }
        $res .= "</div>";

        return $res;
    }

    /**
     * @param PortletInstance $instance
     * @param string $propname
     * @param array $propDesc
     * @param string $containerId
     * @return string
     */
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
        if (!empty($propDesc['dspl_width'])) {
            $displ = round(12 * ($propDesc['dspl_width'] * 0.01));
        }
        $res .= "<div class='col-xs-$displ'>";
        $res .= "<div class='form-group'>";
        $res .= $type!=='hidden' ? "<label for='config-$propname'>$label</label>" : "";

        if (!empty($propDesc['layoutCollapse'])) {
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
                $res .= ">$label</label></div>";
                break;
            case 'radio':
                $res     .= "<div class='radio$class'>";
                foreach ($propDesc['options'] as $name => $value) {
                    $selected = $prop === $value ? " checked" : "";
                    $res     .= "<label";
                    $res .= !empty($propDesc['inline']) ? ' class="radio-inline"' : '';
                    $res .="><input type='radio' name='$propname' value='$value'"
                        . "$selected>$name</label>";
                }
                $res     .= "</div>";
                break;
            case 'select':
                $res .= "<select class='form-control$class' name='$propname'>";

                foreach ($propDesc['options'] as $name => $option) {
                    if (stripos($name, 'optgroup') !== false) {
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
                $res .= "<textarea name='text' id='textarea-$propname' class='form-control'>"
                    . htmlspecialchars($prop)
                    . "</textarea>"
                    . "<script>CKEDITOR.replace('textarea-$propname', {baseFloatZIndex: 9000});"
                    . "opc.setConfigSaveCallback(function() {"
                    . "$('#textarea-$propname').val(CKEDITOR.instances['textarea-$propname'].getData());"
                    . "})</script>";
                break;
            case 'color':
                $res .= "<div id='$propname' class='input-group colorpicker-component$class'>
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
            case 'filter':
                $res .= $this->getConfigPanelSnippet($instance, 'filter', [
                    'propname'   => $propname,
                    'prop'       => $prop
                ]);
                break;
            case 'icon':
                $res .= $this->getConfigPanelSnippet($instance, 'icon', [
                    'propname'   => $propname,
                    'prop'       => $prop,
                    'uid'        => uniqid()
                ]);
                break;
            case 'hidden':
                $res .= "<input type='hidden' name='$propname' value='$prop'"
                    . " id='config-$propname'>";
                break;
            case 'banner-zones':
                $res .= $this->getConfigPanelSnippet($instance, 'banner-zones');
                break;
            case 'image-set':
                $res .= $this->getConfigPanelSnippet($instance, 'image-set', [
                    'propname'   => $propname,
                    'prop'       => $prop,
                    'useColumns' => !empty($propDesc['useColumns']) ? $propDesc['useColumns'] : false,
                    'useLinks'   => !empty($propDesc['useLinks']) ? $propDesc['useLinks'] : false,
                    'useTitles'  => !empty($propDesc['useTitles']) ? $propDesc['useTitles'] : false
                ]);
                break;
            case 'text':
            default:
                $res .= "<input type='text' class='form-control' name='$propname' value='$prop'"
                    . " id='config-$propname'>";
                break;
        }

        $res .= $help;
        $res .= $containerId !== null ? "</div>" : "</div></div>";

        return $res;
    }
}