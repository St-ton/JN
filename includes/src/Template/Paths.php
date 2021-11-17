<?php declare(strict_types=1);

namespace JTL\Template;

use InvalidArgumentException;

/**
 * Class Paths
 * @package JTL\Template
 */
class Paths
{
    /**
     * @var string - like '/var/www/shop/templates/'
     */
    private $rootPath = \PFAD_ROOT . \PFAD_TEMPLATES;

    /**
     * @var string - like 'https://example.com/templates/'
     */
    private $rootURL;

    /**
     * @var string - like '/var/www/shop/templates/mytemplate/'
     */
    private $basePath;

    /**
     * @var string - like 'templates/mytemplate/'
     */
    private $baseRelPath;

    /**
     * @var string - like 'mytemplate'
     */
    private $baseDir;

    /**
     * @var string - like 'https://example.com/templates/mytemplate/'
     */
    private $baseURL;

    /**
     * @var string|null - like '/var/www/shop/templates/NOVA/'
     */
    private $parentPath;

    /**
     * @var string|null - like 'templates/NOVA/'
     */
    private $parentRelPath;

    /**
     * @var string|null - like 'NOVA'
     */
    private $parentDir;

    /**
     * @var string - like 'https://example.com/templates/NOVA/'
     */
    private $parentURL;

    /**
     * @var string - like '/var/www/shop/templates/mytemplate/themes/mytheme'
     */
    private $themePath = '';

    /**
     * @var string - like 'templates/mytemplate/themes/mytheme'
     */
    private $themeRelPath = '';

    /**
     * @var string - like 'mytheme'
     */
    private $themeDir = '';

    /**
     * @var string - like 'https://example.com/templates/mytemplate/themes/mytheme/'
     */
    private $themeURL;

    /**
     * @var string - like 'mytheme' if realThemeDir exists - parent theme dir otherwise
     */
    private $realThemeDir;

    /**
     * @var string - like '/var/www/shop/templates/mytemplate/themes/mytheme' if exists - parent otherwise
     */
    private $realThemePath;

    /**
     * @var string - like 'templates/mytemplate/themes/mytheme' if exists - parent otherwise
     */
    private $realRelThemePath;

    /**
     * @var string - like 'https://example.com/templates/mytemplate/themes/mytheme/' if exists - parent otherwise
     */
    private $realThemeURL;

    /**
     * @param string      $themeBaseDir
     * @param string      $shopURL
     * @param string|null $parentDir
     * @param string|null $themeName
     */
    public function __construct(string $themeBaseDir, string $shopURL, ?string $parentDir, ?string $themeName)
    {
        $shopURL           = \rtrim($shopURL, '/') . '/';
        $this->rootURL     = $shopURL . \PFAD_TEMPLATES;
        $this->baseDir     = $themeBaseDir;
        $this->baseRelPath = \PFAD_TEMPLATES . $this->baseDir . '/';
        $this->basePath    = $this->rootPath . $this->baseDir . '/';
        $this->baseURL     = $shopURL . $this->baseRelPath;
        if ($parentDir !== null) {
            $this->parentDir     = $parentDir;
            $this->parentRelPath = \PFAD_TEMPLATES . $parentDir . '/';
            $this->parentPath    = $this->rootPath . $parentDir . '/';
            $this->parentURL     = $shopURL . $this->parentRelPath;
            if (!\is_dir($this->parentPath)) {
                throw new InvalidArgumentException('Theme dir does not exist: ' . $this->themeDir);
            }
        }
        if ($themeName !== null) {
            $this->themeDir     = $themeName;
            $this->themePath    = $this->basePath . 'themes/' . $themeName . '/';
            $this->themeRelPath = $this->baseRelPath . 'themes/' . $themeName . '/';
            $this->themeURL     = $shopURL . $this->themeRelPath;

            $this->realThemeDir     = $this->themeDir;
            $this->realThemePath    = $this->themePath;
            $this->realRelThemePath = $this->themeRelPath;

            $parentThemePath = $this->parentPath . 'themes/' . $themeName . '/';
            if ($parentDir !== null && !\is_dir($this->themePath) && \is_dir($parentThemePath)) {
                $this->realThemePath    = $parentThemePath;
                $this->realRelThemePath = $this->parentRelPath . 'themes/' . $themeName . '/';
            }
            $this->realThemeURL = $shopURL . $this->realRelThemePath;
            if (!\is_dir($this->realThemePath)) {
                throw new InvalidArgumentException('Theme dir for ' . $this->themeDir . ' does not exist: ' . $this->realThemePath);
            }
        }
    }

    /**
     * @return string
     */
    public function getRootPath(): string
    {
        return $this->rootPath;
    }

    /**
     * @param string $rootPath
     */
    public function setRootPath(string $rootPath): void
    {
        $this->rootPath = $rootPath;
    }

    /**
     * @return string
     */
    public function getRootURL(): string
    {
        return $this->rootURL;
    }

    /**
     * @param string $rootURL
     */
    public function setRootURL(string $rootURL): void
    {
        $this->rootURL = $rootURL;
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
    public function getBaseRelPath(): string
    {
        return $this->baseRelPath;
    }

    /**
     * @param string $baseRelPath
     */
    public function setBaseRelPath(string $baseRelPath): void
    {
        $this->baseRelPath = $baseRelPath;
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
     * @return string|null
     */
    public function getParentPath(): ?string
    {
        return $this->parentPath;
    }

    /**
     * @param string|null $parentPath
     */
    public function setParentPath(?string $parentPath): void
    {
        $this->parentPath = $parentPath;
    }

    /**
     * @return string|null
     */
    public function getParentRelPath(): ?string
    {
        return $this->parentRelPath;
    }

    /**
     * @param string|null $parentRelPath
     */
    public function setParentRelPath(?string $parentRelPath): void
    {
        $this->parentRelPath = $parentRelPath;
    }

    /**
     * @return string|null
     */
    public function getParentDir(): ?string
    {
        return $this->parentDir;
    }

    /**
     * @param string|null $parentDir
     */
    public function setParentDir(?string $parentDir): void
    {
        $this->parentDir = $parentDir;
    }

    /**
     * @return string
     */
    public function getParentURL(): string
    {
        return $this->parentURL;
    }

    /**
     * @param string $parentURL
     */
    public function setParentURL(string $parentURL): void
    {
        $this->parentURL = $parentURL;
    }

    /**
     * @return string
     */
    public function getThemePath(): string
    {
        return $this->themePath;
    }

    /**
     * @param string $themePath
     */
    public function setThemePath(string $themePath): void
    {
        $this->themePath = $themePath;
    }

    /**
     * @return string
     */
    public function getThemeRelPath(): string
    {
        return $this->themeRelPath;
    }

    /**
     * @param string $themeRelPath
     */
    public function setThemeRelPath(string $themeRelPath): void
    {
        $this->themeRelPath = $themeRelPath;
    }

    /**
     * @return string
     */
    public function getThemeDir(): string
    {
        return $this->themeDir;
    }

    /**
     * @param string $themeDir
     */
    public function setThemeDir(string $themeDir): void
    {
        $this->themeDir = $themeDir;
    }

    /**
     * @return string
     */
    public function getThemeURL(): string
    {
        return $this->themeURL;
    }

    /**
     * @param string $themeURL
     */
    public function setThemeURL(string $themeURL): void
    {
        $this->themeURL = $themeURL;
    }

    /**
     * @return string
     */
    public function getRealThemeDir(): string
    {
        return $this->realThemeDir;
    }

    /**
     * @param string $realThemeDir
     */
    public function setRealThemeDir(string $realThemeDir): void
    {
        $this->realThemeDir = $realThemeDir;
    }

    /**
     * @return string
     */
    public function getRealThemePath(): string
    {
        return $this->realThemePath;
    }

    /**
     * @param string $realThemePath
     */
    public function setRealThemePath(string $realThemePath): void
    {
        $this->realThemePath = $realThemePath;
    }

    /**
     * @return string
     */
    public function getRealRelThemePath(): string
    {
        return $this->realRelThemePath;
    }

    /**
     * @param string $realRelThemePath
     */
    public function setRealRelThemePath(string $realRelThemePath): void
    {
        $this->realRelThemePath = $realRelThemePath;
    }

    /**
     * @return string
     */
    public function getRealThemeURL(): string
    {
        return $this->realThemeURL;
    }

    /**
     * @param string $realThemeURL
     */
    public function setRealThemeURL(string $realThemeURL): void
    {
        $this->realThemeURL = $realThemeURL;
    }
}
