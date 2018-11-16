<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Plugin;

use Plugin\ExtensionData\Config;
use Plugin\ExtensionData\Links;
use Plugin\ExtensionData\Meta;
use Plugin\ExtensionData\Paths;

/**
 * Class Extension
 * @package Plugin
 */
class Extension
{
    use \MagicCompatibilityTrait;

    public static $mapping = [
        'kPlugin' => 'ID',
        'nStatus' => '',
        'nVersion' => '',
        'nPrio' => '',
        'cName' => '',
        'cBeschreibung' => '',
        'cAutor' => '',
        'cURL' => '',
        'cVerzeichnis' => '',
        'cPluginID' => '',
        'cFehler' => '',
        'cLizenz' => '',
        'cLizenzKlasse' => '',
        'cLizenzKlasseName' => '',
        'PluginPfad' => '',
        'cFrontendPfad' => '',
        'cFrontendPfadURL' => '',
        'cFrontendPfadURLSSL' => '',
        'cAdminmenuPfad' => '',
        'cAdminmenuPfadURL' => '',
        'cLicencePfad' => '',
        'cLicencePfadURL' => '',
        'cLicencePfadURLSSL' => '',
        'dZuletztAktualisiert' => '',
        'dInstalliert' => '',
        'dErstellt' => '',
        'oPluginHook_arr' => '',
        '$oPluginAdminMenu_arr' => '',
        '$oPluginEinstellung_arr' => '',
        '$oPluginEinstellungConf_arr' => '',
        '$oPluginEinstellungAssoc_arr' => '',
        '$oPluginSprachvariable_arr' => '',
        '$oPluginSprachvariableAssoc_arr' => '',
        '$oPluginFrontendLink_arr' => '',
        '$oPluginZahlungsmethode_arr' => '',
        '$oPluginZahlungsmethodeAssoc_arr' => '',
        '$oPluginZahlungsKlasseAssoc_arr' => '',
        '$oPluginEmailvorlage_arr' => '',
        '$oPluginEmailvorlageAssoc_arr' => '',
        '$oPluginAdminWidget_arr' => '',
        '$oPluginAdminWidgetAssoc_arr' => '',
        '$oPluginUninstall' => '',
        '$dInstalliert_DE' => '',
        '$dZuletztAktualisiert_DE' => '',
        '$dErstellt_DE' => '',
        '$cPluginUninstallPfad' => '',
        '$cAdminmenuPfadURLSSL' => '',
        '$pluginCacheID' => '',
        '$pluginCacheGroup' => '',
        '$cIcon' => '',
        '$bBootstrap' => '',
        '$nCalledHook' => '',
        '$cTextReadmePath' => '',
        '$cTextLicensePath' => '',
        '$changelogPath' => '',
    ];

    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $pluginID;

    /**
     * @var int
     */
    private $state = State::DISABLED;

    /**
     * @var Meta
     */
    private $meta;

    /**
     * @var Paths
     */
    private $paths;

    /**
     * @var int
     */
    private $priority = 5;

    /**
     * @var string
     */
    private $version;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var Links
     */
    private $links;

    public function __construct()
    {
    }

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
     * @return string
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * @param string $version
     */
    public function setVersion(string $version): void
    {
        $this->version = $version;
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
}
