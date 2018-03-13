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
     * @var Area[] mapping area ids to subareas
     */
    protected $subareas = [];

    /**
     * @var bool
     */
    protected $previewHtmlEnabled = false;

    /**
     * @var bool
     */
    protected $finalHtmlEnabled = false;

    /**
     * @var bool
     */
    protected $configPanelHtmlEnabled = false;

    /**
     * PortletInstance constructor.
     * @param array $data
     * @throws \Exception
     */
    public function __construct($data)
    {
        $portlet = Portlet::fromId($data['id']);

        $this->portlet    = $portlet;
        $this->properties = $portlet->getDefaultProps();

        $this
            ->setPreviewHtmlEnabled(isset($data['previewHtmlEnabled']) ? $data['previewHtmlEnabled'] : false)
            ->setFinalHtmlEnabled(isset($data['finalHtmlEnabled']) ? $data['finalHtmlEnabled'] : false)
            ->setConfigPanelHtmlEnabled(isset($data['configPanelHtmlEnabled']) ? $data['configPanelHtmlEnabled'] : false);

        if (isset($data['properties']) && is_array($data['properties'])) {
            foreach ($data['properties'] as $name => $value) {
                $this->setProperty($name, $value);
            }
        }

        if (isset($data['subareas']) && is_array($data['subareas'])) {
            foreach ($data['subareas'] as $areaData) {
                $this->putSubarea(new Area($areaData));
            }
        }
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
        return $this->hasSubarea($id) ? $this->getSubarea($id)->getPreviewHtml() : '';
    }

    /**
     * @param string $id
     * @return string
     */
    public function getSubareaFinalHtml($id)
    {
        return $this->hasSubarea($id) ? $this->getSubarea($id)->getFinalHtml() : '';
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
     * @param string $id
     * @return Area
     */
    public function getSubarea($id)
    {
        return $this->subareas[$id];
    }

    /**
     * @param string $id
     * @return bool
     */
    public function hasSubarea($id)
    {
        return array_key_exists($id, $this->subareas);
    }

    /**
     * @param Area $area
     * @return PortletInstance
     */
    public function putSubarea($area)
    {
        $this->subareas[$area->getId()] = $area;

        return $this;
    }

    /**
     * @param bool $previewHtmlEnabled
     * @return PortletInstance
     */
    public function setPreviewHtmlEnabled($previewHtmlEnabled)
    {
        $this->previewHtmlEnabled = $previewHtmlEnabled;

        return $this;
    }

    /**
     * @param bool $finalHtmlEnabled
     * @return PortletInstance
     */
    public function setFinalHtmlEnabled($finalHtmlEnabled)
    {
        $this->finalHtmlEnabled = $finalHtmlEnabled;

        return $this;
    }

    /**
     * @param bool $enabled
     * @return PortletInstance
     */
    public function setConfigPanelHtmlEnabled($enabled)
    {
        $this->configPanelHtmlEnabled = $enabled;

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

        return isset($this->properties['attributes'][$name])
            ? $this->properties['attributes'][$name]
            : '';
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
     * @return array
     */
    public function jsonSerializeShort()
    {
        $result = [
            'id'    => $this->portlet->getId(),
            'title' => $this->portlet->getTitle(),
        ];

        if (count($this->properties) > 0) {
            $result['properties'] = $this->properties;
        }

        return $result;
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        $result = $this->jsonSerializeShort();

        if (count($this->subareas) > 0) {
            $result['subareas'] = [];

            foreach ($this->subareas as $id => $subarea) {
                $result['subareas'][$id] = $subarea->jsonSerialize();
            }
        }

        if ($this->previewHtmlEnabled) {
            $result['previewHtml'] = $this->getPreviewHtml();
        }

        if ($this->finalHtmlEnabled) {
            $result['finalHtml'] = $this->getFinalHtml();
        }

        if ($this->configPanelHtmlEnabled) {
            $result['configPanelHtml'] = $this->getConfigPanelHtml();
        }

        return $result;
    }
}