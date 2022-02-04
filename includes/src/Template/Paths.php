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
    private string $rootDir = \PFAD_ROOT . \PFAD_TEMPLATES;

    /**
     * @var string - like '/var/www/shop/templates_c/mytemplate/'
     */
    private string $compileDir;

    /**
     * @var string - like '/var/www/shop/templates_c/mytemplate/page_cache/'
     */
    private string $cacheDir;

    /**
     * @var string - like 'https://example.com/templates/'
     */
    private string $rootURL;

    /**
     * @var string - like '/var/www/shop/templates/mytemplate/'
     */
    private string $baseDir;

    /**
     * @var string - like 'templates/mytemplate/'
     */
    private string $baseRelDir;

    /**
     * @var string - like 'mytemplate'
     */
    private string $baseDirName;

    /**
     * @var string - like 'https://example.com/templates/mytemplate/'
     */
    private string $baseURL;

    /**
     * @var string|null - like '/var/www/shop/templates/NOVA/'
     */
    private ?string $parentDir = null;

    /**
     * @var string|null - like 'templates/NOVA/'
     */
    private ?string $parentRelDir = null;

    /**
     * @var string|null - like 'NOVA'
     */
    private ?string $parentDirName;

    /**
     * @var string - like 'https://example.com/templates/NOVA/'
     */
    private string $parentURL;

    /**
     * @var string - like '/var/www/shop/templates/mytemplate/themes/mytheme'
     */
    private string $themeDir = '';

    /**
     * @var string - like 'templates/mytemplate/themes/mytheme'
     */
    private string $themeRelDir = '';

    /**
     * @var string - like 'mytheme'
     */
    private string $themeDirName = '';

    /**
     * @var string - like 'https://example.com/templates/mytemplate/themes/mytheme/'
     */
    private string $themeURL;

    /**
     * @var string - like 'mytheme' if realThemeDir exists - parent theme dir otherwise
     */
    private string $realThemeDirName;

    /**
     * @var string - like '/var/www/shop/templates/mytemplate/themes/mytheme' if exists - parent otherwise
     */
    private string $realThemeDir;

    /**
     * @var string - like 'templates/mytemplate/themes/mytheme' if exists - parent otherwise
     */
    private string $realRelThemeDir;

    /**
     * @var string - like 'https://example.com/templates/mytemplate/themes/mytheme/' if exists - parent otherwise
     */
    private string $realThemeURL;

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
        $this->compileDir  = \PFAD_ROOT . \PFAD_COMPILEDIR . $themeBaseDir . '/';
        $this->cacheDir    = $this->compileDir . 'page_cache/';
        $this->baseDirName = $themeBaseDir;
        $this->baseRelDir  = \PFAD_TEMPLATES . $this->baseDirName . '/';
        $this->baseDir     = $this->rootDir . $this->baseDirName . '/';
        $this->baseURL     = $shopURL . $this->baseRelDir;
        if ($parentDir !== null) {
            $this->parentDirName = $parentDir;
            $this->parentRelDir  = \PFAD_TEMPLATES . $parentDir . '/';
            $this->parentDir     = $this->rootDir . $parentDir . '/';
            $this->parentURL     = $shopURL . $this->parentRelDir;
            if (!\is_dir($this->parentDir)) {
                throw new InvalidArgumentException('Parent path does not exist: ' . $this->parentDir);
            }
        }
        if ($themeName !== null) {
            $this->themeDirName = $themeName;
            $this->themeDir     = $this->baseDir . 'themes/' . $themeName . '/';
            $this->themeRelDir  = $this->baseRelDir . 'themes/' . $themeName . '/';
            $this->themeURL     = $shopURL . $this->themeRelDir;

            $this->realThemeDirName = $this->themeDirName;
            $this->realThemeDir     = $this->themeDir;
            $this->realRelThemeDir  = $this->themeRelDir;

            $parentThemePath = $this->parentDir . 'themes/' . $themeName . '/';
            if ($parentDir !== null && !\is_dir($this->themeDir) && \is_dir($parentThemePath)) {
                $this->realThemeDir    = $parentThemePath;
                $this->realRelThemeDir = $this->parentRelDir . 'themes/' . $themeName . '/';
            }
            $this->realThemeURL = $shopURL . $this->realRelThemeDir;
            if (!\is_dir($this->realThemeDir)) {
                throw new InvalidArgumentException('Theme path does not exist: ' . $this->realThemeDir);
            }
        }
    }

    /**
     * @return string
     */
    public function getRootDir(): string
    {
        return $this->rootDir;
    }

    /**
     * @param string $rootDir
     */
    public function setRootDir(string $rootDir): void
    {
        $this->rootDir = $rootDir;
    }

    /**
     * @return string
     */
    public function getCompileDir(): string
    {
        return $this->compileDir;
    }

    /**
     * @param string $compileDir
     */
    public function setCompileDir(string $compileDir): void
    {
        $this->compileDir = $compileDir;
    }

    /**
     * @return string
     */
    public function getCacheDir(): string
    {
        return $this->cacheDir;
    }

    /**
     * @param string $cacheDir
     */
    public function setCacheDir(string $cacheDir): void
    {
        $this->cacheDir = $cacheDir;
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
    public function getBaseRelDir(): string
    {
        return $this->baseRelDir;
    }

    /**
     * @param string $baseRelDir
     */
    public function setBaseRelDir(string $baseRelDir): void
    {
        $this->baseRelDir = $baseRelDir;
    }

    /**
     * @return string
     */
    public function getBaseDirName(): string
    {
        return $this->baseDirName;
    }

    /**
     * @param string $baseDirName
     */
    public function setBaseDirName(string $baseDirName): void
    {
        $this->baseDirName = $baseDirName;
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
     * @return string|null
     */
    public function getParentRelDir(): ?string
    {
        return $this->parentRelDir;
    }

    /**
     * @param string|null $parentRelDir
     */
    public function setParentRelDir(?string $parentRelDir): void
    {
        $this->parentRelDir = $parentRelDir;
    }

    /**
     * @return string|null
     */
    public function getParentDirName(): ?string
    {
        return $this->parentDirName;
    }

    /**
     * @param string|null $parentDirName
     */
    public function setParentDirName(?string $parentDirName): void
    {
        $this->parentDirName = $parentDirName;
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
    public function getThemeRelDir(): string
    {
        return $this->themeRelDir;
    }

    /**
     * @param string $themeRelDir
     */
    public function setThemeRelDir(string $themeRelDir): void
    {
        $this->themeRelDir = $themeRelDir;
    }

    /**
     * @return string
     */
    public function getThemeDirName(): string
    {
        return $this->themeDirName;
    }

    /**
     * @param string $themeDirName
     */
    public function setThemeDirName(string $themeDirName): void
    {
        $this->themeDirName = $themeDirName;
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
    public function getRealThemeDirName(): string
    {
        return $this->realThemeDirName;
    }

    /**
     * @param string $realThemeDirName
     */
    public function setRealThemeDirName(string $realThemeDirName): void
    {
        $this->realThemeDirName = $realThemeDirName;
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
    public function getRealRelThemeDir(): string
    {
        return $this->realRelThemeDir;
    }

    /**
     * @param string $realRelThemeDir
     */
    public function setRealRelThemeDir(string $realRelThemeDir): void
    {
        $this->realRelThemeDir = $realRelThemeDir;
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
