<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Plugin\ExtensionData;

/**
 * Class Paths
 * @package Plugin
 */
class Paths
{
    /**
     * @var string
     */
    private $baseDir;

    /**
     * @var string
     */
    private $basePath;

    /**
     * @var string
     */
    private $versionedPath;

    /**
     * @var string
     */
    private $frontendPath;

    /**
     * @var string
     */
    private $frontendURL;

    /**
     * @var string
     */
    private $adminPath;

    /**
     * @var string
     */
    private $adminURL;

    /**
     * @var string|null
     */
    private $licencePath;

    /**
     * @var string|null
     */
    private $uninstaller;

    /**
     * @return string
     */
    public function getBaseDir(): string
    {
        return $this->baseDir;
    }

    /**
     * @param string $baseDir
     */
    public function setBaseDir(string $baseDir): void
    {
        $this->baseDir = $baseDir;
    }

    /**
     * @return string
     */
    public function getBasePath(): string
    {
        return $this->basePath;
    }

    /**
     * @param string $basePath
     */
    public function setBasePath(string $basePath): void
    {
        $this->basePath = $basePath;
    }

    /**
     * @return string
     */
    public function getVersionedPath(): string
    {
        return $this->versionedPath;
    }

    /**
     * @param string $versionedPath
     */
    public function setVersionedPath(string $versionedPath): void
    {
        $this->versionedPath = $versionedPath;
    }

    /**
     * @return string
     */
    public function getFrontendPath(): string
    {
        return $this->frontendPath;
    }

    /**
     * @param string $frontendPath
     */
    public function setFrontendPath(string $frontendPath): void
    {
        $this->frontendPath = $frontendPath;
    }

    /**
     * @return string
     */
    public function getFrontendURL(): string
    {
        return $this->frontendURL;
    }

    /**
     * @param string $frontendURL
     */
    public function setFrontendURL(string $frontendURL): void
    {
        $this->frontendURL = $frontendURL;
    }

    /**
     * @return string
     */
    public function getAdminPath(): string
    {
        return $this->adminPath;
    }

    /**
     * @param string $adminPath
     */
    public function setAdminPath(string $adminPath): void
    {
        $this->adminPath = $adminPath;
    }

    /**
     * @return string
     */
    public function getAdminURL(): string
    {
        return $this->adminURL;
    }

    /**
     * @param string $adminURL
     */
    public function setAdminURL(string $adminURL): void
    {
        $this->adminURL = $adminURL;
    }

    /**
     * @return string|null
     */
    public function getLicencePath(): ?string
    {
        return $this->licencePath;
    }

    /**
     * @param string $licencePath
     */
    public function setLicencePath(string $licencePath): void
    {
        $this->licencePath = $licencePath;
    }

    /**
     * @return string|null
     */
    public function getUninstaller(): ?string
    {
        return $this->uninstaller;
    }

    /**
     * @param string|null $uninstaller
     */
    public function setUninstaller(?string $uninstaller): void
    {
        $this->uninstaller = $uninstaller;
    }
}
