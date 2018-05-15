<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace OPC\Portlets;

class Heading extends \OPC\Portlet
{
    public function getPreviewHtml($inst)
    {
        return $this->getPreviewRootHtml($inst, 'h' . $inst->getProperty('level'), $inst->getProperty('text'));
    }

    public function getFinalHtml($inst)
    {
        return $this->getFinalRootHtml($inst, 'h' . $inst->getProperty('level'), $inst->getProperty('text'));
    }

    public function getButtonHtml()
    {
        return '<i class="fa fa-header"></i><br>Überschrift';
    }

    public function getPropertyDesc()
    {
        return [
            'level' => [
                'label'      => 'Level',
                'type'       => 'select',
                'options'    => ['1', '2', '3', '4', '5', '6'],
                'default'    => '1',
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

    public function getPropertyTabs()
    {
        return [
            'Styles'    => 'styles',
            'Animation' => 'animations',
        ];
    }
}