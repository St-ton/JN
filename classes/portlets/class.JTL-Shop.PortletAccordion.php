<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class PortletAccordion
 */
class PortletAccordion extends CMSPortlet
{
    /**
     * @return string
     */
    public function getButton()
    {
        return '<img class="fa" src="' . Shop::getURL() .'/'
            . PFAD_TEMPLATES
            . 'Evo/themes/base/images/cms_live_editor/Icon-Accordion.svg"></i>
            <br/> Akkordeon';
    }

    /**
     * @return string
     */
    public function getPreviewHtml()
    {
        // TODO Editor: in .tpl auslagern
        if ($this->properties['layout'] === 'button') {
            $ret = '<div ' . $this->getStyleString() . ' ' . $this->getAttribString() . '>    
                <button 
                class="btn btn-' . $this->properties['cllps-button-type'] . ' btn-' . $this->properties['cllps-button-size'] . '" 
                type="button" data-toggle="collapse" 
                data-target="#div_' . $this->properties['uid'] . '" 
                aria-expanded="true" 
                aria-controls="div_' . $this->properties['uid'] . '">
                    ' . $this->properties['cllps-button-text'] . '
                </button>
                <div class="collapse in" id="div_' . $this->properties['uid'] . '">
                    <div class="well cle-area"></div>
                </div>
            </div>';
        } else {
            $ret = '<div ' . $this->getStyleString() . ' ' . $this->getAttribString() . '>
                <div class="panel-group" 
                id="accordion_' . $this->properties['uid'] . '" 
                role="tablist" 
                aria-multiselectable="true">
                    <div class="panel panel-default">
                        <div class="panel-heading" 
                        role="tab" 
                        id="pnl_hd_' . $this->properties['uid'] . '">
                            <h4 class="panel-title">
                                <a role="button" data-toggle="collapse" 
                                data-parent="#accordion_' . $this->properties['uid'] . '" 
                                href="#div_' . $this->properties['uid'] . '" 
                                aria-expanded="true" 
                                aria-controls="div_' . $this->properties['uid'] . '">
                                    <div class="well cle-area"></div>
                                </a>
                            </h4>
                        </div>
                        <div id="#div_' . $this->properties['uid'] . '" 
                        class="panel-collapse collapse in" 
                        role="tabpanel" 
                        aria-labelledby="pnl_hd_' . $this->properties['uid'] . '">
                            <div class="panel-body">
                                <div class="well cle-area"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>';
        }

        return $ret;
    }

    /**
     * @return string
     */
    public function getFinalHtml()
    {
        $i   = 0;
        if ($this->properties['layout'] === 'button') {
            $ret='<div '. $this->getStyleString() . ' ' . $this->getAttribString() .'>    
            <button 
            class="btn btn-' . $this->properties['cllps-button-type'] . ' btn-' . $this->properties['cllps-button-size'] . '" 
            type="button" 
            data-toggle="collapse" 
            data-target="#div_' . $this->properties['uid'] . '" 
            aria-expanded="true" 
            aria-controls="div_' . $this->properties['uid'] . '">
                ' . $this->properties['cllps-button-text'] . '
            </button>
            <div class="collapse ' . $this->properties['cllps-initial-state'] . '" 
            id="div_' . $this->properties['uid'] . '">
            <div class="cle-area well">';
            $subArea = $this->subAreas[$i];
            foreach ($subArea as $subPortlet) {
                $portlet        = CMS::getInstance()->createPortlet($subPortlet['portletId'])
                    ->setProperties($subPortlet['properties'])
                    ->setSubAreas($subPortlet['subAreas']);
                $subPortletHtml = $portlet->getFinalHtml();
                $ret           .= $subPortletHtml;
            }
            $ret .= '</div></div></div>';
        } else {
            $ret='<div '. $this->getStyleString() . ' ' . $this->getAttribString() .'>
                <div class="panel-group" 
                id="accordion_' . $this->properties['uid'] . '" 
                role="tablist" aria-multiselectable="true">
                    <div class="panel panel-default">
                        <div class="panel-heading" 
                        role="tab" id="pnl_hd_' . $this->properties['uid'] . '">
                            <h4 class="panel-title">
                                <a class="cle-area" 
                                role="button" data-toggle="collapse" 
                                data-parent="#accordion_' . $this->properties['uid'] . '" 
                                href="#div_' . $this->properties['uid'] . '" 
                                aria-expanded="true" 
                                aria-controls="div_' . $this->properties['uid'] . '">';
            $subArea = $this->subAreas[$i];
            foreach ($subArea as $subPortlet) {
                $portlet        = CMS::getInstance()->createPortlet($subPortlet['portletId'])
                    ->setProperties($subPortlet['properties'])
                    ->setSubAreas($subPortlet['subAreas']);
                $subPortletHtml = $portlet->getFinalHtml();
                $ret           .= $subPortletHtml;
            }
            $i++;
            $ret.='                  </a>
                            </h4>
                        </div>
                        <div id="div_' . $this->properties['uid'] . '" 
                        class="panel-collapse collapse ' . $this->properties['cllps-initial-state'] . '" 
                        role="tabpanel" aria-labelledby="pnl_hd_' . $this->properties['uid'] . '">
                            <div class="panel-body cle-area">';
            $subArea = $this->subAreas[$i];
            foreach ($subArea as $subPortlet) {
                $portlet        = CMS::getInstance()->createPortlet($subPortlet['portletId'])
                    ->setProperties($subPortlet['properties'])
                    ->setSubAreas($subPortlet['subAreas']);
                $subPortletHtml = $portlet->getFinalHtml();
                $ret           .= $subPortletHtml;
            }
            $ret.='         </div>
                        </div>
                    </div>
                </div>
            </div>';
        }

        return $ret;
    }

    /**
     * @return array
     */
    public function getDefaultProps()
    {
        return [
            'uid' => uniqid('cllps_'),
            'layout' => 'button',
            'cllps-button-text' => 'Text',
            'cllps-button-type' => 'default',
            'cllps-button-size' => 'normal',
            'cllps-initial-state' => '',
            // animation
            'animation-style'     => '',
            // attributes
            'attr' => [
                'id'                 => '',
                'class'              => '',
                'data-wow-duration'  => '',
                'data-wow-delay'     => '',
                'data-wow-offset'    => '',
                'data-wow-iteration' => '',
            ],
            // style
            'style' => [
                'color'               => '',
                'margin-top'          => '',
                'margin-right'        => '',
                'margin-bottom'       => '',
                'margin-left'         => '',
                'background-color'    => '',
                'padding-top'         => '',
                'padding-right'       => '',
                'padding-bottom'      => '',
                'padding-left'        => '',
                'border-top-width'    => '',
                'border-right-width'  => '',
                'border-bottom-width' => '',
                'border-left-width'   => '',
                'border-style'        => '',
                'border-color'        => '',
            ],
        ];
    }

    /**
     * @return string
     */
    public function getConfigPanelHtml()
    {
        return (new JTLSmarty(true))
            ->assign('properties', $this->properties)
            ->fetch('portlets/settings.accordion.tpl');
    }
}
