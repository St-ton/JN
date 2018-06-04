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
class Alert extends \OPC\Portlet
{
    public function getPreviewHtml($instance)
    {
        $text = $instance->getProperty('some-text');
        $instance->addClass('alert alert-' . $instance->getProperty('type-select'));

        return "<div {$instance->getAttributeString()} {$instance->getDataAttributeString()} role='alert'>$text</div>";
    }

    public function getFinalHtml($instance)
    {
        $text = $instance->getProperty('some-text');
        $instance->addClass('alert alert-' . $instance->getProperty('type-select'));

        return "<div {$instance->getAttributeString()} role='alert'>$text</div>";
    }

    public function getButtonHtml()
    {
        return '<i class="fa fa-exclamation-circle"></i><br>Alert';
    }

    public function getPropertyDesc()
    {
        return [
            'some-text'   => [
                'label'   => 'Ein Text',
                'type'    => 'text',
                'default' => 'Hallo Welt!',
            ],
            'type-select' => [
                'label'   => 'Alert Type',
                'type'    => 'select',
                'options'    => [
                    'success' => 'Erfolg',
                    'info'    => 'Info',
                    'warning' => 'Warunug',
                    'danger'  => 'Gefahr',
                ],
                'default' => 'info',
            ],
        ];
    }
}