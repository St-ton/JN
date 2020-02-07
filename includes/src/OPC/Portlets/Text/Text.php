<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace JTL\OPC\Portlets\Text;

use JTL\OPC\InputType;
use JTL\OPC\Portlet;
use JTL\OPC\PortletInstance;

/**
 * Class Text
 * @package JTL\OPC\Portlets
 */
class Text extends Portlet
{
    /**
     * @return string
     */
    public function getButtonHtml(): string
    {
        return $this->getFontAwesomeButtonHtml('fas fa-font');
    }

    /**
     * @return array
     */
    public function getPropertyDesc(): array
    {
        return [
            'text' => [
                'label'   => __('text'),
                'type'    => InputType::RICHTEXT,
                'default' => __('exampleRichText'),
            ],
        ];
    }

    /**
     * @return array
     */
    public function getPropertyTabs(): array
    {
        return [
            __('Styles') => 'styles',
        ];
    }
}
