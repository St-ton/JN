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
     * @var self
     */
    private static $instance = null;

    /**
     * ctor
     */
    private function __construct() { }

    /**
     * copy-ctor
     */
    private function __clone() { }

    /**
     * @return self
     */
    public static function getInstance()
    {
        return self::$instance === null ? (self::$instance = new self()) : self::$instance;
    }

    /**
     * @param $limit
     * @return mixed|string
     */
    public function getPages($limit = 0, $search = '')
    {
        return $this->getJson('getPages', $limit, $search);
    }

    /**
     * @param $limit
     * @return mixed|string
     */
    public function getCategories($limit = 0, $search = '')
    {
        return $this->getJson('getCategories', $limit, $search);
    }

    /**
     * @param $limit
     * @return mixed|string
     */
    public function getProducts($limit = 0, $search = '')
    {
        return $this->getJson('getProducts', $limit, $search);
    }

    /**
     * @param $limit
     * @return mixed|string
     */
    public function getManufacturers($limit = 0, $search = '')
    {
        return $this->getJson('getManufacturers', $limit, $search);
    }

    /**
     * @param $limit
     * @return mixed|string
     */
    public function getCustomers($limit = 0, $search = '')
    {
        return $this->getJson('getCustomers', $limit, $search);
    }

    /**
     * @param $name
     * @param int $limit
     * @param string|array $search
     * @return string
     */
    private function getJson($name, $limit = 0, $search = '')
    {
        $limit = (int)$limit;
        $keys  = null;

        if (is_string($search)) {
            $search = Shop::DB()->escape($search);
        } elseif (is_array($search)) {
            $keys = array_map(function ($key) {
                return (int)$key;
            }, $search);
            $search = serialize($keys);
        }

        $cacheID   = 'jsonapi_' . $name . '_' . $limit . '_' . md5($search);
        $cacheTags = [CACHING_GROUP_CORE];

        if (($data = Shop::Cache()->get($cacheID)) !== false) {
            return $data;
        }

        switch ($name) {
            case 'getPages':
                $data = Shop::DB()->query(
                    "SELECT kLink AS id, cName AS name
                        FROM tlink
                        WHERE cName LIKE '%" . $search . "%'
                        " . ($limit > 0 ? "LIMIT " . $limit : ""),
                    2
                );
                break;
            case 'getCategories':
                $data        = Shop::DB()->query(
                    "SELECT kKategorie AS id, cName AS name
                        FROM tkategorie
                        WHERE cName LIKE '%" . $search . "%'
                        " . ($limit > 0 ? "LIMIT " . $limit : ""),
                    2
                );
                $cacheTags[] = CACHING_GROUP_CATEGORY;
                break;
            case 'getProducts':
                $data        = Shop::DB()->query(
                    "SELECT kArtikel AS id, cName AS name
                        FROM tartikel
                        WHERE cName LIKE '%" . $search . "%'
                        " . ($limit > 0 ? "LIMIT " . $limit : ""),
                    2
                );
                $cacheTags[] = CACHING_GROUP_ARTICLE;
                break;
            case 'getManufacturers':
                $data        = Shop::DB()->query(
                    "SELECT kHersteller AS id, cName AS name
                        FROM thersteller
                        WHERE cName LIKE '%" . $search . "%'
                        " . ($limit > 0 ? "LIMIT " . $limit : ""),
                    2
                );
                $cacheTags[] = CACHING_GROUP_MANUFACTURER;
                break;
            case 'getCustomers':
                $data = Shop::DB()->query(
                    "SELECT kKunde AS id, cVorname AS first, cNachname AS last, cMail AS mail, cStrasse AS street,
                            cHausnummer AS housenr, cPLZ AS postcode, cOrt AS city
                        FROM tkunde
                        WHERE " . ($keys !== null
                            ? "kKunde IN (" . implode(',', $keys) . ")"
                            : "cVorname LIKE '%" . $search . "%'
                                OR cMail LIKE '%" . $search . "%'
                                OR cOrt LIKE '%" . $search . "%'
                                OR cPLZ LIKE '%" . $search . "%'
                            ") .
                        ($limit > 0 ? " LIMIT " . $limit : ""),
                    2
                );
                foreach ($data as $item) {
                    $item->last   = trim(entschluesselXTEA($item->last));
                    $item->street = trim(entschluesselXTEA($item->street));
                }
                $cacheTags[] = CACHING_GROUP_MANUFACTURER;
                break;
            default:
                $data = [];
                break;
        }

        // deep UTF-8 encode
        foreach ($data as $item) {
            foreach (get_object_vars($item) as $key => $val) {
                $item->$key = utf8_encode($val);
            }
        }

        $data = json_encode($data);
        Shop::Cache()->set($cacheID, $data, $cacheTags);

        return $data;
    }
}
