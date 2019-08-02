<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace JTL\OPC;

/**
 * Trait PortletStyles
 * @package JTL\OPC
 */
trait PortletStyles
{
    /**
     * @param bool $preview
     * @return string|null
     */
    final public function getCssFile($preview = false)
    {
        $cssPath = $this->getTemplatePath() . ($preview ? 'preview' : $this->getClass()) . '.css';
        $cssUrl  = $this->getTemplateUrl() . ($preview ? 'preview' : $this->getClass()) . '.css';

        if (\file_exists($cssPath)) {
            return $cssUrl;
        }

        return null;
    }

    /**
     * @return array
     */
    public function getStylesPropertyDesc(): array
    {
        return [
            // TODO: Support these options for both bootstrap versions
//            'hidden-xs'        => [
//                'type'       => InputType::CHECKBOX,
//                'label'      => '<i class="fa fa-mobile"></i> ' . __('Visibility') . ' XS',
//                'option'     => __('hide'),
//                'width'      => 25,
//            ],
//            'hidden-sm'        => [
//                'type'       => InputType::CHECKBOX,
//                'label'      => '<i class="fa fa-tablet"></i> ' . __('Visibility') . ' S',
//                'option'     => __('hide'),
//                'width'      => 25,
//            ],
//            'hidden-md'        => [
//                'type'       => InputType::CHECKBOX,
//                'label'      => '<i class="fa fa-laptop"></i> ' . __('Visibility') . ' M',
//                'option'     => __('hide'),
//                'width'      => 25,
//            ],
//            'hidden-lg'        => [
//                'type'       => InputType::CHECKBOX,
//                'label'      => '<i class="fa fa-desktop"></i> ' . __('Visibility') . ' L',
//                'option'     => __('hide'),
//                'width'      => 25,
//            ],
            'background-color' => [
                'label'   => __('Background color'),
                'type'    => InputType::COLOR,
                'default' => '',
                'width'   => 34,
            ],
            'color'            => [
                'type'    => InputType::COLOR,
                'label'   => __('Font color'),
                'default' => '',
                'width'   => 34,
            ],
            'font-size'        => [
                'label'   => __('Font size'),
                'default' => '',
                'width'   => 34,
            ],
            'box-styles'  => [
                'type'    => InputType::BOX_STYLES,
            ],
        ];
    }
}
