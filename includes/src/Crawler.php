<?php

namespace JTL;

use JTL\DB\ReturnType;
use JTL\Services\JTL\Validation\Rules\DateTime;

/**
 * Class Crawler
 * @package JTL
 * @since 5.0.0
 */
class Crawler
{
    /**
     * @var int
     */
    public $kBesucherBot;

    /**
     * @var string
     */
    public $cName;

    /**
     * @var string
     */
    public $cUserAgent;

    /**
     * @var string
     */
    public $cBeschreibung;

    /**
     * @var string
     */
    public $cLink;

    /**
     * @var DateTime
     */
    public $dZeit;

    /**
     * @var $id int
     * @return object
     */
    public static function get(int $id): object
    {
        return Shop::Container()->getDB()->queryPrepared(
            'SELECT * FROM tbesucherbot WHERE kBesucherBot = :id LIMIT 1',
            ['id' => $id],
            ReturnType::SINGLE_OBJECT
        );
    }

    /**
     * @var $id int
     * @return object|bool
     */
    public static function getByUserAgent(string $userAgent)
    {
        $crawler = self::getAll();
        $result  = array_filter($crawler, static function ($item) use ($userAgent) {
            return mb_stripos($item->cUserAgent, $userAgent) !== false;
        }, ARRAY_FILTER_USE_BOTH);
        $result  = array_values($result);
        return count($result) > 0 ? (object)$result[0] : false;
    }

    /**
     * @return bool
     */
    public static function set($item): bool
    {
        Shop::Container()->getCache()->flush('crawler');
        if (isset($item['kBesucherBot'], $item['cUserAgent'], $item['cBeschreibung']) && $item['kBesucherBot'] > 0) {
            return Shop::Container()->getDB()->update(
                'tbesucherbot',
                'kBesucherBot',
                $item['kBesucherBot'],
                (object)$item
            );
        } elseif ($item['kBesucherBot'] === 0) {
            return Shop::Container()->getDB()->insert(
                'tbesucherbot',
                (object)$item
            );
        }
        return -1;
    }

    /**
     * @return array|mixed
     */
    public static function getAll(): array
    {
        $cacheID = 'crawler';
        if (($crawler = Shop::Container()->getCache()->get($cacheID)) === false) {
            $crawler = Shop::Container()->getDB()->query(
                'SELECT * FROM tbesucherbot ORDER BY kBesucherBot DESC',
                ReturnType::ARRAY_OF_OBJECTS
            );
            Shop::Container()->getCache()->set($cacheID, $crawler, [\CACHING_GROUP_CORE]);
        }
        return $crawler;
    }

    /**
     * @return bool
     * @var $ids array
     */
    public static function deleteBatch(array $ids): bool
    {
        $db       = Shop::Container()->getDB();
        $ids      = array_map(static function ($id) {
            return (int)$id;
        }, $ids);
        $where_in = '('.implode(',', $ids).')';

        $db->executeQuery(
            'DELETE FROM tbesucherbot WHERE kBesucherBot IN '.$where_in.' ',
            ReturnType::DEFAULT
        );
        Shop::Container()->getCache()->flush('crawler');
        return true;
    }
}
