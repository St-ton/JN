<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace OPC;

abstract class Portlet implements \JsonSerializable
{
    /**
     * @var int
     */
    protected $id = 0;

    /**
     * @var int
     */
    protected $pluginId = 0;

    /**
     * @var string
     */
    protected $title = '';

    /**
     * @var string
     */
    protected $class = '';

    /**
     * @var string
     */
    protected $group = '';

    /**
     * @var bool
     */
    protected $active = false;

    /**
     * Portlet constructor.
     */
    final public function __construct()
    {
    }

    /**
     * @param PortletInstance $instance
     * @return string
     * @throws \Exception
     */
    final protected function getPreviewHtmlFromTpl($instance)
    {
        return (new \JTLSmarty(true))
            ->assign('portlet', $this)
            ->assign('instance', $instance)
            ->fetch('portlets/' . $this->getClass() . '/preview.tpl');
    }

    /**
     * @param PortletInstance $instance
     * @return string
     * @throws \Exception
     */
    final protected function getFinalHtmlFromTpl($instance)
    {
        return \Shop::Smarty()
            ->assign('portlet', $this)
            ->assign('instance', $instance)
            ->fetch('portlets/' . $this->getClass() . '/final.tpl');
    }

    /**
     * @param PortletInstance $instance
     * @return string
     * @throws \Exception
     */
    final protected function getConfigPanelHtmlFromTpl($instance)
    {
        return (new \JTLSmarty(true))
            ->assign('portlet', $this)
            ->assign('instance', $instance)
            ->fetch('portlets/' . $this->getClass() . '/configpanel.tpl');
    }

    /**
     * @param PortletInstance $instance
     * @return string
     */
    final protected function getAutoConfigPanelHtml($instance)
    {
        $res = '';

        foreach ($this->getPropertyDesc() as $propname => $propDesc) {
            $label = $propDesc['label'] ?? $propname;
            $type  = $propDesc['type'] ?? 'text';

            $prop = $instance->hasProperty($propname)
                ? $instance->getProperty($propname)
                : $propDesc['default'];

            $res .= "<div class='form-group'><label for='config-$propname'>$label</label>";

            if ($type === 'text') {
                $res .= "<input type='text' class='form-control' name='$propname' value='$prop'
                    id='config-$propname'>";
            } elseif ($type === 'select') {
                $res .= "<select class='form-control' name='$propname'>";

                foreach ($propDesc['options'] as $option) {
                    if ($prop === $option) {
                        $res .= "<option value='$option' selected>$option</option>";
                    } else {
                        $res .= "<option value='$option'>$option</option>";
                    }
                }

                $res .= '</select>';
            }

            $res .= "</div>";
        }

        return $res;
    }

    /**
     * @return array
     */
    final public function getDefaultProps()
    {
        $defProps = [];

        foreach ($this->getPropertyDesc() as $name => $propDesc) {
            $defProps[$name] = $propDesc['default'];
        }

        return $defProps;
    }

    /**
     * @param PortletInstance $instance
     * @return string
     */
    abstract public function getPreviewHtml($instance);

    /**
     * @param PortletInstance $instance
     * @return string
     */
    abstract public function getFinalHtml($instance);

    /**
     * @param PortletInstance $instance
     * @return string
     */
    public function getConfigPanelHtml($instance)
    {
        return '';
    }

    /**
     * @return string
     */
    public function getButtonHtml()
    {
        return $this->getTitle();
    }

    /**
     * @return array
     */
    public function getPropertyDesc()
    {
        return [];
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getPluginId()
    {
        return $this->pluginId;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @return string
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * @param int $id
     * @return Portlet
     */
    public function setId(int $id) : Portlet
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @param int $pluginId
     * @return Portlet
     */
    public function setPluginId(int $pluginId) : Portlet
    {
        $this->pluginId = $pluginId;

        return $this;
    }

    /**
     * @param string $title
     * @return Portlet
     */
    public function setTitle(string $title) : Portlet
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @param string $class
     * @return Portlet
     */
    public function setClass(string $class) : Portlet
    {
        $this->class = $class;

        return $this;
    }

    /**
     * @param string $group
     * @return Portlet
     */
    public function setGroup(string $group) : Portlet
    {
        $this->group = $group;

        return $this;
    }

    /**
     * @param bool $active
     * @return Portlet
     */
    public function setActive(bool $active) : Portlet
    {
        $this->active = $active;

        return $this;
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'id'           => $this->getId(),
            'pluginId'     => $this->getPluginId(),
            'title'        => $this->getTitle(),
            'class'        => $this->getClass(),
            'group'        => $this->getGroup(),
            'active'       => $this->isActive(),
            'defaultProps' => $this->getDefaultProps(),
        ];
    }
}