<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Plugin;

use Plugin\ExtensionData\Cache;
use Plugin\ExtensionData\Config;
use Plugin\ExtensionData\Hook;
use Plugin\ExtensionData\License;
use Plugin\ExtensionData\Links;
use Plugin\ExtensionData\Meta;
use Plugin\ExtensionData\Paths;

/**
 * Class AbstractExtension
 * @package Plugin
 */
abstract class AbstractExtension
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $pluginID;

    /**
     * @var int
     */
    protected $state = State::DISABLED;

    /**
     * @var Meta
     */
    protected $meta;

    /**
     * @var Paths
     */
    protected $paths;

    /**
     * @var int
     */
    protected $priority = 5;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var Links
     */
    protected $links;

    /**
     * @var License
     */
    protected $license;

    /**
     * @var Cache
     */
    protected $cache;

    /**
     * @var bool
     */
    protected $isExtension = false;

    /**
     * @var bool
     */
    protected $bootstrap = false;

    /**
     * @var Hook
     */
    protected $hooks;

    /**
     * @return int
     */
    public function getID(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setID(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getPluginID(): string
    {
        return $this->pluginID;
    }

    /**
     * @param string $pluginID
     */
    public function setPluginID(string $pluginID): void
    {
        $this->pluginID = $pluginID;
    }

    /**
     * @return int
     */
    public function getState(): int
    {
        return $this->state;
    }

    /**
     * @param int $state
     */
    public function setState(int $state): void
    {
        $this->state = $state;
    }

    /**
     * @return Meta
     */
    public function getMeta(): Meta
    {
        return $this->meta;
    }

    /**
     * @param Meta $meta
     */
    public function setMeta(Meta $meta): void
    {
        $this->meta = $meta;
    }

    /**
     * @return Paths
     */
    public function getPaths(): Paths
    {
        return $this->paths;
    }

    /**
     * @param Paths $paths
     */
    public function setPaths(Paths $paths): void
    {
        $this->paths = $paths;
    }

    /**
     * @return int
     */
    public function getPriority(): int
    {
        return $this->priority;
    }

    /**
     * @param int $priority
     */
    public function setPriority(int $priority): void
    {
        $this->priority = $priority;
    }

    /**
     * @return Config
     */
    public function getConfig(): Config
    {
        return $this->config;
    }

    /**
     * @param Config $config
     */
    public function setConfig(Config $config): void
    {
        $this->config = $config;
    }

    /**
     * @return Links
     */
    public function getLinks(): Links
    {
        return $this->links;
    }

    /**
     * @param Links $links
     */
    public function setLinks(Links $links): void
    {
        $this->links = $links;
    }

    /**
     * @return License
     */
    public function getLicense(): License
    {
        return $this->license;
    }

    /**
     * @param License $license
     */
    public function setLicense(License $license): void
    {
        $this->license = $license;
    }

    /**
     * @return Cache
     */
    public function getCache(): Cache
    {
        return $this->cache;
    }

    /**
     * @param Cache $cache
     */
    public function setCache(Cache $cache): void
    {
        $this->cache = $cache;
    }

    /**
     * @return bool
     */
    public function isExtension(): bool
    {
        return $this->isExtension;
    }

    /**
     * @param bool $isExtension
     */
    public function setIsExtension(bool $isExtension): void
    {
        $this->isExtension = $isExtension;
    }

    /**
     * @return bool
     */
    public function isBootstrap(): bool
    {
        return $this->bootstrap;
    }

    /**
     * @param bool $bootstrap
     */
    public function setBootstrap(bool $bootstrap): void
    {
        $this->bootstrap = $bootstrap;
    }

    /**
     * @return Hook
     */
    public function getHooks(): Hook
    {
        return $this->hooks;
    }

    /**
     * @param Hook $hooks
     */
    public function setHooks(Hook $hooks): void
    {
        $this->hooks = $hooks;
    }
}
