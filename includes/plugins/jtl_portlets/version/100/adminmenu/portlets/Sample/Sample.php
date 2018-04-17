<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace OPC\Portlets;

/**
 * Class Sample
 * @package OPC\Portlets
 */
class Sample extends \OPC\Portlet
{
    public function getPreviewHtml($instance)
    {
        $text = $instance->getProperty('some-text');

        return "<div {$instance->getDataAttributeString()}>$text</div>";
    }

    public function getFinalHtml($instance)
    {
        return $this->getPreviewHtml($instance);
    }

    public function getButtonHtml()
    {
        return '<i class="fa fa-glass"></i><br>Beispiel';
    }

    public function getPropertyDesc()
    {
        return [
            'some-text' => [
                'label'   => 'Ein Text',
                'type'    => 'text',
                'default' => 'Hallo Welt',
            ],
            'some-select' => [
                'label'   => 'Ein Selectfeld',
                'type'    => 'select',
                'options' => ['red', 'green', 'blue'],
                'default' => 'green',
            ],
        ];
    }
}