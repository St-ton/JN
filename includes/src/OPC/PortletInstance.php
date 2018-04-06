<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace OPC;

class PortletInstance implements \JsonSerializable
{
    /**
     * @var Portlet
     */
    protected $portlet;

    /**
     * @var array
     */
    protected $properties = [];

    /**
     * @var null|AreaList mapping area ids to subareas
     */
    protected $subareaList = null;

    /**
     * PortletInstance constructor.
     * @param Portlet $portlet
     */
    public function __construct(Portlet $portlet)
    {
        $this->portlet     = $portlet;
        $this->properties  = $portlet->getDefaultProps();
        $this->subareaList = new AreaList();
    }

    /**
     * @return Portlet
     */
    public function getPortlet()
    {
        return $this->portlet;
    }

    /**
     * @return string
     */
    public function getPreviewHtml()
    {
        return $this->portlet->getPreviewHtml($this);
    }

    /**
     * @return string
     */
    public function getFinalHtml()
    {
        return $this->portlet->getFinalHtml($this);
    }

    /**
     * @return string
     */
    public function getConfigPanelHtml()
    {
        return $this->portlet->getConfigPanelHtml($this);
    }

    /**
     * @param string $id
     * @return string
     */
    public function getSubareaPreviewHtml($id)
    {
        return $this->hasSubarea($id)
            ? $this->getSubarea($id)->getPreviewHtml()
            : '';
    }

    /**
     * @param string $id
     * @return string
     */
    public function getSubareaFinalHtml($id)
    {
        return $this->hasSubarea($id)
            ? $this->getSubarea($id)->getFinalHtml()
            : '';
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function getProperty($name)
    {
        return $this->properties[$name];
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return PortletInstance
     */
    public function setProperty($name, $value)
    {
        $this->properties[$name] = $value;

        return $this;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasProperty($name)
    {
        return array_key_exists($name, $this->properties);
    }

    /**
     * @return null|AreaList
     */
    public function getSubareaList()
    {
        return $this->subareaList;
    }

    /**
     * @param string $id
     * @return Area
     */
    public function getSubarea($id)
    {
        return $this->subareaList->getArea($id);
    }

    /**
     * @param string $id
     * @return bool
     */
    public function hasSubarea($id)
    {
        return $this->subareaList->hasArea($id);
    }

    /**
     * @param Area $area
     * @return PortletInstance
     */
    public function putSubarea($area)
    {
        $this->subareaList->putArea($area);

        return $this;
    }

    /**
     * @param $name
     * @return string
     */
    public function getAttribute($name)
    {
        if (!isset($this->properties['attributes'])) {
            $this->properties['attributes'] = [];
        }

        return $this->properties['attributes'][$name] ?? '';
    }

    /**
     * @param string $name
     * @param string $value
     * @return $this
     */
    public function setAttribute($name, $value)
    {
        if (!isset($this->properties['attributes'])) {
            $this->properties['attributes'] = [];
        }

        $this->properties['attributes'][$name] = $value;

        return $this;
    }

    /**
     * @param string $className
     * @return $this
     */
    public function addClass($className)
    {
        $classes = explode(' ', $this->getAttribute('class'));

        if (!in_array($className, $classes)) {
            $classes[] = $className;
        }

        $this->setAttribute('class', implode(' ', $classes));

        return $this;
    }

    /**
     * @return array
     */
    public function getStyles()
    {
        return $this->hasProperty('styles') ? $this->getProperty('styles') : [];
    }

    /**
     * @param string $name
     * @param string $value
     * @return $this
     */
    public function setStyle($name, $value)
    {
        $styles        = $this->getStyles();
        $styles[$name] = $value;
        $this->setProperty('styles', $styles);
        $this->updateAttributes();

        return $this;
    }

    /**
     * @return $this
     */
    public function updateAttributes()
    {
        $styles      = $this->getStyles();
        $styleString = '';

        foreach ($styles as $styleName => $styleValue) {
            $styleString .= "$styleName: $styleValue; ";
        }

        $this->setAttribute('style', $styleString);

        return $this;
    }

    /**
     * @return string
     */
    public function getAttributeString()
    {
        $result = '';

        if (isset($this->properties['attributes']) && is_array($this->properties['attributes'])) {
            foreach ($this->properties['attributes'] as $name => $value) {
                $result .= "$name=\"$value\"";
            }
        }

        return $result;
    }

    /**
     * @return string
     */
    public function getDataAttributeString()
    {
        $instanceData = $this->jsonSerializeShort();

        unset($instanceData['subareas']);

        return 'data-portlet="' . htmlspecialchars(json_encode($instanceData), ENT_QUOTES) . '"';
    }

    /**
     * @param array $data
     * @return $this
     */
    public function deserialize($data)
    {
        if (isset($data['properties']) && is_array($data['properties'])) {
            foreach ($data['properties'] as $name => $value) {
                $this->setProperty($name, $value);
            }
        }

        if (isset($data['subareas']) && is_array($data['subareas'])) {
            foreach ($data['subareas'] as $areaData) {
                $area = new Area();
                $area->deserialize($areaData);
                $this->putSubarea($area);
            }
        }

        return $this;
    }

    /**
     * @return array
     */
    public function jsonSerializeShort()
    {
        $result = [
            'id'         => $this->portlet->getId(),
            'title'      => $this->portlet->getTitle(),
            'properties' => $this->properties,
        ];

        return $result;
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        $result             = $this->jsonSerializeShort();
        $result['subareas'] = $this->subareaList->jsonSerialize();

        return $result;
    }
}