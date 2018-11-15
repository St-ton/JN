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
    private $basePath;

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
     * @var string
     */
    private $licencePath;

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
     * @return string
     */
    public function getLicencePath(): string
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
}
