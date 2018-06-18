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
        $instance->addClass('alert alert-' . $instance->getProperty('type-select'));

        return $this->getPreviewHtmlFromTpl($instance);
    }

    public function getFinalHtml($instance)
    {
        $instance->addClass('alert alert-' . $instance->getProperty('type-select'));

        return $this->getFinalHtmlFromTpl($instance);
    }

    public function getButtonHtml()
    {
        return '<i class="fa fa-exclamation-circle"></i><br>Alert';
    }

    public function getPropertyDesc()
    {
        return [
            'some-text'   => [
                'label'   => _('a text'),
                'type'    => 'text',
                'default' => _('Hello world!'),
            ],
            'type-select' => [
                'label'   => _('Alert Type'),
                'type'    => 'select',
                'options'    => [
                    'success' => _('Success'),
                    'info'    => _('Info'),
                    'warning' => _('Warning'),
                    'danger'  => _('Danger'),
                ],
                'default' => 'info',
            ],
        ];
    }
}