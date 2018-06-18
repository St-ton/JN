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
        $text = $instance->getProperty('some-text');
        $instance->addClass('alert alert-' . $instance->getProperty('type-select'));


        return $this->getFinalHtmlFromTpl($instance);
    }

    public function getButtonHtml()
    {
        return '<i class="fa fa-exclamation-circle"></i><br>Alert';
    }

    public function getPropertyDesc()
    {
        \Shop::dbg($this->getPlugin()->cPluginPfad . 'lang');
        $path = $this->getPlugin()->cPluginPfad . 'lang';
        \Shop::Lang()->setLocalization('jtl_portlets', $path);

        return [
            'some-text'   => [
                'label'   => _('a text'),
                'type'    => 'text',
                'default' => gettext('Hello world!'),
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