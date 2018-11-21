<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Plugin;

/**
 * Class ExtensionBC
 * @package Plugin
 */
class ExtensionBC extends AbstractExtension
{
    use \MagicCompatibilityTrait;

    /**
     * @var array
     */
    public static $mapping = [
        'kPlugin'                 => 'ID',
        'nStatus'                 => 'State',
        'nVersion'                => ['getMeta', 'Version'],
        'nPrio'                   => 'Priority',
        'cName'                   => ['getMeta', 'Name'],
        'cBeschreibung'           => ['getMeta', 'Description'],
        'cAutor'                  => ['getMeta', 'Author'],
        'cURL'                    => ['getMeta', 'URL'],
        'cVerzeichnis'            => ['getPaths', 'BaseDir'],
        'cPluginID'               => 'PluginID',
        //        'cFehler'                 => '',
        'cLizenz'                 => ['getLicense', 'Key'],
        'cLizenzKlasse'           => ['getLicense', 'Class'],
        'cLizenzKlasseName'       => ['getLicense', 'ClassName'],
        'cPluginPfad'             => ['getPaths', 'VersionedPath'],
        'cFrontendPfad'           => ['getPaths', 'FrontendPath'],
        'cFrontendPfadURL'        => ['getPaths', 'FrontendURL'],
        'cFrontendPfadURLSSL'     => ['getPaths', 'FrontendURL'],
        'cAdminmenuPfad'          => ['getPaths', 'AdminPath'],
        'cAdminmenuPfadURL'       => ['getPaths', 'AdminURL'],
        'cLicencePfad'            => ['getPaths', 'LicencePath'],
        //        'cLicencePfadURL'                 => '',
        //        'cLicencePfadURLSSL'              => '',
        //        'oPluginHook_arr'                 => '',
        'oPluginAdminMenu_arr'    => ['getAdminMenu', 'Items'],
        //        'oPluginFrontendLink_arr'         => '',
        //        'oPluginZahlungsmethode_arr'      => '',
        //        'oPluginZahlungsmethodeAssoc_arr' => '',
        //        'oPluginZahlungsKlasseAssoc_arr'  => '',
        //        'oPluginEmailvorlage_arr'         => '',
        //        'oPluginEmailvorlageAssoc_arr'    => '',
        //        'oPluginAdminWidget_arr'          => '',
        //        'oPluginAdminWidgetAssoc_arr'     => '',
        //        'oPluginUninstall'                => '',
        'dZuletztAktualisiert'    => 'CompatDateUpdated',
        'dInstalliert'            => 'CompatDateInstalled',
        'dErstellt'               => 'CompatDateInstalled',
        'dZuletztAktualisiert_DE' => 'CompatDateUpdatedGER',
        'dInstalliert_DE'         => 'CompatDateInstalledGER',
        'dErstellt_DE'            => 'CompatDateInstalledGER',
        'cPluginUninstallPfad'    => ['getPaths', 'Uninstaller'],
        //        'cAdminmenuPfadURLSSL'            => '',
        //        'pluginCacheID'                   => '',
        //        'pluginCacheGroup'                => '',
        'cIcon'                   => ['getMeta', 'Icon'],
        'bBootstrap'              => 'CompatBootstrap',
        'nCalledHook'             => 'CalledHook',
        'cTextReadmePath'         => ['getMeta', 'ReadmeMD'],
        'cTextLicensePath'        => ['getMeta', 'LicenseMD'],
        'changelogPath'           => ['getMeta', 'ChangelogMD'],
    ];

    /**
     * @var int
     */
    private $calledHookID = -1;

    /**
     * @var bool
     */
    private $boostrap = false;

    /**
     * @return string
     */
    public function getCompatDateUpdated(): string
    {
        return $this->getMeta()->getDateLastUpdate()->format('Y-m-d H:i:s');
    }

    /**
     * @param string $date
     */
    public function setCompatDateUpdated(string $date): void
    {
        $this->getMeta()->setDateLastUpdate(new \DateTime($date));
    }

    /**
     * @return string
     */
    public function getCompatDateUpdatedGER(): string
    {
        return $this->getMeta()->getDateLastUpdate()->format('d.m.Y H:i');
    }

    /**
     * @param string $date
     */
    public function setCompatDateUpdatedGER(string $date): void
    {
        $this->getMeta()->setDateLastUpdate(new \DateTime($date));
    }

    /**
     * @return string
     */
    public function getCompatDateInstalled(): string
    {
        return $this->getMeta()->getDateInstalled()->format('Y-m-d H:i');
    }

    /**
     * @param string $date
     */
    public function setCompatDateInstalled(string $date): void
    {
        $this->getMeta()->setDateInstalled(new \DateTime($date));
    }

    /**
     * @return string
     */
    public function getCompatDateInstalledGER(): string
    {
        return $this->getMeta()->getDateInstalled()->format('d.m.Y H:i');
    }

    /**
     * @param string $date
     */
    public function setCompatDateInstalledGER(string $date): void
    {
        $this->getMeta()->setDateInstalled(new \DateTime($date));
    }

    /**
     * @return bool
     */
    public function getCompatBootstrap(): bool
    {
        return $this->boostrap;
    }

    /**
     * @param mixed $d
     */
    public function setCompatBootstrap($d): void
    {
        $this->boostrap = (bool)$d;
    }

    /**
     * @return int
     */
    public function getCalledHook(): int
    {
        return $this->calledHookID;
    }

    /**
     * @param int $id
     */
    public function setCalledHook(int $id): void
    {
        $this->calledHookID = $id;
    }
}
