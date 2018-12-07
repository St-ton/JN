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
    abstract public function getPreviewHtml(PortletInstance $instance): string;

    /**
     * @param PortletInstance $instance
     * @return string
     */
    abstract public function getFinalHtml(PortletInstance $instance): string;

    /**
     * @param PortletInstance $instance
     * @return string
     * @throws \Exception
     */
    public function getConfigPanelHtml(PortletInstance $instance): string
    {
        return $this->getAutoConfigPanelHtml($instance);
    }

    /**
     * @return string
     */
    public function getButtonHtml(): string
    {
        return '<img alt="" class="fa" src="' . $this->getDefaultIconSvgUrl() . '"><br>' . $this->getTitle();
    }

    /**
     * @param string $faType
     * @return string
     */
    public function getFontAwesomeButtonHtml(string $faType): string
    {
        return '<i class="fa fa-' . $faType . '"></i><br>' . $this->getTitle();
    }

    /**
     * @return string
     */
    final protected function getTemplatePath(): string
    {
        $plugin = $this->getPlugin();
        if ($plugin !== null) {
            return $plugin->getPaths()->getPortletsPath() . $this->getClass() . '/';
        }

        return PFAD_ROOT . \PFAD_TEMPLATES . 'Evo/portlets/' . $this->getClass() . '/';
    }

    /**
     * @param PortletInstance $instance
     * @return string
     * @throws \Exception
     */
    final protected function getPreviewHtmlFromTpl(PortletInstance $instance): string
    {
        return \Shop::Smarty()
                    ->assign('portlet', $this)
                    ->assign('instance', $instance)
                    ->fetch($this->getTemplatePath() . 'preview.tpl');
    }

    /**
     * @param PortletInstance $instance
     * @return string
     * @throws \Exception
     */
    final protected function getFinalHtmlFromTpl($instance): string
    {

        return \Shop::Smarty()
                    ->assign('portlet', $this)
                    ->assign('instance', $instance)
                    ->fetch($this->getTemplatePath() . 'final.tpl');
    }

    /**
     * @param PortletInstance $instance
     * @return string
     * @throws \Exception
     */
    final protected function getConfigPanelHtmlFromTpl(PortletInstance $instance): string
    {
        return \Shop::Smarty()
                    ->assign('portlet', $this)
                    ->assign('instance', $instance)
                    ->fetch($this->getTemplatePath() . 'configpanel.tpl');
    }

    /**
     * @param PortletInstance $instance
     * @param string $id
     * @param array $extraAssigns
     * @return string
     * @throws \Exception
     */
    final protected function getConfigPanelSnippet(PortletInstance $instance, $id, $extraAssigns = []): string
    {
        $smarty = \Shop::Smarty();

        foreach ($extraAssigns as $name => $val) {
            $smarty->assign($name, $val);
        }

        return $smarty->assign('portlet', $this)
                      ->assign('instance', $instance)
                      ->fetch(\PFAD_ROOT . \PFAD_TEMPLATES . "Evo/portlets/OPC/config.$id.tpl");
    }

    /**
     * @param PortletInstance $instance
     * @return string
     * @throws \Exception
     */
    final protected function getAutoConfigPanelHtml(PortletInstance $instance): string
    {
        $desc = $this->getPropertyDesc();
        $tabs = $this->getPropertyTabs();

        foreach ($tabs as $tabname => $propnames) {
            if (\is_string($propnames)) {
                if ($propnames === 'styles') {
                    $tabs[$tabname] = $this->getStylesPropertyDesc();
                } elseif ($propnames === 'animations') {
                    $tabs[$tabname] = $this->getAnimationsPropertyDesc();
                }
            } else {
                foreach ($propnames as $i => $propname) {
                    $tabs[$tabname][$propname] = $desc[$propname];
                    unset($tabs[$tabname][$i], $desc[$propname]);
                }
            }
        }

        if (\count($desc) > 0) {
            $tabs = [__('General') => $desc] + $tabs;
        }

        $res  = '';
        $res .= "<ul class='nav nav-tabs'>";
        $i    = 0;

        foreach ($tabs as $tabname => $props) {
            $tabid  = \preg_replace('/[^A-Za-z0-9\-]/', '', $tabname);
            $active = $i === 0 ? " class='active'" : "";
            $res   .= "<li$active>";
            $res   .= "<a href='#$tabid' data-toggle='tab'>$tabname</a></li>";
            $i ++;
        }

        $res .= "</ul>";
        $res .= "<div class='tab-content'>";
        $i    = 0;

        foreach ($tabs as $tabname => $props) {
            $tabid  = \preg_replace('/[^A-Za-z0-9\-]/', '', $tabname);
            $active = $i === 0 ? " active" : "";
            $res   .= "<div class='tab-pane$active' id='$tabid'>";
            $res   .= "<div class='row'>";

            foreach ($props as $propname => $propDesc) {
                $containerId = !empty($propDesc['layoutCollapse']) ? $propname : null;
                $cllpsID     = \uniqid('', false);

                if (!empty($propDesc['collapseControlStart'])) {
                    $res .= "<script>
                                $(function(){
                                    $('[name=\"" . $propDesc['showOnProp'] . "\"]').click(function(e){
                                        if ($(e.target).val() == '" . $propDesc['showOnPropValue'] . "'){
                                            $('#collapseContainer$cllpsID').show();
                                        }else{
                                            $('#collapseContainer$cllpsID').hide();
                                        }
                                    });";
                    if ($props[$propDesc['showOnProp']]['type'] === 'checkbox'
                        || $props[$propDesc['showOnProp']]['type'] === 'radio'
                    ) {
                        $res .="    
                                    if ($('[name=\"" . $propDesc['showOnProp'] . "\"][value=\"" .
                                        $propDesc['showOnPropValue'] . "\"]').prop('checked') == true){
                                        $('#collapseContainer$cllpsID').show();
                                    }
                                });
                            </script>";
                    } else {
                        $res .="    
                                    if ($('[name=\"" . $propDesc['showOnProp'] . "\"]').val() == '" .
                                        $propDesc['showOnPropValue'] . "'){
                                        $('#collapseContainer$cllpsID').show();
                                    }
                                });
                            </script>";
                    }
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
     * @throws \Exception
     */
    final protected function getAutoConfigProp(
        PortletInstance $instance,
        $propname,
        $propDesc,
        $containerId = null
    ): string {
        $res   = '';
        $label = $propDesc['label'] ?? $propname;
        $type  = $propDesc['type'] ?? 'text';
        $class = !empty($propDesc['class']) ? ' ' . $propDesc['class'] : '';
        $prop  = $instance->hasProperty($propname)
            ? $instance->getProperty($propname)
            : $propDesc['default'] ?? null;

        $placeholder = !empty($propDesc['placeholder']) ? ' placeholder="' . $propDesc['placeholder'] . '"' : '';
        $help        = !empty($propDesc['help']) ? '<span class="help-block">' . $propDesc['help'] . '</span>' : '';
        $required    = $propDesc['required'] ?? false;
        $displ       = 12;
        if (!empty($propDesc['dspl_width'])) {
            $displ = \round(12 * ($propDesc['dspl_width'] * 0.01));
        }
        $res .= "<div class='col-xs-$displ'>";
        $res .= "<div class='form-group'>";
        $res .= $type !== 'hidden' ? '<label for="config-' . $propname . '">' . $label . '</label>' : '';

        if (!empty($propDesc['layoutCollapse'])) {
            $res .= '<a title="more" class="pull-right" role="button" data-toggle="collapse"
                       href="#collapseContainer' . $containerId . '">
                        <i class="fa fa-gears"></i>
                    </a>';
        }
        switch ($type) {
            case 'number':
            case 'email':
            case 'date':
            case 'time':
            case 'password':
                $res .= '<input type="' . $type . '" class="form-control' .
                    $class . '" name="' .
                    $propname . '" value="' . $prop . '" id="config-' . $propname . '"' .
                    $placeholder .
                    ($required ? ' required>' : '>');
                break;
            case 'checkbox':
                $res .= $this->getConfigPanelSnippet($instance, 'checkbox', [
                    'option'   => $propDesc['option'] ?? null,
                    'required' => $required,
                    'class'    => $class,
                    'prop'     => $prop,
                    'propname' => $propname,
                ]);
                break;
            case 'textlist':
                $res .= $this->getConfigPanelSnippet($instance, 'textlist', [
                    'propname' => $propname,
                    'prop'     => $prop
                ]);
                break;
            case 'radio':
                $res .= $this->getConfigPanelSnippet($instance, 'radio', [
                    'options'  => $propDesc['options'],
                    'inline'   => $propDesc['inline'] ?? false,
                    'required' => $required,
                    'class'    => $class,
                    'prop'     => $prop,
                    'propname' => $propname,
                ]);
                break;
            case 'select':
                $res .= $this->getConfigPanelSnippet($instance, 'select', [
                    'options'  => $propDesc['options'],
                    'inline'   => $propDesc['inline'] ?? false,
                    'required' => $required,
                    'class'    => $class,
                    'prop'     => $prop,
                    'propname' => $propname,
                ]);
                break;
            case 'image':
                $res .= $this->getConfigPanelSnippet($instance, 'image', [
                    'previewImgUrl' => empty($prop) ? \Shop::getURL() . '/' . \PFAD_GFX . 'keinBild.gif' : $prop,
                    'prop'          => $prop,
                    'propname'      => $propname,
                ]);
                break;
            case 'richtext':
                $res .= $this->getConfigPanelSnippet($instance, 'richtext', [
                    'prop'     => $prop,
                    'propname' => $propname,
                    'required' => $required,
                ]);
                break;
            case 'color':
                $res .= $this->getConfigPanelSnippet($instance, 'color', [
                    'prop'        => $prop,
                    'propname'    => $propname,
                    'required'    => $required,
                    'class'       => $class,
                    'colorFormat' => $propDesc['color-format'] ?? 'rgba',
                ]);
                break;
            case 'filter':
                $res .= $this->getConfigPanelSnippet($instance, 'filter', [
                    'propname' => $propname,
                    'prop'     => $prop
                ]);
                break;
            case 'icon':
                $res .= $this->getConfigPanelSnippet($instance, 'icon', [
                    'propname' => $propname,
                    'prop'     => $prop,
                    'uid'      => \uniqid('', false)
                ]);
                break;
            case 'hidden':
                $res .= '<input type="hidden" name="' .
                    $propname . '" value="' . $prop .
                    '" id="config-"' . $propname . '">';
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
            case 'video':
                $res .= $this->getConfigPanelSnippet($instance, 'video', [
                    'previewVidUrl' => empty($prop) ? \Shop::getURL() . '/' . \PFAD_GFX . 'keinBild.gif' : $prop,
                    'prop'          => $prop,
                    'propname'      => $propname,
                ]);
                break;
            case 'hint':
                $res .= '<div class="alert alert-' . $propDesc['class'] . '" role="alert">' .
                    $propDesc['text'] .
                    '</div>';
                break;
            case 'text':
            default:
                $res .= '<input type="text" class="form-control" name="' . $propname .
                    '" value="' . $prop . '" id="config-' . $propname . '"' .
                    ($required ? ' required>' : '>');
                break;
        }

        $res .= $help;
        $res .= $containerId !== null ? '</div>' : '</div></div>';

        return $res;
    }

    /**
     * @param PortletInstance $instance
     * @param string $tag
     * @param string $innerHtml
     * @return string
     */
    final protected function getPreviewRootHtml(
        PortletInstance $instance,
        string $tag = 'div',
        string $innerHtml = ''
    ): string {
        $attributes    = $instance->getAttributeString();
        $dataAttribute = $instance->getDataAttributeString();

        return "<$tag $attributes $dataAttribute >$innerHtml</$tag>";
    }

    /**
     * @param PortletInstance $instance
     * @param string $tag
     * @param string $innerHtml
     * @return string
     */
    final protected function getFinalRootHtml(
        PortletInstance $instance,
        string $tag = 'div',
        string $innerHtml = ''
    ): string {
        $attributes = $instance->getAttributeString();

        return "<$tag $attributes>$innerHtml</$tag>";
    }

    /**
     * @return string
     */
    final protected function getDefaultIconSvgUrl(): string
    {
        return \Shop::getURL() . '/' . \PFAD_TEMPLATES . 'Evo/portlets/' . $this->getClass() . '/icon.svg';
    }
}
