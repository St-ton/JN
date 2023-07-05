<?php declare(strict_types=1);

namespace JTL\Backend;

use JsonException;
use JTL\Cache\JTLCacheInterface;
use JTL\DB\DbInterface;
use JTL\Language\LanguageHelper;
use JTL\Router\Router;
use JTL\Shop;

/**
 * Class JSONAPI
 * @package JTL\Backend
 */
class JSONAPI
{
    /**
     * @var JSONAPI|null
     */
    private static ?self $instance = null;

    /**
     * @param DbInterface       $db
     * @param JTLCacheInterface $cache
     */
    private function __construct(private readonly DbInterface $db, private readonly JTLCacheInterface $cache)
    {
        self::$instance = $this;
    }

    /**
     *
     */
    private function __clone()
    {
    }

    /**
     * @param DbInterface|null       $db
     * @param JTLCacheInterface|null $cache
     * @return static
     */
    public static function getInstance(?DbInterface $db = null, ?JTLCacheInterface $cache = null): self
    {
        return self::$instance ?? new self($db ?? Shop::Container()->getDB(), $cache ?? Shop::Container()->getCache());
    }

    /**
     * @param string|array|null $search
     * @param int|string        $limit
     * @param string            $keyName
     * @return string
     */
    public function getSeos($search = null, $limit = 0, string $keyName = 'cSeo'): string
    {
        $searchIn = null;
        if (\is_string($search)) {
            $searchIn = ['cSeo'];
        } elseif (\is_array($search)) {
            $searchIn = $keyName;
        }

        return $this->itemsToJson($this->getItems(
            'tseo',
            ['cSeo', 'cKey', 'kKey', 'kSprache'],
            null,
            $searchIn,
            \ltrim($search, '/'),
            (int)$limit
        ));
    }

    /**
     * @param string|array|null $search
     * @param int|string        $limit
     * @param string            $keyName
     * @return string
     */
    public function getPages($search = null, $limit = 0, string $keyName = 'kLink'): string
    {
        $searchIn = null;
        if (\is_string($search)) {
            $searchIn = ['cName'];
        } elseif (\is_array($search)) {
            $searchIn = $keyName;
        }

        return $this->itemsToJson($this->getItems('tlink', ['kLink', 'cName'], null, $searchIn, $search, (int)$limit));
    }

    /**
     * @param string $search
     * @param int    $limit
     * @param string $keyName
     * @param int    $linkGroupID
     * @return string
     * @throws JsonException
     * @since 5.2.3
     */
    public function getPagesByLinkGroup(
        string $search = '',
        int    $limit = 50,
        int    $linkGroupID = 0,
        string $keyName = 'kLink'
    ): string {
        if ($linkGroupID === 0) {
            return $this->getPages($search, $limit, $keyName);
        }

        return $this->itemsToJson(
            $this->db->getObjects(
                "SELECT kLink, cName
                FROM tlink
                    JOIN tlinkgroupassociations
                        ON tlink.kLink = tlinkgroupassociations.linkID AND tlinkgroupassociations.linkGroupID = :GroupID
                WHERE tlink.cName LIKE '%" . $search . "%'
                LIMIT " . $limit,
                ['GroupID' => $linkGroupID]
            ) ?? []
        );
    }

    /**
     * @param string|array|null $search
     * @param int|string        $limit
     * @param string            $keyName
     * @return string
     */
    public function getCategories($search = null, $limit = 0, string $keyName = 'kKategorie'): string
    {
        $searchIn = null;
        if (\is_string($search)) {
            $searchIn = ['tkategorie.cName'];
        } elseif (\is_array($search)) {
            $searchIn = $keyName;
        }

        return $this->itemsToJson($this->getItems(
            'tkategorie',
            ['tkategorie.kKategorie', 'tkategorie.cName'],
            \CACHING_GROUP_CATEGORY,
            $searchIn,
            $search,
            (int)$limit
        ));
    }

    /**
     * @param string|array|null $search
     * @param int|string        $limit
     * @param string            $keyName
     * @return string
     */
    public function getProducts($search = null, $limit = 0, string $keyName = 'kArtikel'): string
    {
        $searchIn = null;
        if (\is_string($search)) {
            $searchIn = ['cName', 'cArtNr'];
        } elseif (\is_array($search)) {
            $searchIn = $keyName;
        }

        return $this->itemsToJson(
            $this->getItems(
                'tartikel',
                ['kArtikel', 'cName', 'cArtNr'],
                \CACHING_GROUP_ARTICLE,
                $searchIn,
                $search,
                (int)$limit
            )
        );
    }

    /**
     * @param string|array|null $search
     * @param int|string        $limit
     * @param string            $keyName
     * @return string
     */
    public function getManufacturers($search = null, $limit = 0, string $keyName = 'kHersteller'): string
    {
        $searchIn = null;
        if (\is_string($search)) {
            $searchIn = ['cName'];
        } elseif (\is_array($search)) {
            $searchIn = $keyName;
        }

        return $this->itemsToJson($this->getItems(
            'thersteller',
            ['kHersteller', 'cName'],
            \CACHING_GROUP_MANUFACTURER,
            $searchIn,
            $search,
            (int)$limit
        ));
    }

    /**
     * @param string|array|null $search
     * @param int|string        $limit
     * @param string            $keyName
     * @return string
     */
    public function getCustomers($search = null, $limit = 0, string $keyName = 'kKunde'): string
    {
        $searchIn = null;
        if (\is_string($search)) {
            $searchIn = ['cVorname', 'cMail', 'cOrt', 'cPLZ'];
        } elseif (\is_array($search)) {
            $searchIn = $keyName;
        }

        $items         = $this->getItems(
            'tkunde',
            ['kKunde', 'cVorname', 'cNachname', 'cStrasse', 'cHausnummer', 'cPLZ', 'cOrt', 'cMail'],
            null,
            $searchIn,
            $search,
            (int)$limit
        );
        $cryptoService = Shop::Container()->getCryptoService();
        foreach ($items as $item) {
            $item->cNachname = \trim($cryptoService->decryptXTEA($item->cNachname));
            $item->cStrasse  = \trim($cryptoService->decryptXTEA($item->cStrasse));
        }

        return $this->itemsToJson($items);
    }

    /**
     * @param string|array|null $search
     * @param int|string        $limit
     * @param string            $keyName
     * @return string
     */
    public function getAttributes($search = null, $limit = 0, string $keyName = 'kMerkmalWert'): string
    {
        $searchIn = null;
        if (\is_string($search)) {
            $searchIn = ['cWert'];
        } elseif (\is_array($search)) {
            $searchIn = $keyName;
        }

        return $this->itemsToJson($this->getItems(
            'tmerkmalwertsprache',
            ['kMerkmalWert', 'cWert'],
            \CACHING_GROUP_ARTICLE,
            $searchIn,
            $search,
            (int)$limit
        ));
    }

    /**
     * @param string $table
     * @return bool
     */
    private function validateTableName(string $table): bool
    {
        $res = $this->db->getSingleObject(
            'SELECT `TABLE_NAME` AS table_name
                FROM information_schema.TABLES
                WHERE `TABLE_SCHEMA` = :sma
                    AND `TABLE_NAME` = :tn',
            [
                'sma' => \DB_NAME,
                'tn'  => $table
            ]
        );

        return $res !== null && $res->table_name === $table;
    }

    /**
     * @param string $table
     * @param array  $columns
     * @return bool
     */
    private function validateColumnNames(string $table, array $columns): bool
    {
        static $tableRows = null;
        if (isset($tableRows[$table])) {
            $rows = $tableRows[$table];
        } else {
            $res  = $this->db->getObjects(
                'SELECT `COLUMN_NAME` AS column_name
                    FROM information_schema.COLUMNS
                    WHERE `TABLE_SCHEMA` = :sma
                        AND `TABLE_NAME` = :tn',
                [
                    'sma' => \DB_NAME,
                    'tn'  => $table
                ]
            );
            $rows = [];
            foreach ($res as $item) {
                $rows[] = $item->column_name;
                $rows[] = $table . '.' . $item->column_name;
            }

            $tableRows[$table] = $rows;
        }

        return \collect($columns)->every(static function ($e) use ($rows): bool {
            return \in_array($e, $rows, true);
        });
    }

    /**
     * @param string               $table
     * @param string[]             $columns
     * @param string|null          $addCacheTag
     * @param string[]|string|null $searchIn
     * @param string|string[]|null $searchFor
     * @param int                  $limit
     * @return array
     * @todo: add URL hints for new URL scheme (like cSeo:/de/products/myproduct instead of cSeo:myproduct)
     */
    public function getItems(
        string  $table,
        array   $columns,
        ?string $addCacheTag = null,
        $searchIn = null,
        $searchFor = null,
        int     $limit = 0
    ): array {
        if ($this->validateTableName($table) === false || $this->validateColumnNames($table, $columns) === false) {
            return [];
        }
        $cacheId   = 'jsonapi_' . $table . '_' . $limit . '_';
        $cacheId  .= \md5(\serialize($columns) . \serialize($searchIn) . \serialize($searchFor));
        $cacheTags = [\CACHING_GROUP_CORE];
        if ($addCacheTag !== null) {
            $cacheTags[] = $addCacheTag;
        }
        if (($data = $this->cache->get($cacheId)) !== false) {
            return $data;
        }
        if (\is_array($searchIn) && \is_string($searchFor)) {
            // full text search
            $conditions  = [];
            $colsToCheck = [];
            foreach ($searchIn as $column) {
                $colsToCheck[] = $column;
                $conditions[]  = $column . ' LIKE :val';
            }

            if ($table === 'tkategorie') {
                $qry = 'SELECT ' . \implode(',', $columns) . ', t2.cName AS parentName
                    FROM tkategorie 
                        LEFT JOIN tkategorie AS t2 
                        ON tkategorie.kOberKategorie = t2.kKategorie
                        WHERE ' . \implode(' OR ', $conditions) . ($limit > 0 ? ' LIMIT ' . $limit : '');
            } else {
                $qry = 'SELECT ' . \implode(',', $columns) . '
                        FROM ' . $table . '
                        WHERE ' . \implode(' OR ', $conditions) . ($limit > 0 ? ' LIMIT ' . $limit : '');
            }

            $result = $this->validateColumnNames($table, $colsToCheck)
                ? $this->db->getObjects($qry, ['val' => '%' . $searchFor . '%'])
                : [];
        } elseif (\is_string($searchIn) && \is_array($searchFor)) {
            // key array select
            $bindValues = [];
            $count      = 1;
            foreach ($searchFor as $t) {
                $bindValues[$count] = $t;
                ++$count;
            }
            $qry    = 'SELECT ' . \implode(',', $columns) . '
                    FROM ' . $table . '
                    WHERE ' . $searchIn . ' IN (' . \implode(',', \array_fill(0, $count - 1, '?')) . ')
                    ' . ($limit > 0 ? 'LIMIT ' . $limit : '');
            $result = $this->validateColumnNames($table, [$searchIn])
                ? $this->db->getObjects($qry, $bindValues)
                : [];
        } elseif ($searchIn === null && $searchFor === null) {
            // select all
            $result = $this->db->getObjects(
                'SELECT ' . \implode(',', $columns) . '
                    FROM ' . $table . '
                    ' . ($limit > 0 ? 'LIMIT ' . $limit : '')
            );
        } else {
            // invalid arguments
            $result = [];
        }
        if ($table === 'tseo') {
            $this->setRealSeo($result);
        }

        $this->cache->set($cacheId, $result, $cacheTags);

        return $result;
    }

    /**
     * @param array $items
     * @return void
     */
    private function setRealSeo(array $items): void
    {
        $router    = Shop::getRouter();
        $languages = LanguageHelper::getAllLanguages();
        foreach ($items as $item) {
            $locale = 'de';
            $langID = (int)($item->kSprache ?? 0);
            if (!isset($item->cSeo, $item->cKey, $item->kKey) || $langID === 0) {
                continue;
            }
            foreach ($languages as $language) {
                if ($language->getId() === $langID) {
                    $locale = $language->getIso639();
                }
            }
            $path = $router->getPathByType(
                $this->getTypeByKey($item->cKey),
                ['lang' => $locale, 'id' => (int)$item->kKey, 'name' => $item->cSeo]
            );
            if (!empty($path)) {
                $item->cSeo = \ltrim($path, '/');
            }
        }
    }

    /**
     * @param string $key
     * @return string
     */
    private function getTypeByKey(string $key): string
    {
        return match ($key) {
            'kKategorie'   => Router::TYPE_CATEGORY,
            'kMerkmalWert' => Router::TYPE_CHARACTERISTIC_VALUE,
            'kNews'        => Router::TYPE_NEWS,
            'kHersteller'  => Router::TYPE_MANUFACTURER,
            'kArtikel'     => Router::TYPE_PRODUCT,
            default        => '',
        };
    }

    /**
     * @param array|mixed $items
     * @return string
     * @throws JsonException
     */
    public function itemsToJson($items): string
    {
        return \json_encode($items, \JSON_THROW_ON_ERROR);
    }
}
