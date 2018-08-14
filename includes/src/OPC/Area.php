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
    public function addPortlet(PortletInstance $portlet)
    {
        $this->content[] = $portlet;
    }

    /**
     * @return PortletInstance[]
     */
    public function getContent(): array
    {
        return $this->content;
    }

    /**
     * @return string
     */
    public function getPreviewHtml(): string
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
    public function getFinalHtml(): string
    {
        $result = '';
        foreach ($this->content as $portletInstance) {
            $result .= $portletInstance->getFinalHtml();
        }

        return $result;
    }

    /**
     * @param array $data
     * @return $this
     * @throws \Exception
     */
    public function deserialize($data)
    {
        $this->id = $data['id'];

        if (isset($data['content']) && \is_array($data['content'])) {
            $this->clear();

            foreach ($data['content'] as $portletData) {
                $instance = \Shop::Container()->getOPC()->getPortletInstance($portletData);
                $this->addPortlet($instance);
            }
        }

        return $this;
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
