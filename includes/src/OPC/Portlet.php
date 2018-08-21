<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace OPC;

/**
 * Class Portlet
 * @package OPC
 */
abstract class Portlet implements \JsonSerializable
{
    use PortletHtml;
    use PortletStyles;
    use PortletAnimations;

    /**
     * @var int
     */
    protected $id = 0;

    /**
     * @var null|\Plugin
     */
    protected $plugin;

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
     * @return array
     */
    final public function getDefaultProps(): array
    {
        $defProps = [];

        foreach ($this->getPropertyDesc() as $name => $propDesc) {
            $defProps[$name] = $propDesc['default'] ?? '';
        }

        return $defProps;
    }

    /**
     * @return array
     */
    public function getPropertyDesc(): array
    {
        return [];
    }

    /**
     * @return array
     */
    public function getPropertyTabs(): array
    {
        return [];
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getPluginId(): int
    {
        return $this->plugin === null ? 0 : $this->plugin->kPlugin;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getClass(): string
    {
        return $this->class;
    }

    /**
     * @return string
     */
    public function getGroup(): string
    {
        return $this->group;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * @param int $id
     * @return Portlet
     */
    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @param int $pluginId
     * @return Portlet
     */
    public function setPluginId(int $pluginId): self
    {
        $this->plugin = $pluginId > 0 ? new \Plugin($pluginId) : null;

        return $this;
    }

    /**
     * @param string $title
     * @return Portlet
     */
    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @param string $class
     * @return Portlet
     */
    public function setClass(string $class): self
    {
        $this->class = $class;

        return $this;
    }

    /**
     * @param string $group
     * @return Portlet
     */
    public function setGroup(string $group): self
    {
        $this->group = $group;

        return $this;
    }

    /**
     * @return \Plugin|null
     */
    public function getPlugin()
    {
        return $this->plugin;
    }

    /**
     * @param bool $active
     * @return Portlet
     */
    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
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
