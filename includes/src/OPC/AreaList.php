<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace OPC;

class AreaList implements \JsonSerializable
{
    /**
     * @var Area[]
     */
    protected $areas = [];

    /**
     * @return $this
     */
    public function clear()
    {
        $this->areas = [];

        return $this;
    }

    /**
     * @param Area $area
     * @return $this
     */
    public function putArea($area)
    {
        $this->areas[$area->getId()] = $area;

        return $this;
    }

    /**
     * @param string $id
     * @return bool
     */
    public function hasArea($id)
    {
        return array_key_exists($id, $this->areas);
    }

    /**
     * @param string $id
     * @return Area
     */
    public function getArea($id)
    {
        return $this->areas[$id];
    }

    /**
     * @return Area[]
     */
    public function getAreas()
    {
        return $this->areas;
    }

    /**
     * @return string[] the rendered HTML content of this page
     */
    public function getPreviewHtml()
    {
        $result = [];

        foreach ($this->areas as $id => $area) {
            $result[$id] = $area->getPreviewHtml();
        }

        return $result;
    }

    /**
     * @return string[] the rendered HTML content of this page
     */
    public function getFinalHtml()
    {
        $result = [];

        foreach ($this->areas as $id => $area) {
            $result[$id] = $area->getFinalHtml();
        }

        return $result;
    }

    /**
     * @param array $data
     */
    public function deserialize($data)
    {
        $this->clear();

        foreach ($data as $areaData) {
            $area = (new Area())->deserialize($areaData);
            $this->putArea($area);
        }
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        $res = [];

        foreach ($this->areas as $id => $area) {
            $res[$id] = $area->jsonSerialize();
        }

        return $res;
    }
}