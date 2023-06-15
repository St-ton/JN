<?php declare(strict_types=1);

namespace JTL\Router;

use JTL\Helpers\Text;
use JTL\Language\LanguageHelper;
use JTL\Shop;

/**
 * Trait RoutableTrait
 * @package JTL\Router
 */
trait RoutableTrait
{
    /**
     * @var int
     */
    protected int $currentLanguageID;

    /**
     * @var string
     */
    protected string $currentLocale;

    /**
     * @var int
     */
    protected int $fallbackLanguageID;

    /**
     * @var string[]
     */
    protected array $urls = [];

    /**
     * @var string[]
     */
    protected array $paths = [];

    /**
     * @var string[]
     */
    protected array $slugs = [];

    /**
     * @var string
     */
    protected string $routeType;

    /**
     * @return int
     */
    public function getCurrentLanguageID(): int
    {
        return $this->currentLanguageID;
    }

    /**
     * @param int $currentLanguageID
     */
    public function setCurrentLanguageID(int $currentLanguageID): void
    {
        $this->currentLanguageID = $currentLanguageID;
    }

    /**
     * @return string
     */
    public function getRouteType(): string
    {
        return $this->routeType;
    }

    /**
     * @param string $routeType
     */
    public function setRouteType(string $routeType): void
    {
        $this->routeType = $routeType;
    }

    /**
     * @param int|null    $languageID
     * @param string|null $locale
     * @return void
     */
    public function initLanguageID(int $languageID = null, string $locale = null): void
    {
        if ($languageID === 0) {
            $languageID = null;
        }
        $this->currentLanguageID = $languageID ?? Shop::getLanguageID();
        if ($this->currentLanguageID === 0) {
            $this->currentLanguageID = (int)($_SESSION['kSprache'] ?? 1);
        }
        $this->fallbackLanguageID = LanguageHelper::getDefaultLanguage(true)->getId();
        $this->currentLocale      = Text::convertISO2ISO639($locale ?? Shop::getLanguageCode());
    }

    /**
     * @param int|null $fallbackID
     * @param array    $additional
     * @return void
     */
    public function createBySlug(?int $fallbackID = null, array $additional = []): void
    {
        $router    = Shop::getRouter();
        $languages = LanguageHelper::getAllLanguages();
        foreach ($this->getSlugs() as $langID => $slug) {
            foreach ($languages as $languageModel) {
                if ($languageModel->getId() === $langID) {
                    $locale = $languageModel->getIso639();
                    $route  = $router->getPathByType(
                        $this->getRouteType(),
                        \array_merge(['lang' => $locale, 'name' => $slug, 'id' => $fallbackID], $additional)
                    );
                    $this->setURLPath($route, $langID);
                    $url = $router->getURLByType(
                        $this->getRouteType(),
                        \array_merge(['lang' => $locale, 'name' => $slug, 'id' => $fallbackID], $additional)
                    );
                    $this->setURL($url, $langID);
                    break;
                }
            }
        }
        if ($fallbackID === null || \count($this->slugs) >= \count($languages)) {
            return;
        }
        foreach ($languages as $languageModel) {
            $langID = $languageModel->getId();
            if (\array_key_exists($langID, $this->slugs)) {
                continue;
            }
            $route = $router->getPathByType(
                $this->getRouteType(),
                \array_merge(['lang' => $languageModel->getIso639(), 'id' => $fallbackID], $additional)
            );
            $this->setURLPath($route, $langID);
            $url = $router->getURLByType(
                $this->getRouteType(),
                \array_merge(['lang' => $languageModel->getIso639(), 'id' => $fallbackID], $additional)
            );
            if (!$languageModel->isShopDefault() && !\str_contains($url, '?')) {
                $url .= '?lang=' . $languageModel->getCode();
            }
            $this->setURL($url, $langID);
        }
    }

    /**
     * @return array
     */
    public function getURLs(): array
    {
        return $this->urls;
    }

    /**
     * @param array $urls
     * @return void
     */
    public function setURLs(array $urls): void
    {
        $this->urls = $urls;
    }

    /**
     * @param int|null $idx
     * @return string|null
     */
    public function getURL(int $idx = null): ?string
    {
        return $this->urls[$idx ?? $this->currentLanguageID] ?? null;
    }

    /**
     * @return array
     */
    public function getURLPaths(): array
    {
        return $this->paths;
    }

    /**
     * @param array $paths
     * @return void
     */
    public function setURLPaths(array $paths): void
    {
        $this->paths = $paths;
    }

    /**
     * @param int|null $idx
     * @return string|null
     */
    public function getURLPath(int $idx = null): ?string
    {
        return $this->paths[$idx ?? $this->currentLanguageID] ?? '';
    }

    /**
     * @param string   $path
     * @param int|null $idx
     * @return void
     */
    public function setURLPath(string $path, int $idx = null): void
    {
        $this->paths[$idx ?? $this->currentLanguageID] = $path;
    }

    /**
     * @param string   $url
     * @param int|null $idx
     * @return void
     */
    public function setURL(string $url, int $idx = null): void
    {
        $this->urls[$idx ?? $this->currentLanguageID] = $url;
    }

    /**
     * @return string[]
     */
    public function getSlugs(): array
    {
        return $this->slugs;
    }

    /**
     * @param int|null $idx
     * @return string
     */
    public function getSlug(int $idx = null): string
    {
        return $this->slugs[$idx ?? $this->currentLanguageID] ?? '';
    }

    /**
     * @param string   $seo
     * @param int|null $idx
     * @return void
     */
    public function setSlug(string $seo, int $idx = null): void
    {
        $this->slugs[$idx ?? $this->currentLanguageID] = $seo;
    }
}
