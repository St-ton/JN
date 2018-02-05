<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class PortletPanel
 */
class PortletAccordion extends CMSPortlet
{
    /**
     * @return string
     */
    public function getButton()
    {
        return '<i class="fa fa-th-list"></i> Akkordeon';
    }

    /**
     * @return string
     */
    public function getPreviewHtml()
    {
        if ($this->properties['layout'] === 'button') {
            $ret='<div>    
            <button class="btn btn-primary" type="button" data-toggle="collapse" data-target="#div_' . $this->properties['uid'] . '" aria-expanded="true" aria-controls="div_' . $this->properties['uid'] . '">
                click to minimize
            </button>
            <div class="collapse in cle-area" id="div_' . $this->properties['uid'] . '">
              
            </div></div>';
        } else {
            $ret='<div>
                <div class="panel-group" id="accordion_' . $this->properties['uid'] . '" role="tablist" aria-multiselectable="true">
                    <div class="panel panel-default">
                        <div class="panel-heading" role="tab" id="headingOne">
                            <h4 class="panel-title">
                                <a role="button" data-toggle="collapse" data-parent="#accordion" href="#div_' . $this->properties['uid'] . '" aria-expanded="true" aria-controls="div_' . $this->properties['uid'] . '">
                                    <div class="well cle-area"></div>
                                </a>
                            </h4>
                        </div>
                        <div id="#div_' . $this->properties['uid'] . '" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne">
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
            $ret='<div>    
            <button class="btn btn-primary" type="button" data-toggle="collapse" data-target="#div_' . $this->properties['uid'] . '" aria-expanded="true" aria-controls="div_' . $this->properties['uid'] . '">
                click to minimize
            </button>
            <div class="collapse in cle-area" id="div_' . $this->properties['uid'] . '">';
            $subArea = $this->subAreas[$i];
            foreach ($subArea as $subPortlet) {
                $portlet        = CMS::getInstance()->createPortlet($subPortlet['portletId'])
                    ->setProperties($subPortlet['properties'])
                    ->setSubAreas($subPortlet['subAreas']);
                $subPortletHtml = $portlet->getFinalHtml();
                $ret           .= $subPortletHtml;
            }
            $ret .= '</div></div>';
        } else {
            $ret='<div>
                <div class="panel-group" id="accordion_' . $this->properties['uid'] . '" role="tablist" aria-multiselectable="true">
                    <div class="panel panel-default">
                        <div class="panel-heading" role="tab" id="headingOne">
                            <h4 class="panel-title">
                                <a class="cle-area" role="button" data-toggle="collapse" data-parent="#accordion" href="#div_' . $this->properties['uid'] . '" aria-expanded="true" aria-controls="div_' . $this->properties['uid'] . '">';
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
                        <div id="#div_' . $this->properties['uid'] . '" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne">
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