<?php declare(strict_types=1);

namespace JTL;

use stdClass;

/**
 * Class Visitor
 * @package JTL
 * @deprecated since 5.2.0
 */
class Visitor
{
    /**
     * @return Customer\Visitor
     */
    private static function getInstance()
    {
        return new \JTL\Customer\Visitor(Shop::Container()->getDB(), Shop::Container()->getCache());
    }

    /**
     * @deprecated since 5.2.0
     */
    public static function generateData(): void
    {
        \trigger_error(__METHOD__ . ' is deprecated. Use JTL\Customer\Visitor class instead.', \E_USER_DEPRECATED);
        if (\TRACK_VISITORS === false) {
            return;
        }
        self::getInstance()->generateData();
    }

    /**
     * @deprecated since 5.2.0
     */
    public static function archive(): void
    {
        \trigger_error(__METHOD__ . ' is deprecated. Use JTL\Customer\Visitor class instead.', \E_USER_DEPRECATED);
        self::getInstance()->archive();
    }

    /**
     * @param string $userAgent
     * @param string $ip
     * @return stdClass|null
     * @deprecated since 5.2.0
     */
    public static function dbLookup(string $userAgent, string $ip): ?stdClass
    {
        \trigger_error(__METHOD__ . ' is deprecated. Use JTL\Customer\Visitor class instead.', \E_USER_DEPRECATED);
        return self::getInstance()->dbLookup($userAgent, $ip);
    }

    /**
     * @param stdClass $vis
     * @param int      $visitorID
     * @param string   $userAgent
     * @param int      $botID
     * @return stdClass
     * @deprecated since 5.2.0
     */
    public static function updateVisitorObject(stdClass $vis, int $visitorID, string $userAgent, int $botID)
    {
        \trigger_error(__METHOD__ . ' is deprecated. Use JTL\Customer\Visitor class instead.', \E_USER_DEPRECATED);
        return self::getInstance()->updateVisitorObject($vis, $visitorID, $userAgent, $botID);
    }

    /**
     * @param string $userAgent
     * @param int    $botID
     * @return stdClass
     * @deprecated since 5.2.0
     */
    public static function createVisitorObject(string $userAgent, int $botID): stdClass
    {
        \trigger_error(__METHOD__ . ' is deprecated. Use JTL\Customer\Visitor class instead.', \E_USER_DEPRECATED);
        return self::getInstance()->createVisitorObject($userAgent, $botID);
    }

    /**
     * @param stdClass $visitor
     * @return int
     * @deprecated since 5.2.0
     */
    public static function dbInsert(stdClass $visitor): int
    {
        \trigger_error(__METHOD__ . ' is deprecated. Use JTL\Customer\Visitor class instead.', \E_USER_DEPRECATED);
        return self::getInstance()->insert($visitor);
    }

    /**
     * @param stdClass $visitor
     * @param int      $visitorID
     * @return int
     * @deprecated since 5.2.0
     */
    public static function dbUpdate(stdClass $visitor, int $visitorID): int
    {
        \trigger_error(__METHOD__ . ' is deprecated. Use JTL\Customer\Visitor class instead.', \E_USER_DEPRECATED);
        return self::getInstance()->update($visitor, $visitorID);
    }

    /**
     * @param int $customerID
     * @return int
     * @deprecated since 5.2.0
     */
    public static function refreshCustomerOrderId(int $customerID): int
    {
        \trigger_error(__METHOD__ . ' is deprecated. Use JTL\Customer\Visitor class instead.', \E_USER_DEPRECATED);
        return self::getInstance()->refreshCustomerOrderId($customerID);
    }

    /**
     * @return string
     * @deprecated since 5.2.0
     */
    public static function getBrowser(): string
    {
        \trigger_error(__METHOD__ . ' is deprecated. Use JTL\Customer\Visitor class instead.', \E_USER_DEPRECATED);
        return self::getInstance()->getBrowser();
    }

    /**
     * @return string
     * @fomer gibReferer()
     * @deprecated since 5.2.0
     */
    public static function getReferer(): string
    {
        \trigger_error(__METHOD__ . ' is deprecated. Use JTL\Customer\Visitor class instead.', \E_USER_DEPRECATED);
        return self::getInstance()::getReferer();
    }

    /**
     * @return string
     * @deprecated since 5.2.0
     */
    public static function getBot(): string
    {
        \trigger_error(__METHOD__ . ' is deprecated. Use JTL\Customer\Visitor class instead.', \E_USER_DEPRECATED);
        return self::getInstance()->getBot();
    }

    /**
     * @param int    $visitorID
     * @param string $referer
     * @deprecated since 5.2.0
     */
    public static function analyzeReferer(int $visitorID, string $referer): void
    {
        \trigger_error(__METHOD__ . ' is deprecated and should not be used anymore.', \E_USER_DEPRECATED);
    }

    /**
     * @param string|null $referer
     * @return int
     * @deprecated since 5.2.0
     */
    public static function isSearchEngine(?string $referer): int
    {
        \trigger_error(__METHOD__ . ' is deprecated. Use JTL\Customer\Visitor class instead.', \E_USER_DEPRECATED);
        return (int)self::getInstance()->isSearchEngine($referer);
    }

    /**
     * @param string $userAgent
     * @return int
     * @deprecated since 5.2.0
     */
    public static function isSpider(string $userAgent): int
    {
        \trigger_error(__METHOD__ . ' is deprecated. Use JTL\Customer\Visitor class instead.', \E_USER_DEPRECATED);
        return self::getInstance()->getSpiderID($userAgent);
    }

    /**
     * @return array
     * @deprecated since 5.2.0
     */
    public static function getSpiders(): array
    {
        \trigger_error(__METHOD__ . ' is deprecated. Use JTL\Customer\Visitor class instead.', \E_USER_DEPRECATED);
        return self::getInstance()->getSpiders();
    }

    /**
     * @param null|string $userAgent
     * @return stdClass
     * @deprecated since 5.2.0
     */
    public static function getBrowserForUserAgent(?string $userAgent = null): stdClass
    {
        \trigger_error(__METHOD__ . ' is deprecated. Use JTL\Customer\Visitor class instead.', \E_USER_DEPRECATED);
        return self::getInstance()->getBrowserForUserAgent($userAgent);
    }
}
