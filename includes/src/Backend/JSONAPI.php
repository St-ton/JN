<?php declare(strict_types=1);

namespace JTL\Backend;

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
    private static $instance;

    /**
     * JSONAPI constructor.
     */
    private function __construct()
    {
    }

    /**
     *
     */
    private function __clone()
    {
    }

    /**
     * @return JSONAPI
     */
    public static function getInstance(): self
    {
        return self::$instance ?? self::$instance = new self();
    }

    /**
     * @param string|array|null $search
     * @param int|string        $limit
     * @param string            $keyName
     * @return string|bool
     */
    public function getSeos($search = null, $limit = 0, string $keyName = 'cSeo')
    {
        $searchIn = null;
        if (\is_string($search)) {
            $searchIn = ['cSeo'];
        } elseif (\is_array($search)) {
            $searchIn = $keyName;
        }
        $items = $this->getItems('tseo', ['cSeo', 'cKey', 'kKey'], null, $searchIn, \ltrim($search, '/'), (int)$limit);

        return $this->itemsToJson($items);
    }

    /**
     * @param string|array|null $search
     * @param int|string        $limit
     * @param string            $keyName
     * @return string|bool
     */
    public function getPages($search = null, $limit = 0, string $keyName = 'kLink')
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
     * @param string|array|null $search
     * @param int|string        $limit
     * @param string            $keyName
     * @return string|bool
     */
    public function getCategories($search = null, $limit = 0, string $keyName = 'kKategorie')
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
     * @return string|bool
     */
    public function getProducts($search = null, $limit = 0, string $keyName = 'kArtikel')
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
     * @return string|bool
     */
    public function getManufacturers($search = null, $limit = 0, string $keyName = 'kHersteller')
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
     * @return string|bool
     */
    public function getCustomers($search = null, $limit = 0, string $keyName = 'kKunde')
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
     * @return string|bool
     */
    public function getAttributes($search = null, $limit = 0, string $keyName = 'kMerkmalWert')
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
        $res = Shop::Container()->getDB()->getSingleObject(
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
            $res  = Shop::Container()->getDB()->getObjects(
                'SELECT `COLUMN_NAME` AS column_name
                    FROM information_schema.COLUMNS
                    WHERE `TABLE_SCHEMA` = :sma
                        AND `TABLE_NAME` = :tn',
                [
                    'sma' => \DB_NAME,
                    'tn' => $table
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
        string $table,
        array $columns,
        ?string $addCacheTag = null,
        $searchIn = null,
        $searchFor = null,
        int $limit = 0
    ): array {
        if ($this->validateTableName($table) === false || $this->validateColumnNames($table, $columns) === false) {
            return [];
        }
        $db        = Shop::Container()->getDB();
        $cacheId   = 'jsonapi_' . $table . '_' . $limit . '_';
        $cacheId  .= \md5(\serialize($columns) . \serialize($searchIn) . \serialize($searchFor));
        $cacheTags = [\CACHING_GROUP_CORE];

        if ($addCacheTag !== null) {
            $cacheTags[] = $addCacheTag;
        }

        if (($data = Shop::Container()->getCache()->get($cacheId)) !== false) {
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
                ? $db->getObjects($qry, ['val' => '%' . $searchFor . '%'])
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
                ? $db->getObjects($qry, $bindValues)
                : [];
        } elseif ($searchIn === null && $searchFor === null) {
            // select all
            $result = $db->getObjects(
                'SELECT ' . \implode(',', $columns) . '
                    FROM ' . $table . '
                    ' . ($limit > 0 ? 'LIMIT ' . $limit : '')
            );
        } else {
            // invalid arguments
            $result = [];
        }

        Shop::Container()->getCache()->set($cacheId, $result, $cacheTags);

        return $result;
    }

    /**
     * @param array|mixed $items
     * @return false|string
     * @throws \JsonException
     */
    public function itemsToJson($items): string
    {
        return \json_encode($items, \JSON_THROW_ON_ERROR);
    }
}
