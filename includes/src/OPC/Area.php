<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace OPC;

/**
 * Class Area
 * @package OPC
 */
class Area implements \JsonSerializable
{
    /**
     * @var string
     */
    protected $id = '';

    /**
     * @var PortletInstance[]
     */
    protected $content = [];

    /**
     * Area constructor.
     * @param array $data
     */
    public function __construct($data)
    {
        $this->id = $data['id'];

        if (isset($data['content']) && is_array($data['content'])) {
            foreach ($data['content'] as $portletData) {
                $this->addPortlet(new PortletInstance($portletData));
            }
        }
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Clear the contents
     */
    public function clear()
    {
        $this->content = [];
    }

    /**
     * @param PortletInstance $portlet
     */
    public function addPortlet($portlet)
    {
        $this->content[] = $portlet;
    }

    /**
     * @return PortletInstance[]
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @return string
     */
    public function getPreviewHtml()
    {
        $result = '';

        foreach ($this->content as $portletInstance) {
            $result .= $portletInstance->getPreviewHtml();
        }

        return $result;
    }

    /**
     * @return string
     */
    public function getFinalHtml()
    {
        $result = '';

        foreach ($this->content as $portletInstance) {
            $result .= $portletInstance->getFinalHtml();
        }

        return $result;
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        $result = [
            'id'      => $this->id,
            'content' => [],
        ];

        foreach ($this->content as $instance) {
            $result['content'][] = $instance->jsonSerialize();
        }

        return $result;
    }
}