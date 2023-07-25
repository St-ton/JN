<?php

namespace JTL\Network;

use Exception;
use JTL\Cache\JTLCacheInterface;
use JTL\Helpers\Request;
use JTL\Nice;
use JTLShop\SemVer\Version;
use stdClass;
use function Functional\last;

/**
 * Class JTLApi
 * @package JTL\Network
 */
final class JTLApi
{
    public const URI = 'https://api.jtl-software.de/shop';

    public const URI_VERSION = 'https://api.jtl-shop.de';

    /**
     * JTLApi constructor.
     * @param Nice              $nice
     * @param JTLCacheInterface $cache
     */
    public function __construct(private readonly Nice $nice, private readonly JTLCacheInterface $cache)
    {
    }

    /**
     * @return stdClass|null
     */
    public function getSubscription(): ?stdClass
    {
        $cacheID = 'rs_subscriptions';
        $cached  = $this->cache->get($cacheID);
        if ($cached !== false) {
            return $cached;
        }
        $uri          = self::URI . '/check/subscription';
        $subscription = $this->call($uri, [
            'key'    => $this->nice->getAPIKey(),
            'domain' => $this->nice->getDomain(),
        ]);
        if (!\is_object($subscription)) {
            return null;
        }
        $data = (isset($subscription->kShop) && $subscription->kShop > 0)
            ? $subscription
            : null;
        $this->cache->set($cacheID, $data, [\CACHING_GROUP_CORE], 60 * 60);

        return $subscription;
    }

    /**
     * @param bool $includingDev
     *
     * @return array|null
     */
    public function getAvailableVersions(bool $includingDev = false): ?array
    {
        $cacheID = 'rs_versions';
        $cached  = $this->cache->get($cacheID);
        if ($cached !== false) {
            return $cached;
        }
        $url = self::URI_VERSION . '/versions';
        if ($includingDev === true) {
            $url .= '-dev';
        }
        $versions = $this->call($url);
        if (!\is_object($versions)) {
            return null;
        }
        $data = (array)$versions;
        $this->cache->set($cacheID, $data, [\CACHING_GROUP_CORE], 60 * 60);

        return $data;
    }

    /**
     * @return Version
     * @throws Exception
     */
    public function getLatestVersion(): Version
    {
        $shopVersion       = \APPLICATION_VERSION;
        $parsedShopVersion = Version::parse($shopVersion);
        $availableVersions = $this->getAvailableVersions();
        $newerVersions     = \array_filter((array)$availableVersions, static function ($v) use ($parsedShopVersion) {
            try {
                return Version::parse($v->reference)->greaterThan($parsedShopVersion);
            } catch (Exception) {
                return false;
            }
        });
        $version           = \count($newerVersions) > 0 ? last($newerVersions) : \end($availableVersions);

        return Version::parse($version->reference);
    }

    /**
     * @return bool
     */
    public function hasNewerVersion(): bool
    {
        try {
            return \APPLICATION_BUILD_SHA === '#DEV#'
                ? false
                : $this->getLatestVersion()->greaterThan(Version::parse(\APPLICATION_VERSION));
        } catch (Exception) {
            return false;
        }
    }

    /**
     * @param string     $uri
     * @param array|null $data
     * @return string|bool|null
     */
    private function call(string $uri, $data = null)
    {
        $content = Request::http_get_contents($uri, 10, $data);

        return empty($content) ? null : \json_decode($content);
    }
}
