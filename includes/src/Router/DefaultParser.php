<?php declare(strict_types=1);

namespace JTL\Router;

use JTL\DB\DbInterface;
use JTL\Shop;
use stdClass;

/**
 * Class DefaultParser
 * @package JTL\Router
 */
class DefaultParser
{
    /**
     * @var array
     */
    private array $params = [];

    /**
     * @param DbInterface $db
     * @param State       $state
     */
    public function __construct(protected DbInterface $db, protected State $state)
    {
    }

    /**
     * @param array $hierarchy
     * @return stdClass|null
     */
    protected function validateCategoryHierarchy(array $hierarchy): ?stdClass
    {
        $seo   = null;
        $left  = [];
        $right = [];
        foreach ($hierarchy as $item) {
            $seo = $this->db->getSingleObject(
                'SELECT tseo.cSeo AS slug, tkategorie.lft, tkategorie.rght 
                    FROM tseo
                    JOIN tkategorie
                        ON tseo.cKey = :keyname
                        AND tseo.kKey = tkategorie.kKategorie
                    WHERE tseo.cSeo = :slg',
                ['slg' => $item, 'keyname' => 'kKategorie']
            );
            if ($seo === null) {
                break;
            }
            $left[]  = (int)$seo->lft;
            $right[] = (int)$seo->rght;
        }
        if ($seo === null) {
            return null;
        }
        $test = \array_values($left);
        \sort($test, \SORT_NUMERIC);
        if ($test !== $left) {
            return null;
        }
        $test = \array_values($right);
        \sort($test, \SORT_NUMERIC);
        $test = \array_reverse($test);
        if ($test !== $right) {
            return null;
        }

        return $seo;
    }

    /**
     * @return array
     */
    public function getParams(): array
    {
        return $this->params;
    }

    /**
     * @param array $params
     */
    public function setParams(array $params): void
    {
        $this->params = $params;
    }

    /**
     * @param string      $slug
     * @param array|null  $replacements
     * @param string|null $type
     * @return string
     */
    public function parse(string $slug, ?array $replacements = null, ?string $type = null): string
    {
        $page = 0;
        $slug = $this->checkCustomFilters($slug);
        // change Opera Fix
        if (\mb_substr($slug, \mb_strlen($slug) - 1, 1) === '?') {
            $slug = \mb_substr($slug, 0, -1);
        }
        $match = \preg_match('/[^_](' . \SEP_SEITE . '(\d+))/', $slug, $matches, \PREG_OFFSET_CAPTURE);
        if ($match === 1) {
            $page = (int)$matches[2][0];
            $slug = \mb_substr($slug, 0, $matches[1][1]);
        }
        if ($page === 1 && \mb_strlen($slug) > 0) {
            $url = Shop::getURL() . '/';
            if ($type !== null && isset($replacements['name'])) {
                $replacements['name'] = \mb_substr($replacements['name'], 0, $matches[1][1]);
                $url                  = Shop::getRouter()->getURLByType($type, $replacements);
            } elseif (isset($replacements['lang'])) {
                $c1 = Shop::getSettingValue(\CONF_GLOBAL, 'routing_default_language');
                $c2 = Shop::getSettingValue(\CONF_GLOBAL, 'routing_scheme');
                if ($c1 === 'L' || $c2 === 'L') {
                    $url .= $replacements['lang'] . '/';
                }
                $url .= $slug;
            } else {
                $url .= $slug;
            }
            \http_response_code(301);
            \header('Location: ' . $url);
            exit();
        }
        if ($page > 0) {
            $_GET['seite']          = $page;
            $this->params['kSeite'] = $page;
            $this->state->pageID    = $page;
        }
        $slug = $this->checkCharacteristics($slug);
        $slug = $this->checkManufacturers($slug);
        $slug = $this->checkCategories($slug);

        return $this->checkCharacteristicValues($slug);
    }

    /**
     * @param string $slug
     * @return string
     */
    private function checkCustomFilters(string $slug): string
    {
        $customSeo = [];
        foreach (Shop::getProductFilter()->getCustomFilters() as $customFilter) {
            $seoParam = $customFilter->getUrlParamSEO();
            if (empty($seoParam)) {
                continue;
            }
            $customFilterArr = \explode($seoParam, $slug);
            if (\count($customFilterArr) <= 1) {
                continue;
            }
            [$slug, $customFilterSeo] = $customFilterArr;
            if (\str_contains($customFilterSeo, \SEP_HST)) {
                $arr             = \explode(\SEP_HST, $customFilterSeo);
                $customFilterSeo = $arr[0];
                $slug           .= \SEP_HST . $arr[1];
            }
            if (($idx = \mb_strpos($customFilterSeo, \SEP_KAT)) !== false
                && $idx !== \mb_strpos($customFilterSeo, \SEP_HST)
            ) {
                $manufacturers   = \explode(\SEP_KAT, $customFilterSeo);
                $customFilterSeo = $manufacturers[0];
                $slug           .= \SEP_KAT . $manufacturers[1];
            }
            if (\str_contains($customFilterSeo, \SEP_MERKMAL)) {
                $arr             = \explode(\SEP_MERKMAL, $customFilterSeo);
                $customFilterSeo = $arr[0];
                $slug           .= \SEP_MERKMAL . $arr[1];
            }
            if (\str_contains($customFilterSeo, \SEP_MM_MMW)) {
                $arr             = \explode(\SEP_MM_MMW, $customFilterSeo);
                $customFilterSeo = $arr[0];
                $slug           .= \SEP_MM_MMW . $arr[1];
            }
            if (\str_contains($customFilterSeo, \SEP_SEITE)) {
                $arr             = \explode(\SEP_SEITE, $customFilterSeo);
                $customFilterSeo = $arr[0];
                $slug           .= \SEP_SEITE . $arr[1];
            }

            $customSeo[$customFilter->getClassName()] = [
                'cSeo'  => $customFilterSeo,
                'table' => $customFilter->getTableName()
            ];
        }

        // custom filter
        $this->params['customFilters'] = [];
        foreach ($customSeo as $className => $data) {
            $seoData = $this->db->select($data['table'], 'cSeo', $data['cSeo']);
            if ($seoData !== null && isset($seoData->filterval)) {
                $this->params['customFilters'][$className] = (int)$seoData->filterval;
                $this->state->customFilters[$className]    = (int)$seoData->filterval;
            } else {
                $this->params['bKatFilterNotFound']  = true;
                $this->state->categoryFilterNotFound = true;
            }
            if ($seoData !== null && $seoData->kSprache > 0) {
                Shop::updateLanguage((int)$seoData->kSprache);
            }
        }

        return $slug;
    }

    /**
     * @param string $slug
     * @return string
     */
    private function checkCharacteristics(string $slug): string
    {
        $characteristics = \explode(\SEP_MERKMAL, $slug);
        $slug            = $characteristics[0];
        foreach ($characteristics as $i => &$characteristic) {
            if ($i === 0) {
                continue;
            }
            if (($idx = \mb_strpos($characteristic, \SEP_KAT)) !== false
                && $idx !== \mb_strpos($characteristic, \SEP_HST)
            ) {
                $arr            = \explode(\SEP_KAT, $characteristic);
                $characteristic = $arr[0];
                $slug          .= \SEP_KAT . $arr[1];
            }
            if (\str_contains($characteristic, \SEP_HST)) {
                $arr            = \explode(\SEP_HST, $characteristic);
                $characteristic = $arr[0];
                $slug          .= \SEP_HST . $arr[1];
            }
            if (\str_contains($characteristic, \SEP_MM_MMW)) {
                $arr            = \explode(\SEP_MM_MMW, $characteristic);
                $characteristic = $arr[0];
                $slug          .= \SEP_MM_MMW . $arr[1];
            }
            if (\str_contains($characteristic, \SEP_SEITE)) {
                $arr            = \explode(\SEP_SEITE, $characteristic);
                $characteristic = $arr[0];
                $slug          .= \SEP_SEITE . $arr[1];
            }
        }
        unset($characteristic);
        // attribute filter
        if (\count($characteristics) <= 1) {
            return $slug;
        }
        if (!isset($_GET['mf'])) {
            $_GET['mf'] = [];
        } elseif (!\is_array($_GET['mf'])) {
            $_GET['mf'] = [(int)$_GET['mf']];
        }
        $this->params['bSEOMerkmalNotFound'] = false;
        $this->state->characteristicNotFound = false;
        foreach ($characteristics as $i => $seoString) {
            if ($i <= 0 || \mb_strlen($seoString) === 0) {
                continue;
            }
            $seoData = $this->db->select('tseo', 'cKey', 'kMerkmalWert', 'cSeo', $seoString);
            if ($seoData !== null && \strcasecmp($seoData->cSeo, $seoString) === 0) {
                // haenge an GET, damit baueMerkmalFilter die Merkmalfilter setzen kann - @todo?
                $_GET['mf'][]                           = (int)$seoData->kKey;
                $this->state->characteristicFilterIDs[] = (int)$seoData->kKey;
            } else {
                $this->params['bSEOMerkmalNotFound'] = true;
                $this->state->characteristicNotFound = true;
            }
        }

        return $slug;
    }

    /**
     * @param string $slug
     * @return string
     */
    private function checkManufacturers(string $slug): string
    {
        $manufSeo      = [];
        $manufacturers = \explode(\SEP_HST, $slug);
        if (\is_array($manufacturers) && \count($manufacturers) > 1) {
            foreach ($manufacturers as $i => $manufacturer) {
                if ($i === 0) {
                    $slug = $manufacturer;
                } else {
                    $manufSeo[] = $manufacturer;
                }
            }
            foreach ($manufSeo as $i => $hstseo) {
                if (($idx = \mb_strpos($hstseo, \SEP_KAT)) !== false && $idx !== \mb_strpos($hstseo, \SEP_HST)) {
                    $manufacturers[] = \explode(\SEP_KAT, $hstseo);
                    $manufSeo[$i]    = $manufacturers[0];
                    $slug           .= \SEP_KAT . $manufacturers[1];
                }
                if (\str_contains($hstseo, \SEP_MERKMAL)) {
                    $arr          = \explode(\SEP_MERKMAL, $hstseo);
                    $manufSeo[$i] = $arr[0];
                    $slug        .= \SEP_MERKMAL . $arr[1];
                }
                if (\str_contains($hstseo, \SEP_MM_MMW)) {
                    $arr          = \explode(\SEP_MM_MMW, $hstseo);
                    $manufSeo[$i] = $arr[0];
                    $slug        .= \SEP_MM_MMW . $arr[1];
                }
                if (\str_contains($hstseo, \SEP_SEITE)) {
                    $arr          = \explode(\SEP_SEITE, $hstseo);
                    $manufSeo[$i] = $arr[0];
                    $slug        .= \SEP_SEITE . $arr[1];
                }
            }
        } else {
            $slug = $manufacturers[0];
        }
        // manufacturer filter
        if (($seoCount = \count($manufSeo)) === 0) {
            return $slug;
        }
        if ($seoCount === 1) {
            $oSeo = $this->db->selectAll(
                'tseo',
                ['cKey', 'cSeo'],
                ['kHersteller', $manufSeo[0]],
                'kKey'
            );
        } else {
            $bindValues = [];
            // PDO::bindValue() is 1-based
            foreach ($manufSeo as $i => $t) {
                $bindValues[$i + 1] = $t;
            }
            $oSeo = $this->db->getObjects(
                "SELECT kKey
                    FROM tseo
                    WHERE cKey = 'kHersteller'
                    AND cSeo IN (" . \implode(',', \array_fill(0, $seoCount, '?')) . ')',
                $bindValues
            );
        }
        $results = \count($oSeo);
        if ($results === 1) {
            $this->params['kHerstellerFilter'] = (int)$oSeo[0]->kKey;
            $this->state->manufacturerFilterID = (int)$oSeo[0]->kKey;
        } elseif ($results === 0) {
            $this->params['bHerstellerFilterNotFound'] = true;
            $this->state->manufacturerFilterNotFound   = true;
        } else {
            $this->params['manufacturerFilterIDs'] = \array_map(static function ($e): int {
                return (int)$e->kKey;
            }, $oSeo);
            $this->state->manufacturerFilterIDs    = \array_map(static function ($e): int {
                return (int)$e->kKey;
            }, $oSeo);
        }

        return $slug;
    }

    /**
     * @param string $slug
     * @return string
     */
    private function checkCategories(string $slug): string
    {
        $categorySeo = '';
        $categories  = \explode(\SEP_KAT, $slug);
        if (\is_array($categories) && \count($categories) > 1) {
            [$slug, $categorySeo] = $categories;
            if (\str_contains($categorySeo, \SEP_HST)) {
                $arr         = \explode(\SEP_HST, $categorySeo);
                $categorySeo = $arr[0];
                $slug       .= \SEP_HST . $arr[1];
            }
            if (\str_contains($categorySeo, \SEP_MERKMAL)) {
                $arr         = \explode(\SEP_MERKMAL, $categorySeo);
                $categorySeo = $arr[0];
                $slug       .= \SEP_MERKMAL . $arr[1];
            }
            if (\str_contains($categorySeo, \SEP_MM_MMW)) {
                $arr         = \explode(\SEP_MM_MMW, $categorySeo);
                $categorySeo = $arr[0];
                $slug       .= \SEP_MM_MMW . $arr[1];
            }
            if (\str_contains($categorySeo, \SEP_SEITE)) {
                $arr         = \explode(\SEP_SEITE, $categorySeo);
                $categorySeo = $arr[0];
                $slug       .= \SEP_SEITE . $arr[1];
            }
        } elseif (\CATEGORIES_SLUG_HIERARCHICALLY === true && \str_contains($slug, '/')) {
            $valid = $this->validateCategoryHierarchy(\explode('/', $slug));
            if ($valid !== null) {
                $slug = $valid->slug;
            }
        } else {
            $slug = $categories[0];
        }
        // category filter
        if (\mb_strlen($categorySeo) > 0) {
            $seoData = $this->db->select('tseo', 'cKey', 'kKategorie', 'cSeo', $categorySeo);
            if ($seoData !== null && \strcasecmp($seoData->cSeo, $categorySeo) === 0) {
                $this->params['kKategorieFilter'] = (int)$seoData->kKey;
                $this->state->categoryFilterID    = (int)$seoData->kKey;
            } else {
                $this->params['bKatFilterNotFound']  = true;
                $this->state->categoryFilterNotFound = true;
            }
        }
        if (\count($categories) <= 1) {
            return $slug;
        }
        if (!isset($_GET['kf'])) {
            $_GET['kf'] = [];
        } elseif (!\is_array($_GET['kf'])) {
            $_GET['kf'] = [(int)$_GET['kf']];
        }
        $this->params['bSEOMerkmalNotFound'] = false;
        $this->state->characteristicNotFound = false;
        foreach ($categories as $i => $seoString) {
            if ($i === 0 || \mb_strlen($seoString) === 0) {
                continue;
            }
            $seoData = $this->db->select('tseo', 'cKey', 'kKategorie', 'cSeo', $seoString);
            if ($seoData !== null && \strcasecmp($seoData->cSeo, $seoString) === 0) {
                $_GET['kf'][] = (int)$seoData->kKey;
            } else {
                $this->params['bSEOMerkmalNotFound'] = true;
                $this->state->characteristicNotFound = true;
            }
        }

        return $slug;
    }

    /**
     * @param string $slug
     * @return string
     */
    private function checkCharacteristicValues(string $slug): string
    {
        // split attribute/attribute value
        $attributes = \explode(\SEP_MM_MMW, $slug);
        if (\is_array($attributes) && \count($attributes) > 1) {
            return $attributes[1];
        }

        return $slug;
    }
}
