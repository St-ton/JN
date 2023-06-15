<?php declare(strict_types=1);

namespace JTL;

use JTL\DB\DbInterface;
use JTL\Filter\FilterInterface;
use JTL\Filter\ProductFilter;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Helpers\URL;
use stdClass;

/**
 * Class Redirect
 * @package JTL
 */
class Redirect
{
    /**
     * @var int|null
     */
    public ?int $kRedirect = null;

    /**
     * @var string|null
     */
    public ?string $cFromUrl = null;

    /**
     * @var string|null
     */
    public ?string $cToUrl = null;

    /**
     * @var string|null
     */
    public ?string $cAvailable = null;

    /**
     * @var int
     */
    public int $nCount = 0;

    /**
     * @var int
     */
    public int $paramHandling = 0;

    /**
     * @var DbInterface
     */
    protected DbInterface $db;

    /**
     * @param int $id
     */
    public function __construct(int $id = 0)
    {
        $this->db = Shop::Container()->getDB();
        if ($id > 0) {
            $this->loadFromDB($id);
        }
    }

    /**
     * @param int $id
     * @return $this
     */
    public function loadFromDB(int $id): self
    {
        $obj = $this->db->select('tredirect', 'kRedirect', $id);
        if ($obj !== null && $obj->kRedirect > 0) {
            $this->kRedirect     = (int)$obj->kRedirect;
            $this->nCount        = (int)$obj->nCount;
            $this->paramHandling = (int)$obj->paramHandling;
            $this->cFromUrl      = $obj->cFromUrl;
            $this->cToUrl        = $obj->cToUrl;
            $this->cAvailable    = $obj->cAvailable;
        }

        return $this;
    }

    /**
     * @param string $url
     * @return null|stdClass
     */
    public function find(string $url): ?stdClass
    {
        return $this->db->select(
            'tredirect',
            'cFromUrl',
            \mb_substr($this->normalize($url), 0, 255)
        );
    }

    /**
     * Get a redirect by target
     *
     * @param string $targetURL target to search for
     * @return null|stdClass
     */
    public function getRedirectByTarget(string $targetURL): ?stdClass
    {
        return $this->db->select('tredirect', 'cToUrl', $this->normalize($targetURL));
    }

    /**
     * @param string $source
     * @param string $destination
     * @return bool
     */
    public function isDeadlock(string $source, string $destination): bool
    {
        $path        = \parse_url(Shop::getURL(), \PHP_URL_PATH);
        $destination = $path !== null ? ($path . '/' . $destination) : $destination;
        $redirect    = $this->db->select('tredirect', 'cFromUrl', $destination, 'cToUrl', $source);

        return $redirect !== null && (int)$redirect->kRedirect > 0;
    }

    /**
     * @param string $source
     * @param string $destination
     * @param bool   $force
     * @param int    $handling
     * @return bool
     */
    public function saveExt(string $source, string $destination, bool $force = false, int $handling = 0): bool
    {
        if (\mb_strlen($source) > 0) {
            $source = $this->normalize($source);
        }
        if (\mb_strlen($destination) > 0) {
            $destination = $this->normalize($destination);
        }
        if ($source === $destination) {
            return false;
        }

        $oldRedirects = $this->db->getObjects(
            'SELECT * FROM tredirect WHERE cToUrl = :source',
            ['source' => $source]
        );
        foreach ($oldRedirects as $oldRedirect) {
            $oldRedirect->cToUrl = $destination;
            if ($oldRedirect->cFromUrl === $destination) {
                $this->db->delete('tredirect', 'kRedirect', (int)$oldRedirect->kRedirect);
            } else {
                $this->db->updateRow('tredirect', 'kRedirect', (int)$oldRedirect->kRedirect, $oldRedirect);
            }
        }

        if ($force
            || (self::checkAvailability($destination)
                && \mb_strlen($source) > 1
                && \mb_strlen($destination) > 1)
        ) {
            if ($this->isDeadlock($source, $destination)) {
                $this->db->delete('tredirect', ['cToUrl', 'cFromUrl'], [$source, $destination]);
            }
            $target = $this->getRedirectByTarget($source);
            if ($target !== null) {
                $this->saveExt($target->cFromUrl, $destination, false, $handling);
                $ins                = new stdClass();
                $ins->cToUrl        = Text::convertUTF8($destination);
                $ins->cAvailable    = 'y';
                $ins->paramHandling = $handling;
                $this->db->update('tredirect', 'cToUrl', $source, $ins);
            }

            $redirect = $this->find($source);
            if ($redirect === null) {
                $ins                = new stdClass();
                $ins->cFromUrl      = Text::convertUTF8($source);
                $ins->cToUrl        = Text::convertUTF8($destination);
                $ins->cAvailable    = 'y';
                $ins->paramHandling = $handling;

                $kRedirect = $this->db->insert('tredirect', $ins);
                if ($kRedirect > 0) {
                    return true;
                }
            } elseif (empty($redirect->cToUrl)
                && $this->normalize($redirect->cFromUrl) === $source
                && $this->db->update(
                    'tredirect',
                    'cFromUrl',
                    $source,
                    (object)['cToUrl' => Text::convertUTF8($destination)]
                ) > 0
            ) {
                // the redirect already exists but has an empty cToUrl => update it
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $url
     * @return bool|string
     */
    public function test(string $url): bool|string
    {
        $url = $this->normalize($url);
        if (\mb_strlen($url) === 0 || !$this->isValid($url)) {
            return false;
        }
        $redirectUrl = false;
        $parsedUrl   = \parse_url($url);
        $queryString = null;
        if (isset($parsedUrl['query'], $parsedUrl['path'])) {
            $url         = $parsedUrl['path'];
            $queryString = $parsedUrl['query'];
        }
        $foundRedirectWithQuery = false;
        if (!empty($queryString)) {
            $item = $this->find($url . '?' . $queryString);
            if ($item !== null) {
                $url                   .= '?' . $queryString;
                $foundRedirectWithQuery = true;
            } else {
                $item = $this->find($url);
                if ($item !== null) {
                    if ((int)$item->paramHandling === 0) {
                        $item = null;
                    } elseif ((int)$item->paramHandling === 1) {
                        $foundRedirectWithQuery = true;
                    }
                }
            }
        } else {
            $item = $this->find($url);
        }
        if ($item === null) {
            if (!isset($_GET['notrack']) && Shop::getSettingValue(\CONF_GLOBAL, 'redirect_save_404') === 'Y') {
                $item           = new self();
                $item->cFromUrl = $url . (!empty($queryString) ? '?' . $queryString : '');
                $item->cToUrl   = '';
                unset($item->kRedirect);
                $item->kRedirect = $this->db->insert('tredirect', $item);
            }
        } elseif (\mb_strlen($item->cToUrl) > 0) {
            $redirectUrl  = $item->cToUrl;
            $redirectUrl .= $queryString !== null && !$foundRedirectWithQuery
                ? '?' . $queryString
                : '';
        }
        $referer = $_SERVER['HTTP_REFERER'] ?? '';
        if (\mb_strlen($referer) > 0) {
            $referer = $this->normalize($referer);
        }
        $ip = Request::getRealIP();
        // Eintrag für diese IP bereits vorhanden?
        $entry = $this->db->getSingleObject(
            'SELECT *
                FROM tredirectreferer tr
                LEFT JOIN tredirect t
                    ON t.kRedirect = tr.kRedirect
                WHERE tr.cIP = :ip
                AND t.cFromUrl = :frm LIMIT 1',
            ['ip' => $ip, 'frm' => $url]
        );
        if ($entry === null || (\is_object($entry) && (int)$entry->nCount === 0)) {
            $ins               = new stdClass();
            $ins->kRedirect    = $item !== null ? $item->kRedirect : 0;
            $ins->kBesucherBot = isset($_SESSION['oBesucher']->kBesucherBot)
                ? (int)$_SESSION['oBesucher']->kBesucherBot
                : 0;
            $ins->cRefererUrl  = \is_string($referer) ? $referer : '';
            $ins->cIP          = $ip;
            $ins->dDate        = \time();
            $this->db->insert('tredirectreferer', $ins);
            // this counts only how many different referrers are hitting that url
            if ($item !== null) {
                ++$item->nCount;
                $this->db->update('tredirect', 'kRedirect', $item->kRedirect, $item);
            }
        }

        return $redirectUrl;
    }

    /**
     * @param string $url
     * @return bool
     */
    public function isValid(string $url): bool
    {
        $extension         = \pathinfo($url, \PATHINFO_EXTENSION);
        $invalidExtensions = [
            'jpg',
            'gif',
            'bmp',
            'xml',
            'ico',
            'txt',
            'png'
        ];
        if (\mb_strlen($extension) > 0) {
            $extension = \mb_convert_case($extension, \MB_CASE_LOWER);
            if (\in_array($extension, $invalidExtensions, true)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param string $cUrl
     * @return string
     */
    public function normalize(string $cUrl): string
    {
        $url = new URL();
        $url->setUrl($cUrl);

        return '/' . \trim($url->normalize(), '\\/');
    }

    /**
     * @param string $whereSQL
     * @param string $orderSQL
     * @param string $limitSQL
     * @return stdClass[]
     */
    public static function getRedirects(string $whereSQL = '', string $orderSQL = '', string $limitSQL = ''): array
    {
        $redirects = Shop::Container()->getDB()->getObjects(
            'SELECT *
                FROM tredirect' .
            ($whereSQL !== '' ? ' WHERE ' . $whereSQL : '') .
            ($orderSQL !== '' ? ' ORDER BY ' . $orderSQL : '') .
            ($limitSQL !== '' ? ' LIMIT ' . $limitSQL : '')
        );
        foreach ($redirects as $redirect) {
            $redirect->kRedirect            = (int)$redirect->kRedirect;
            $redirect->paramHandling        = (int)$redirect->paramHandling;
            $redirect->nCount               = (int)$redirect->nCount;
            $redirect->cFromUrl             = Text::filterXSS($redirect->cFromUrl);
            $redirect->oRedirectReferer_arr = self::getReferers($redirect->kRedirect);

            foreach ($redirect->oRedirectReferer_arr as $referer) {
                $referer->cRefererUrl = Text::filterXSS($referer->cRefererUrl);
            }
        }

        return $redirects;
    }

    /**
     * @param string $whereSQL
     * @return int
     */
    public static function getRedirectCount(string $whereSQL = ''): int
    {
        return (int)Shop::Container()->getDB()->getSingleObject(
            'SELECT COUNT(kRedirect) AS cnt
                FROM tredirect' .
            ($whereSQL !== '' ? ' WHERE ' . $whereSQL : '')
        )->cnt;
    }

    /**
     * @param int $kRedirect
     * @param int $limit
     * @return stdClass[]
     */
    public static function getReferers(int $kRedirect, int $limit = 100): array
    {
        return Shop::Container()->getDB()->getObjects(
            'SELECT tredirectreferer.*, tbesucherbot.cName AS cBesucherBotName,
                    tbesucherbot.cUserAgent AS cBesucherBotAgent
                FROM tredirectreferer
                LEFT JOIN tbesucherbot
                    ON tredirectreferer.kBesucherBot = tbesucherbot.kBesucherBot
                    WHERE kRedirect = :kr
                ORDER BY dDate ASC
                LIMIT :lmt',
            ['kr' => $kRedirect, 'lmt' => $limit]
        );
    }

    /**
     * @return int
     */
    public static function getTotalRedirectCount(): int
    {
        return (int)Shop::Container()->getDB()->getSingleObject(
            'SELECT COUNT(kRedirect) AS cnt
                FROM tredirect'
        )->cnt;
    }

    /**
     * @param string $url - one of
     *                    * full URL (must be inside the same shop) e.g. http://www.shop.com/path/to/page
     *                    * url path e.g. /path/to/page
     *                    * path relative to the shop root url
     * @return bool
     */
    public static function checkAvailability(string $url): bool
    {
        if (empty($url)) {
            return false;
        }
        $parsedUrl     = \parse_url($url);
        $parsedShopUrl = \parse_url(Shop::getURL() . '/');
        $fullUrlParts  = $parsedUrl;
        if (!isset($parsedUrl['host'])) {
            $fullUrlParts['scheme'] = $parsedShopUrl['scheme'];
            $fullUrlParts['host']   = $parsedShopUrl['host'];
        } elseif ($parsedUrl['host'] !== $parsedShopUrl['host']) {
            return false;
        }

        if (!isset($parsedUrl['path'])) {
            $fullUrlParts['path'] = $parsedShopUrl['path'];
        } elseif (!\str_starts_with($parsedUrl['path'], $parsedShopUrl['path'])) {
            if (isset($parsedUrl['host'])) {
                return false;
            }
            $fullUrlParts['path'] = $parsedShopUrl['path'] . \ltrim($parsedUrl['path'], '/');
        }

        if (isset($parsedUrl['query'])) {
            $fullUrlParts['query'] .= '&notrack';
        } else {
            $fullUrlParts['query'] = 'notrack';
        }
        $headers = \get_headers(URL::unparseURL($fullUrlParts));
        if ($headers !== false) {
            foreach ($headers as $header) {
                if (\preg_match('/^HTTP\\/\\d+\\.\\d+\\s+2\\d\\d\\s+.*$/', $header)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param int $kRedirect
     */
    public static function deleteRedirect(int $kRedirect): void
    {
        Shop::Container()->getDB()->delete('tredirect', 'kRedirect', $kRedirect);
        Shop::Container()->getDB()->delete('tredirectreferer', 'kRedirect', $kRedirect);
    }

    /**
     * @return int
     */
    public static function deleteUnassigned(): int
    {
        return Shop::Container()->getDB()->getAffectedRows(
            "DELETE tredirect, tredirectreferer
                FROM tredirect
                LEFT JOIN tredirectreferer
                    ON tredirect.kRedirect = tredirectreferer.kRedirect
                WHERE tredirect.cToUrl = ''"
        );
    }

    /**
     * @param array|null $hookInfos
     * @param bool       $forceExit
     * @return array
     */
    public static function urlNotFoundRedirect(array $hookInfos = null, bool $forceExit = false): array
    {
        $shopSubPath = \parse_url(Shop::getURL(), \PHP_URL_PATH) ?? '';
        $url         = \preg_replace('/^' . \preg_quote($shopSubPath, '/') . '/', '', $_SERVER['REQUEST_URI'] ?? '', 1);
        $redirect    = new self();
        $redirectUrl = $redirect->test($url);
        if ($redirectUrl !== false && $redirectUrl !== $url && '/' . $redirectUrl !== $url) {
            if (!\array_key_exists('scheme', \parse_url($redirectUrl))) {
                $redirectUrl = \str_starts_with($redirectUrl, '/')
                    ? Shop::getURL() . $redirectUrl
                    : Shop::getURL() . '/' . $redirectUrl;
            }
            \http_response_code(301);
            \header('Location: ' . $redirectUrl);
            exit;
        }
        \http_response_code(404);

        if ($forceExit || !$redirect->isValid($url)) {
            exit;
        }
        $isFileNotFound = true;
        \executeHook(\HOOK_PAGE_NOT_FOUND_PRE_INCLUDE, [
            'isFileNotFound'  => &$isFileNotFound,
            $hookInfos['key'] => &$hookInfos['value']
        ]);
        $hookInfos['isFileNotFound'] = $isFileNotFound;

        return $hookInfos;
    }

    /**
     * @param ProductFilter $productFilter
     * @param int           $count
     * @param bool          $seo
     * @deprecated since 5.2.0
     */
    public static function doMainwordRedirect(ProductFilter $productFilter, int $count, bool $seo = false): void
    {
        \trigger_error(__METHOD__ . ' is deprecated.', \E_USER_DEPRECATED);
        if ($count !== 0 || $productFilter->getFilterCount() === 0) {
            return;
        }
        $main       = [
            'getCategory'            => [
                'cKey'   => 'kKategorie',
                'cParam' => 'k'
            ],
            'getManufacturer'        => [
                'cKey'   => 'kHersteller',
                'cParam' => 'h'
            ],
            'getSearchQuery'         => [
                'cKey'   => 'kSuchanfrage',
                'cParam' => 'l'
            ],
            'getCharacteristicValue' => [
                'cKey'   => 'kMerkmalWert',
                'cParam' => 'm'
            ],
            'getSearchSpecial'       => [
                'cKey'   => 'kKey',
                'cParam' => 'q'
            ]
        ];
        $languageID = Shop::getLanguageID();
        foreach ($main as $function => $info) {
            $data = \method_exists($productFilter, $function)
                ? $productFilter->$function()
                : null;
            if ($data !== null && \method_exists($data, 'getValue') && $data->getValue() > 0) {
                /** @var FilterInterface $data */
                $url = '?' . $info['cParam'] . '=' . $data->getValue();
                if ($seo && !empty($data->getSeo($languageID))) {
                    $url = $data->getSeo($languageID);
                }
                if (\mb_strlen($url) > 0) {
                    \header('Location: ' . $url, true, 301);
                    exit();
                }
            }
        }
    }

    /**
     * @param int         $redirectedURLs
     * @param string|null $query
     * @return int
     * @deprecated since 5.2.0
     */
    public function getCount(int $redirectedURLs, ?string $query): int
    {
        \trigger_error(__METHOD__ . ' is deprecated.', \E_USER_DEPRECATED);
        $qry  = 'SELECT COUNT(*) AS nCount FROM tredirect ';
        $prep = [];
        if ($redirectedURLs === 1 || !empty($query)) {
            $qry .= 'WHERE ';
        }
        if ($redirectedURLs === 1) {
            $qry .= ' cToUrl != ""';
        }
        if (!empty($query) && $redirectedURLs === 1) {
            $qry .= ' AND ';
        }
        if (!empty($query)) {
            $qry .= 'cFromUrl LIKE :search';
            $prep = ['search' => '%' . $query . '%'];
        }

        return (int)$this->db->getSingleObject($qry, $prep)->nCount;
    }
}
