<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class JSONAPI
 */
class JSONAPI
{
    /**
     * @var JSONAPI
     */
    private static $instance;

    /**
     * ctor
     */
    private function __construct() { }

    /**
     * copy-ctor
     */
    private function __clone() { }

    /**
     * @return JSONAPI
     */
    public static function getInstance()
    {
        return self::$instance === null ? (self::$instance = new self()) : self::$instance;
    }

    /**
     * @param string|array|null $search
     * @param int               $limit
     * @param string            $keyName
     * @return mixed|string
     */
    public function getSeos($search = null, $limit = 0, $keyName = 'cSeo')
    {
        $searchIn = null;
        if (is_string($search)) {
            $searchIn = ['cSeo'];
        } elseif (is_array($search)) {
            $searchIn = $keyName;
        }
        $items = $this->getItems('tseo', ['cSeo', 'cKey', 'kKey'], null, $searchIn, $search, $limit);

        return $this->itemsToJson($items);
    }

    /**
     * @param string|array|null $search
     * @param int $limit
     * @param string $keyName
     * @return string
     */
    public function getPages($search = null, $limit = 0, $keyName = 'kLink')
    {
        $searchIn = null;
        if (is_string($search)) {
            $searchIn = ['cName'];
        } elseif (is_array($search)) {
            $searchIn = $keyName;
        }

        return $this->itemsToJson($this->getItems('tlink', ['kLink', 'cName'], null, $searchIn, $search, $limit));
    }

    /**
     * @param string|array|null $search
     * @param int $limit
     * @param string $keyName
     * @return string
     */
    public function getCategories($search = null, $limit = 0, $keyName = 'kKategorie')
    {
        $searchIn = null;
        if (is_string($search)) {
            $searchIn = ['cName'];
        } elseif (is_array($search)) {
            $searchIn = $keyName;
        }

        return $this->itemsToJson($this->getItems(
            'tkategorie', ['kKategorie', 'cName'], CACHING_GROUP_CATEGORY, $searchIn, $search, $limit
        ));
    }

    /**
     * @param string|array|null $search
     * @param int $limit
     * @param string $keyName
     * @return string
     */
    public function getProducts($search = null, $limit = 0, $keyName = 'kArtikel')
    {
        $searchIn = null;
        if (is_string($search)) {
            $searchIn = ['cName', 'cArtNr'];
        } elseif (is_array($search)) {
            $searchIn = $keyName;
        }

        return $this->itemsToJson(
            $this->getItems(
                'tartikel', ['kArtikel', 'cName', 'cArtNr'], CACHING_GROUP_ARTICLE, $searchIn, $search, $limit
            )
        );
    }

    /**
     * @param string|array|null $search
     * @param int $limit
     * @param string $keyName
     * @return string
     */
    public function getManufacturers($search = null, $limit = 0, $keyName = 'kHersteller')
    {
        $searchIn = null;
        if (is_string($search)) {
            $searchIn = ['cName'];
        } elseif (is_array($search)) {
            $searchIn = $keyName;
        }

        return $this->itemsToJson($this->getItems(
            'thersteller', ['kHersteller', 'cName'], CACHING_GROUP_MANUFACTURER, $searchIn, $search, $limit
        ));
    }

    /**
     * @param string|array|null $search
     * @param int $limit
     * @param string $keyName
     * @return string
     */
    public function getCustomers($search = null, $limit = 0, $keyName = 'kKunde')
    {
        $searchIn = null;
        if (is_string($search)) {
            $searchIn = ['cVorname', 'cMail', 'cOrt', 'cPLZ'];
        } elseif (is_array($search)) {
            $searchIn = $keyName;
        }

        $items = $this->getItems(
            'tkunde', ['kKunde', 'cVorname', 'cNachname', 'cStrasse', 'cHausnummer', 'cPLZ', 'cOrt', 'cMail'],
            null, $searchIn, $search, $limit
        );

        foreach ($items as $item) {
            $item->cNachname = trim(entschluesselXTEA($item->cNachname));
            $item->cStrasse  = trim(entschluesselXTEA($item->cStrasse));
        }

        return $this->itemsToJson($items);
    }

    /**
     * @param string|array|null $search
     * @param int $limit
     * @param string $keyName
     * @return string
     */
    public function getTags($search = null, $limit = 0, $keyName = 'kTag')
    {
        $searchIn = null;
        if (is_string($search)) {
            $searchIn = ['cName'];
        } elseif (is_array($search)) {
            $searchIn = $keyName;
        }

        return $this->itemsToJson($this->getItems(
            'ttag', ['kTag', 'cName'], CACHING_GROUP_ARTICLE, $searchIn, $search, $limit
        ));
    }

    /**
     * @param string|array|null $search
     * @param int $limit
     * @param string $keyName
     * @return string
     */
    public function getAttributes($search = null, $limit = 0, $keyName = 'kMerkmalWert')
    {
        $searchIn = null;
        if (is_string($search)) {
            $searchIn = ['cWert'];
        } elseif (is_array($search)) {
            $searchIn = $keyName;
        }

        return $this->itemsToJson($this->getItems(
            'tmerkmalwertsprache', ['kMerkmalWert', 'cWert'], CACHING_GROUP_ARTICLE, $searchIn, $search, $limit
        ));
    }

    /**
     * @param string $table
     * @param string[] $columns
     * @param string $addCacheTag
     * @param string[]|string|null $searchIn
     * @param string|string[]|null $searchFor
     * @param int $limit
     * @return array
     */
    public function getItems($table, $columns, $addCacheTag = null, $searchIn = null, $searchFor = null, $limit = 0)
    {
        $table     = Shop::Container()->getDB()->escape($table);
        $limit     = (int)$limit;
        $cacheId   = 'jsonapi_' . $table . '_' . $limit . '_';
        $cacheId  .= md5(serialize($columns) . serialize($searchIn) . serialize($searchFor));
        $cacheTags = [CACHING_GROUP_CORE];

        if ($addCacheTag !== null) {
            $cacheTags[] = $addCacheTag;
        }

        if (($data = Shop::Cache()->get($cacheId)) !== false) {
            return $data;
        }

        foreach ($columns as $i => $column) {
            $columns[$i] = Shop::Container()->getDB()->escape($column);
        }

        if (is_array($searchIn) && is_string($searchFor)) {
            // full text search
            $searchFor  = Shop::Container()->getDB()->escape($searchFor);
            $conditions = [];

            foreach ($searchIn as $i => $column) {
                $conditions[] = Shop::Container()->getDB()->escape($column) . " LIKE '%" . $searchFor . "%'";
            }

            $result = Shop::Container()->getDB()->query(
                "SELECT " . implode(',', $columns) . "
                    FROM " . $table . "
                    WHERE " . implode(' OR ', $conditions) . "
                    " . ($limit > 0 ? "LIMIT " . $limit : ""),
                2
            );
        } elseif (is_string($searchIn) && is_array($searchFor)) {
            // key array select
            $searchIn = Shop::Container()->getDB()->escape($searchIn);

            foreach ($searchFor as $i => $key) {
                $searchFor[$i] = "'" . Shop::Container()->getDB()->escape($key) . "'";
            }

            $result = Shop::Container()->getDB()->query(
                "SELECT " . implode(',', $columns) . "
                    FROM " . $table . "
                    WHERE " . $searchIn . " IN (" . implode(',', $searchFor) . ")
                    " . ($limit > 0 ? "LIMIT " . $limit : ""),
                2
            );
        } elseif ($searchIn === null && $searchFor === null) {
            // select all
            $result = Shop::Container()->getDB()->query(
                "SELECT " . implode(',', $columns) . "
                    FROM " . $table . "
                    " . ($limit > 0 ? "LIMIT " . $limit : ""),
                2
            );
        } else {
            // invalid arguments
            $result = [];
        }

        if (!is_array($result)) {
            $result = [];
        }

        Shop::Cache()->set($cacheId, $result, $cacheTags);

        return $result;
    }

    /**
     * @param array $items
     * @return string
     */
    public function itemsToJson($items)
    {
        return json_encode($items);
    }
}
