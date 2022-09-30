<?php declare(strict_types=1);

namespace JTL\Plugin\Data;

/**
 * Class Paths
 * @package JTL\Plugin\Data
 */
class Paths
{
    /**
     * @var string
     */
    private string $shopURL;

    /**
     * @var string
     */
    private string $baseDir;

    /**
     * @var string
     */
    private string $basePath;

    /**
     * @var string
     */
    private string $versionedPath;

    /**
     * @var string
     */
    private string $frontendPath;

    /**
     * @var string
     */
    private string $frontendURL;

    /**
     * @var string
     */
    private string $baseURL;

    /**
     * @var string
     */
    private string $adminPath;

    /**
     * @var string
     */
    private string $adminURL;

    /**
     * @var string
     */
    private string $backendURL;

    /**
     * @var string
     */
    private string $backendRoute;

    /**
     * @var string|null
     */
    private ?string $licencePath = null;

    /**
     * @var string|null
     */
    private ?string $uninstaller = null;

    /**
     * @var string|null
     */
    private ?string $portletsPath = null;

    /**
     * @var string|null
     */
    private ?string $portletsUrl = null;

    /**
     * @var string|null
     */
    private ?string $exportPath = null;

    /**
     * @return string
     */
    public function getShopURL(): string
    {
        return $this->shopURL;
    }

    /**
     * @param string $shopURL
     */
    public function setShopURL(string $shopURL): void
    {
        $this->shopURL = $shopURL;
    }

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
    public function getBaseURL(): string
    {
        return $this->baseURL;
    }

    /**
     * @param string $baseURL
     */
    public function setBaseURL(string $baseURL): void
    {
        $this->baseURL = $baseURL;
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
     * @return string
     */
    public function getBackendURL(): string
    {
        return $this->backendURL;
    }

    /**
     * @param string $backendURL
     */
    public function setBackendURL(string $backendURL): void
    {
        $this->backendURL = $backendURL;
    }

    /**
     * @param string $route
     */
    public function setBackendRoute(string $route): void
    {
        $this->backendRoute = $route;
    }

    /**
     * @return string
     */
    public function getBackendRoute(): string
    {
        return $this->backendRoute;
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

    /**
     * @return string|null
     */
    public function getPortletsPath(): ?string
    {
        return $this->portletsPath;
    }

    /**
     * @param string|null $portletsPath
     */
    public function setPortletsPath(?string $portletsPath): void
    {
        $this->portletsPath = $portletsPath;
    }

    /**
     * @return string|null
     */
    public function getPortletsUrl(): ?string
    {
        return $this->portletsUrl;
    }

    /**
     * @param string|null $portletsUrl
     */
    public function setPortletsUrl(?string $portletsUrl): void
    {
        $this->portletsUrl = $portletsUrl;
    }

    /**
     * @return string|null
     */
    public function getExportPath(): ?string
    {
        return $this->exportPath;
    }

    /**
     * @param string|null $exportPath
     */
    public function setExportPath(?string $exportPath): void
    {
        $this->exportPath = $exportPath;
    }
}
