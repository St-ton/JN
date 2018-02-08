<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class PortletImage
 */
class PortletImage extends CMSPortlet
{
    /**
     * @return string
     */
    public function getButton()
    {
        return '<i class="fa fa-image"></i><br/> Bild';
    }

    public function getPreviewHtml($renderLinks = false)
    {
        // general
        $shape = StringHandler::filterXSS($this->properties['shape']);
        // URL
        $linkFlag       = $this->properties['link-flag'];
        $linkUrl        = $this->properties['link-url'];
        $linkTitle      = $this->properties['link-title'];
        $linkNewTabFlag = $this->properties['link-new-tab-flag'];

        $this
            ->addClass('img-responsive')
            ->addClass($shape);

        // todo Editor: in preview nur kleinste bilder laden?
        $content =
            '<img ' . $this->getAttribString() . ' ' . $this->getStyleString() . ' ' .
            $this->getSrcString($this->properties['src'], $this->properties['widthHeuristics']) . '>';

        if ($renderLinks && $linkFlag === 'yes' && !empty($linkUrl)) {
            if ($linkNewTabFlag) {
                $content = '<a href="' . $linkUrl . '" title="' . $linkTitle . '" target="_blank">' . $content . '</a>';
            } else {
                $content = '<a href="' . $linkUrl . '" title="' . $linkTitle . '">' . $content . '</a>';
            }
        }

        return $content;
    }

    public function getFinalHtml()
    {
        return $this->getPreviewHtml(true);
    }

    public function getConfigPanelHtml()
    {
        return (new JTLSmarty(true))
            ->assign('properties', $this->properties)
            ->fetch('portlets/settings.image.tpl');
    }

    public function getDefaultProps()
    {
        return [
            'shape' => '',
            'image-responsive' => '1',
            // URL
            'link-flag'           => 'no',
            'link-url'            => '',
            'link-title'          => '',
            'link-new-tab-flag'   => 'no',
            // animation
            'animation-style'     => '',
            // attributes
            'attr' => [
                'class'              => '',
                'alt'                => '',
                'title'              => '',
                'data-wow-duration'  => '',
                'data-wow-delay'     => '',
                'data-wow-offset'    => '',
                'data-wow-iteration' => '',
            ],
            'src'                => '',
            'widthHeuristics' => ['lg' => 1, 'md' => 1, 'sm' => 1, 'xs' => 1],
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
}