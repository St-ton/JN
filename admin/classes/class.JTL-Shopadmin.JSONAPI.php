<?php

/**
 * Class JSONAPI
 */
class JSONAPI
{
    /**
     * @param string $name
     * @param array  $arguments
     * @return mixed|string
     */
    public function __call($name, $arguments)
    {
        $limit   = '';
        $cacheID = 'jsonapi_' . $name;
        if (count($arguments) > 0) {
            $limit = ' LIMIT ' . intval($arguments[0]);
            $cacheID .= '_' . intval($arguments[0]);
        }

        if (($data = Shop::Cache()->get($cacheID)) !== false) {
            return $data;
        }

        $data      = array();
        $cacheTags = array(CACHING_GROUP_CORE);
        switch ($name) {
            case 'getPages':
                $data = Shop::DB()->query("SELECT kLink AS id, cName AS name FROM tlink" . $limit, 2);
                break;
            case 'getCategories':
                $data        = Shop::DB()->query("SELECT kKategorie AS id, cName AS name FROM tkategorie" . $limit, 2);
                $cacheTags[] = CACHING_GROUP_CATEGORY;
                break;
            case 'getProducts':
                $data        = Shop::DB()->query("SELECT kArtikel AS id, cName AS name FROM tartikel" . $limit, 2);
                $cacheTags[] = CACHING_GROUP_ARTICLE;
                break;
            case 'getManufacturers':
                $data        = Shop::DB()->query("SELECT kHersteller AS id, cName AS name FROM thersteller" . $limit, 2);
                $cacheTags[] = CACHING_GROUP_MANUFACTURER;
                break;
            default:
                break;
        }
        foreach ($data as $_object) {
            foreach (get_object_vars($_object) as $_k => $_v) {
                $_object->$_k = utf8_encode($_v);
            }
        }
        $data = json_encode($data);
        Shop::Cache()->set($cacheID, $data, $cacheTags);

        return $data;
    }
}
