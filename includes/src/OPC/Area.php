<?php declare(strict_types=1);

namespace JTL\OPC;

use JTL\Events\Dispatcher;
use JTL\Events\Event;
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
    protected string $id = '';

    /**
     * @var PortletInstance[]
     */
    protected array $content = [];

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

        Dispatcher::getInstance()->fire(Event::OPC_AREA_GETPREVIEWHTML, [
            'area'   => $this,
            'result' => &$result
        ]);

        return $result;
    }

    /**
     * @param bool $inContainer
     * @return string
     * @throws \Exception
     */
    public function getFinalHtml(bool $inContainer = true): string
    {
        $result = '';
        foreach ($this->content as $portletInstance) {
            $result .= $portletInstance->getFinalHtml($inContainer);
        }

        Dispatcher::getInstance()->fire(Event::OPC_AREA_GETFINALHTML, [
            'area'   => $this,
            'result' => &$result
        ]);

        return $result;
    }

    /**
     * @param bool $preview
     * @return array
     */
    public function getCssList(bool $preview = false): array
    {
        $list = [];

        foreach ($this->content as $portletInstance) {
            $cssFiles = $portletInstance->getPortlet()->getCssFiles($preview);
            $list     = $list + $cssFiles;

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
    public function jsonSerialize(): array
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
