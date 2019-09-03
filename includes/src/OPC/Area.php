<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace JTL\OPC;

use JTL\Helpers\GeneralObject;
use JTL\Shop;

/**
 * Class Area
 * @package JTL\OPC
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
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Clear the contents
     */
    public function clear(): void
    {
        $this->content = [];
    }

    /**
     * @param PortletInstance $portlet
     */
    public function addPortlet(PortletInstance $portlet): void
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
     * @throws \Exception
     */
    public function getPreviewHtml(): string
    {
        $result = '';
        foreach ($this->content as $portletInstance) {
            $result .= $portletInstance->getPreviewHtml();
        }

        Shop::fire('shop.OPC.Area.getPreviewHtml', [
            'area' => $this,
            'result' => &$result
        ]);

        return $result;
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getFinalHtml(): string
    {
        $result = '';
        foreach ($this->content as $portletInstance) {
            $result .= $portletInstance->getFinalHtml();
        }

        Shop::fire('shop.OPC.Area.getFinalHtml', [
            'area' => $this,
            'result' => &$result
        ]);

        return $result;
    }

    public function getCssList($preview = false)
    {
        $list = [];

        foreach ($this->content as $portletInstance) {
            $cssFile = $portletInstance->getPortlet()->getCssFile($preview);

            if (!empty($cssFile)) {
                $list[$cssFile] = true;
            }

            foreach ($portletInstance->getSubareaList()->getAreas() as $area) {
                $list = $list + $area->getCssList($preview);
            }
        }

        return $list;
    }

    /**
     * @param array $data
     * @return $this
     * @throws \Exception
     */
    public function deserialize(array $data): self
    {
        $this->id = $data['id'];
        if (GeneralObject::hasCount('content', $data)) {
            $this->clear();

            foreach ($data['content'] as $portletData) {
                $instance = Shop::Container()->getOPC()->getPortletInstance($portletData);
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
