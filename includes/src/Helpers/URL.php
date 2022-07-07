<?php declare(strict_types=1);

namespace JTL\Helpers;

use JTL\Catalog\Category\MenuItem;
use JTL\Catalog\Hersteller;
use JTL\Catalog\Product\MerkmalWert;
use JTL\Language\LanguageHelper;
use JTL\Link\LinkInterface;
use JTL\News\Item;
use JTL\Router\Router;
use JTL\Shop;

/**
 * Class URL
 * @package JTL\Helpers
 */
class URL
{
    /**
     * @var string
     */
    private $url = '';

    /**
     * @var string
     */
    private $scheme = '';

    /**
     * @var string
     */
    private $host = '';

    /**
     * @var string
     */
    private $port = '';

    /**
     * @var string
     */
    private $user = '';

    /**
     * @var string
     */
    private $pass = '';

    /**
     * @var string
     */
    private $path = '';

    /**
     * @var string
     */
    private $query = '';

    /**
     * @var string
     */
    private $fragment = '';

    /**
     * @var array
     */
    private array $defaultPorts = ['http' => '80', 'https' => '443'];

    /**
     * @param string|null $url
     */
    public function __construct(?string $url = null)
    {
        if ($url !== null) {
            $this->setUrl($url);
        }
    }

    /**
     * @return string|null
     */
    public function getUrl(): ?string
    {
        return $this->url;
    }

    /**
     * @param string $url
     * @return bool
     */
    public function setUrl(string $url): bool
    {
        $this->url  = $url;
        $components = \parse_url($this->url);
        if (!$components) {
            return false;
        }
        foreach ($components as $key => $value) {
            if (\property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
        $this->port = (string)$this->port;

        return true;
    }

    /**
     * @return string
     */
    public function getScheme(): string
    {
        return $this->scheme;
    }

    /**
     * @return string
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * @param string $host
     */
    public function setHost(string $host): void
    {
        $this->host = $host;
    }

    /**
     * @return string
     */
    public function getPort(): string
    {
        return $this->port;
    }

    /**
     * @param string $port
     */
    public function setPort(string $port): void
    {
        $this->port = $port;
    }

    /**
     * @return string
     */
    public function getUser(): string
    {
        return $this->user;
    }

    /**
     * @param string $user
     */
    public function setUser(string $user): void
    {
        $this->user = $user;
    }

    /**
     * @return string
     */
    public function getPass(): string
    {
        return $this->pass;
    }

    /**
     * @param string $pass
     */
    public function setPass(string $pass): void
    {
        $this->pass = $pass;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @param string $path
     */
    public function setPath(string $path): void
    {
        $this->path = $path;
    }

    /**
     * @return string
     */
    public function getQuery(): string
    {
        return $this->query;
    }

    /**
     * @param string $query
     */
    public function setQuery(string $query): void
    {
        $this->query = $query;
    }

    /**
     * @return string
     */
    public function getFragment(): string
    {
        return $this->fragment;
    }

    /**
     * @param string $fragment
     */
    public function setFragment(string $fragment): void
    {
        $this->fragment = $fragment;
    }

    /**
     * @return string
     */
    public function normalize(): string
    {
        if ($this->path) {
            // case normalization
            $this->path = \preg_replace_callback(
                '/(%([\dabcdef][\dabcdef]))/x',
                static function ($x) {
                    return '%' . \mb_convert_case($x[2], \MB_CASE_UPPER);
                },
                $this->path
            );
            // percent-encoding normalization
            $this->path = $this->urlDecodeUnreservedChars($this->path);
            // path segment normalization
            $this->path = $this->removeDotSegments($this->path);
        }

        $scheme = '';
        if ($this->scheme) {
            $this->scheme = \mb_convert_case($this->scheme, \MB_CASE_LOWER);
            $scheme       = $this->scheme . '://';
        }

        if ($this->host) {
            $this->host = \mb_convert_case($this->host, \MB_CASE_LOWER);
        }

        $this->schemeBasedNormalization();

        // reconstruct uri
        $query = '';
        if ($this->query) {
            $query = '?' . $this->query;
        }

        $fragment = '';
        if ($this->fragment) {
            $fragment = '#' . $this->fragment;
        }

        $port = '';
        if ($this->port) {
            $port = ':' . $this->port;
        }

        $authorization = '';
        if ($this->user) {
            $authorization = $this->user . ':' . $this->pass . '@';
        }

        return $scheme . $authorization . $this->host . $port . $this->path . $query . $fragment;
    }

    /**
     * Decode unreserved characters
     *
     * @see https://datatracker.ietf.org/doc/html/rfc3986#section-2.3
     * @param string $string
     * @return mixed
     */
    public function urlDecodeUnreservedChars(string $string)
    {
        $unreserved = [];
        for ($octet = 65; $octet <= 90; $octet++) {
            $unreserved[] = \dechex($octet);
        }
        for ($octet = 97; $octet <= 122; $octet++) {
            $unreserved[] = \dechex($octet);
        }
        for ($octet = 48; $octet <= 57; $octet++) {
            $unreserved[] = \dechex($octet);
        }

        $unreserved[] = \dechex(\mb_ord('-'));
        $unreserved[] = \dechex(\mb_ord('.'));
        $unreserved[] = \dechex(\mb_ord('_'));
        $unreserved[] = \dechex(\mb_ord('~'));

        return \preg_replace_callback(\array_map(
            static function ($str) {
                return '/%' . \mb_convert_case($str, \MB_CASE_UPPER) . '/x';
            },
            $unreserved
        ), static function ($matches) {
            $match = \str_starts_with($matches[0], '%') ? \mb_substr($matches[0], 1) : $matches[0];
            // php7.4+ expects strings like "7E" instead of "%7E"
            return \chr(\hexdec($match));
        }, $string);
    }

    /**
     * Path segment normalization
     *
     * @see https://datatracker.ietf.org/doc/html/rfc3986#section-5.2.4
     * @param string $path
     * @return string
     */
    public function removeDotSegments(string $path): string
    {
        $new_path = '';
        while (!empty($path)) {
            // A
            $pattern_a   = '!^(\.\./|\./)!x';
            $pattern_b_1 = '!^(/\./)!x';
            $pattern_b_2 = '!^(/\.)$!x';
            $pattern_c   = '!^(/\.\./|/\.\.)!x';
            $pattern_d   = '!^(\.|\.\.)$!x';
            $pattern_e   = '!(/*[^/]*)!x';

            if (\preg_match($pattern_a, $path)) {
                // remove prefix from $path
                $path = \preg_replace($pattern_a, '', $path);
            } elseif (\preg_match($pattern_b_1, $path, $matches) || \preg_match($pattern_b_2, $path, $matches)) {
                $path = \preg_replace('!^' . $matches[1] . '!', '/', $path);
            } elseif (\preg_match($pattern_c, $path, $matches)) {
                $path = \preg_replace('!^' . \preg_quote($matches[1], '!') . '!x', '/', $path);
                // remove the last segment and its preceding "/" (if any) from output buffer
                $new_path = \preg_replace('!/([^/]+)$!x', '', $new_path);
            } elseif (\preg_match($pattern_d, $path)) {
                $path = \preg_replace($pattern_d, '', $path);
            } elseif (\preg_match($pattern_e, $path, $matches)) {
                $first_path_segment = $matches[1];

                $path = \preg_replace('/^' . \preg_quote($first_path_segment, '/') . '/', '', $path, 1);

                $new_path .= $first_path_segment;
            }
        }

        return $new_path;
    }

    /**
     * @return $this;
     */
    private function schemeBasedNormalization(): self
    {
        if (isset($this->defaultPorts[$this->scheme]) && $this->defaultPorts[$this->scheme] === $this->port) {
            $this->port = '';
        }

        return $this;
    }

    /**
     * Build an URL string from a given associative array of parts according to PHP's \parse_url()
     *
     * @param array $parts
     * @return string - the resulting URL
     */
    public static function unparseURL(array $parts): string
    {
        return (isset($parts['scheme']) ? $parts['scheme'] . '://' : '')
            . (isset($parts['user']) ? $parts['user'] . (isset($parts['pass']) ? ':' . $parts['pass'] : '') . '@' : '')
            . ($parts['host'] ?? '')
            . (isset($parts['port']) ? ':' . $parts['port'] : '')
            . ($parts['path'] ?? '')
            . (isset($parts['query']) ? '?' . $parts['query'] : '')
            . (isset($parts['fragment']) ? '#' . $parts['fragment'] : '');
    }

    /**
     * @return string
     */
    private static function getLocalizedFallback(): string
    {
        return LanguageHelper::isDefaultLanguageActive(true)
            ? ''
            : ('&lang=' . Shop::getLanguageCode());
    }

    /**
     * @param object      $obj
     * @param int         $type
     * @param bool        $full
     * @param string|null $prefix
     * @return string
     * @former baueURL()
     * @since 5.0.0
     */
    public static function buildURL($obj, int $type, bool $full = false, ?string $prefix = null): string
    {
        if ($obj instanceof LinkInterface) {
            return $obj->getURL();
        }
        $prefix     = $prefix ?? ($full === false ? '' : (Shop::getURL() . '/'));
        $router     = Shop::getRouter();
        $localeCode = Text::convertISO2ISO639(Shop::getLanguageCode());
        \executeHook(\HOOK_TOOLSGLOBAL_INC_SWITCH_BAUEURL, ['obj' => &$obj, 'art' => &$type]);
        switch ($type) {
            case \URLART_ARTIKEL:
                $route = null;
                if (!empty($obj->cSeo)) {
                    $route = $router->getPathByType(
                        Router::TYPE_PRODUCT,
                        ['lang' => $localeCode, 'name' => $obj->cSeo, 'id' => $obj->kArtikel ?? null]
                    );
                }
                return $route !== null
                    ? \rtrim($prefix, '/') . $route
                    : $prefix . '?a=' . $obj->kArtikel . self::getLocalizedFallback();

            case \URLART_KATEGORIE:
                if ($obj instanceof MenuItem) {
                    $base  = $obj->getURL();
                    $route = $router->getPathByType(
                        Router::TYPE_CATEGORY,
                        ['lang' => $localeCode, 'name' => $base, 'id' => $obj->getID()]
                    );

                    return \rtrim($prefix, '/') . $route;
                }
                return !empty($obj->cSeo)
                    ? $prefix . $obj->cSeo
                    : $prefix . '?k=' . $obj->getID() . self::getLocalizedFallback();

            case \URLART_SEITE:
                $route = null;
                if (!empty($obj->cSeo)) {
                    $route = $router->getPathByType(
                        Router::TYPE_PAGE,
                        ['lang' => $localeCode, 'name' => $obj->cSeo, 'id' => $obj->kLink ?? null]
                    );
                }
                if ($route !== null) {
                    return \rtrim($prefix, '/') . $route;
                }
                if (isset($_SESSION['cISOSprache'], $obj->cLocalizedSeo[$_SESSION['cISOSprache']])
                    && \mb_strlen($obj->cLocalizedSeo[$_SESSION['cISOSprache']])
                ) {
                    return $prefix . $obj->cLocalizedSeo[$_SESSION['cISOSprache']];
                }
                // Hole aktuelle Spezialseite und gib den URL Dateinamen zurück
                $specialPage = Shop::Container()->getDB()->select(
                    'tspezialseite',
                    'nLinkart',
                    (int)$obj->nLinkart
                );

                return !empty($specialPage->cDateiname)
                    ? $prefix . $specialPage->cDateiname
                    : $prefix . '?s=' . $obj->kLink . self::getLocalizedFallback();

            case \URLART_HERSTELLER:
                if ($obj instanceof Hersteller) {
                    return $obj->getURL();
                }
                return !empty($obj->cSeo)
                    ? $prefix . $obj->cSeo
                    : $prefix . '?h=' . $obj->kHersteller . self::getLocalizedFallback();

            case \URLART_LIVESUCHE:
                return !empty($obj->cSeo)
                    ? $prefix . $obj->cSeo
                    : $prefix . '?l=' . $obj->kSuchanfrage . self::getLocalizedFallback();

            case \URLART_MERKMAL:
                if ($obj instanceof MerkmalWert) {
                    return $obj->getURL();
                }
                return !empty($obj->cSeo)
                    ? $prefix . $obj->cSeo
                    : $prefix . '?m=' . $obj->kMerkmalWert . self::getLocalizedFallback();

            case \URLART_NEWS:
                if ($obj instanceof Item) {
                    /** @var Item $obj */
                    return !empty($obj->getSEO())
                        ? $obj->getURL()
                        : $prefix . '?n=' . $obj->getID() . self::getLocalizedFallback();
                }
                if (!empty($obj->cSeo)) {
                    $url = Shop::getRouter()->getPathByType(
                        Router::TYPE_NEWS,
                        ['id' => $obj->kNews, 'name' => $obj->cSeo, 'lang' => $localeCode]
                    );
                } else {
                    $url = Shop::getRouter()->getPathByType(
                        Router::TYPE_NEWS,
                        ['id' => $obj->kNews, 'lang' => $localeCode],
                        false
                    );
                }

                return \rtrim($prefix, '/') . $url;

            case \URLART_NEWSMONAT:
                return !empty($obj->cSeo)
                    ? $prefix . $obj->cSeo
                    : $prefix . '?nm=' . $obj->kNewsMonatsUebersicht . self::getLocalizedFallback();

            case \URLART_NEWSKATEGORIE:
                if (!empty($obj->cSeo)) {
                    $url = Shop::getRouter()->getPathByType(
                        Router::TYPE_NEWS,
                        ['id' => $obj->kNewsKategorie, 'name' => $obj->cSeo, 'lang' => $localeCode]
                    );
                } else {
                    $url = Shop::getRouter()->getPathByType(
                        Router::TYPE_NEWS,
                        ['id' => $obj->kNewsKategorie, 'lang' => $localeCode],
                        false
                    );
                }

                return \rtrim($prefix, '/') . $url;

            case \URLART_SEARCHSPECIALS:
                if (!empty($obj->cSeo)) {
                    $url = Shop::getRouter()->getPathByType(
                        Router::TYPE_SEARCH_SPECIAL,
                        ['id' => $obj->kSuchspecial, 'name' => $obj->cSeo, 'lang' => $localeCode]
                    );
                } else {
                    $url = Shop::getRouter()->getPathByType(
                        Router::TYPE_SEARCH_SPECIAL,
                        ['id' => $obj->kSuchspecial, 'lang' => $localeCode],
                        false
                    );
                }

                return \rtrim($prefix, '/') .  $url;
            default:
                return '';
        }
    }
}
