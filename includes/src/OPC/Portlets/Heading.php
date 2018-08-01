<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace OPC\Portlets;

use OPC\PortletInstance;

/**
 * Class Heading
 * @package OPC\Portlets
 */
class Heading extends \OPC\Portlet
{
    /**
     * @param PortletInstance $instance
     * @return string
     */
    public function getPreviewHtml(PortletInstance $instance): string
    {
        return $this->getPreviewRootHtml($instance, 'h' . $instance->getProperty('level'), $instance->getProperty('text'));
    }

    /**
     * @param PortletInstance $instance
     * @return string
     */
    public function getFinalHtml(PortletInstance $instance): string
    {
        return $this->getFinalRootHtml($instance, 'h' . $instance->getProperty('level'), $instance->getProperty('text'));
    }

    /**
     * @return string
     */
    public function getButtonHtml(): string
    {
        return '<i class="fa fa-header"></i><br>Überschrift';
    }

    /**
     * @return array
     */
    public function getPropertyDesc(): array
    {
        return [
            'level' => [
                'label'      => 'Level',
                'type'       => 'select',
                'options'    => [
                    '1' => '1',
                    '2' => '2',
                    '3' => '3',
                    '4' => '4',
                    '5' => '5',
                    '6' => '6',
                ],
                'default'    => '1',
                'required'   => true,
                'dspl_width' => 50,
            ],
            'text'  => [
                'label'      => 'Text',
                'type'       => 'text',
                'default'    => 'Heading',
                'dspl_width' => 50,
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
