<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Plugin;

use Plugin\ExtensionData\AdminMenu;
use Plugin\ExtensionData\Cache;
use Plugin\ExtensionData\Config;
use Plugin\ExtensionData\Hook;
use Plugin\ExtensionData\License;
use Plugin\ExtensionData\Links;
use Plugin\ExtensionData\Localization;
use Plugin\ExtensionData\MailTemplates;
use Plugin\ExtensionData\Meta;
use Plugin\ExtensionData\Paths;
use Plugin\ExtensionData\PaymentMethods;
use Plugin\ExtensionData\Portlets;
use Plugin\ExtensionData\Widget;

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
     * @var Hook[]
     */
    protected $hooks;

    /**
     * @var AdminMenu
     */
    protected $adminMenu;

    /**
     * @var Localization
     */
    protected $localization;

    /**
     * @var Widget
     */
    protected $widgets;

    /**
     * @var MailTemplates
     */
    protected $mailTemplates;

    /**
     * @var PaymentMethods
     */
    protected $paymentMethods;

    /**
     * @var Portlets
     */
    protected $portlets;

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
     * @return Hook[]
     */
    public function getHooks(): array
    {
        return $this->hooks;
    }

    /**
     * @param Hook[] $hooks
     */
    public function setHooks(array $hooks): void
    {
        $this->hooks = $hooks;
    }

    /**
     * @return AdminMenu
     */
    public function getAdminMenu(): AdminMenu
    {
        return $this->adminMenu;
    }

    /**
     * @param AdminMenu $adminMenu
     */
    public function setAdminMenu(AdminMenu $adminMenu): void
    {
        $this->adminMenu = $adminMenu;
    }

    /**
     * @return Localization
     */
    public function getLocalization(): Localization
    {
        return $this->localization;
    }

    /**
     * @param Localization $localization
     */
    public function setLocalization(Localization $localization): void
    {
        $this->localization = $localization;
    }

    /**
     * @return Widget
     */
    public function getWidgets(): Widget
    {
        return $this->widgets;
    }

    /**
     * @param Widget $widgets
     */
    public function setWidgets(Widget $widgets): void
    {
        $this->widgets = $widgets;
    }

    /**
     * @return MailTemplates
     */
    public function getMailTemplates(): MailTemplates
    {
        return $this->mailTemplates;
    }

    /**
     * @param MailTemplates $mailTemplates
     */
    public function setMailTemplates(MailTemplates $mailTemplates): void
    {
        $this->mailTemplates = $mailTemplates;
    }

    /**
     * @return PaymentMethods
     */
    public function getPaymentMethods(): PaymentMethods
    {
        return $this->paymentMethods;
    }

    /**
     * @param PaymentMethods $paymentMethods
     */
    public function setPaymentMethods(PaymentMethods $paymentMethods): void
    {
        $this->paymentMethods = $paymentMethods;
    }

    /**
     * @return Portlets
     */
    public function getPortlets(): Portlets
    {
        return $this->portlets;
    }

    /**
     * @param Portlets $portlets
     */
    public function setPortlets(Portlets $portlets): void
    {
        $this->portlets = $portlets;
    }
}
