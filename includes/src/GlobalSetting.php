<?php

namespace JTL;

use Illuminate\Support\Collection;
use JTL\Cache\JTLCacheInterface;

/**
 * Class GlobalSetting
 * @package JTL
 * @deprecated since 5.3.0
 */
final class GlobalSetting
{
    /**
     * @var self
     */
    private static $instance;

    /**
     * @var JTLCacheInterface
     */
    private $cache;

    /**
     * @var Collection
     */
    private $settings;

    public const CHILD_ITEM_BULK_PRICING = 'GENERAL_CHILD_ITEM_BULK_PRICING';

    private const CACHE_ID = 'setting_global';

    /**
     *
     */
    private function __construct()
    {
        \trigger_error(__CLASS__ . ' is deprecated and should not be used anymore.', \E_USER_DEPRECATED);
        self::$instance = $this;

        $this->cache = Shop::Container()->getCache();
    }

    /**
     *
     */
    private function __clone()
    {
    }

    /**
     * @return self
     */
    public static function getInstance(): self
    {
        return self::$instance ?? new self();
    }

    /**
     * @return Collection
     */
    private function loadSettings(): Collection
    {
        //todo: implement this method if global settings are supported in dbeS
        return new Collection();
    }

    /**
     * @return Collection
     */
    private function getSettings(): Collection
    {
        if ($this->settings === null || $this->settings->isEmpty()) {
            $this->settings = $this->cache->get(
                self::CACHE_ID,
                function ($cache, $id, &$content, &$tags): bool {
                    $content = $this->loadSettings();
                    $tags    = [\CACHING_GROUP_OPTION];

                    return true;
                }
            );
        }

        return $this->settings;
    }

    /**
     * @param string     $valueName
     * @param mixed|null $default
     * @return mixed
     */
    public function getValue(string $valueName, $default = null)
    {
        $value = $this->getSettings()->get($valueName, $default);

        return match (\gettype($default)) {
            'boolean' => (bool)$value,
            'integer' => (int)$value,
            'double'  => (float)$value,
            'string'  => (string)$value,
            default   => $value,
        };
    }
}
