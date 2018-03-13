<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace OPC;

class PortletModel implements \JsonSerializable
{
    /**
     * @var int
     */
    protected $kPortlet = 0;

    /**
     * @var int
     */
    protected $kPlugin = 0;

    /**
     * @var string
     */
    protected $cTitle = '';

    /**
     * @var string
     */
    protected $cClass = '';

    /**
     * @var string
     */
    protected $cGroup = '';

    /**
     * @var bool
     */
    protected $bActive = false;

    /**
     * PortletModel constructor.
     * @param $id
     * @throws \Exception
     */
    public function __construct($id)
    {
        $this->setId($id)->load();
    }

    /**
     * @return $this
     * @throws \Exception
     */
    protected function load()
    {
        if ($this->kPortlet <= 0) {
            throw new \Exception("The OPC portlet id '{$this->kPortlet}' is invalid.");
        }

        $portletDB = \Shop::DB()->select('topcportlet', 'kPortlet', $this->kPortlet);

        if (!is_object($portletDB)) {
            throw new \Exception("The OPC portlet with the id '{$this->kPortlet}' could not be found.");
        }

        $this
            ->setId($portletDB->kPortlet)
            ->setPluginId($portletDB->kPlugin)
            ->setTitle($portletDB->cTitle)
            ->setClass($portletDB->cClass)
            ->setGroup($portletDB->cGroup)
            ->setActive($portletDB->bActive);

        return $this;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->kPortlet;
    }

    /**
     * @param int $id
     * @return PortletModel
     */
    public function setId($id)
    {
        $this->kPortlet = (int)$id;

        return $this;
    }

    /**
     * @return int
     */
    public function getPluginId()
    {
        return $this->kPlugin;
    }

    /**
     * @param int $id
     * @return PortletModel
     */
    public function setPluginId($id)
    {
        $this->kPlugin = (int)$id;

        return $this;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->cTitle;
    }

    /**
     * @param string $cTitle
     * @return PortletModel
     */
    public function setTitle($cTitle)
    {
        $this->cTitle = (string)$cTitle;

        return $this;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->cClass;
    }

    /**
     * @param string $cClass
     * @return PortletModel
     */
    public function setClass($cClass)
    {
        $this->cClass = (string)$cClass;

        return $this;
    }

    /**
     * @return string
     */
    public function getGroup()
    {
        return $this->cGroup;
    }

    /**
     * @param string $cGroup
     * @return PortletModel
     */
    public function setGroup($cGroup)
    {
        $this->cGroup = (string)$cGroup;

        return $this;
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->bActive;
    }

    /**
     * @param bool $bActive
     * @return PortletModel
     */
    public function setActive($bActive)
    {
        $this->bActive = (bool)$bActive;

        return $this;
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'id'       => $this->kPortlet,
            'pluginId' => $this->kPlugin,
            'title'    => $this->cTitle,
            'class'    => $this->cClass,
            'group'    => $this->cGroup,
            'active'   => $this->bActive,
        ];
    }
}