<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Filter\Items;

use DB\ReturnType;
use Filter\AbstractFilter;
use Filter\FilterInterface;
use Filter\Join;
use Filter\Option;
use Filter\ProductFilter;
use Filter\States\BaseSearchQuery;
use Filter\StateSQL;

/**
 * Class Search
 * @package Filter
 */
class Search extends AbstractFilter
{
    use \JTL\MagicCompatibilityTrait;

    /**
     * @var int
     * @former kSuchCache
     */
    private $searchCacheID = 0;

    /**
     * @var string
     */
    private $error;

    /**
     * @var int
     * @former kSuchanfrage
     */
    private $searchID;

    /**
     * @var bool
     */
    public $bExtendedJTLSearch = false;

    /**
     * @var array
     */
    public static $mapping = [
        'kSuchanfrage' => 'Value',
        'cSuche'       => 'Name',
        'Fehler'       => 'Error'
    ];

    /**
     * Search constructor.
     *
     * @param ProductFilter $productFilter
     */
    public function __construct(ProductFilter $productFilter)
    {
        parent::__construct($productFilter);
        $this->setIsCustom(false)
             ->setUrlParam('sf');
    }

    /**
     * @return int
     */
    public function getSearchCacheID(): int
    {
        return $this->searchCacheID;
    }

    /**
     * @param int $id
     * @return $this
     */
    public function setSearchCacheID(int $id): FilterInterface
    {
        $this->searchCacheID = $id;

        return $this;
    }

    /**
     * @param string $errorMsg
     * @return $this
     */
    public function setError($errorMsg): FilterInterface
    {
        $this->error = $errorMsg;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getError(): ?string
    {
        return $this->error;
    }

    /**
     * @param int $value
     * @return $this
     */
    public function setValue($value): FilterInterface
    {
        $this->searchID = $value;

        return $this;
    }

    /**
     * @return int|string|null
     */
    public function getValue()
    {
        return $this->searchID;
    }

    /**
     * @inheritdoc
     */
    public function setSeo(array $languages): FilterInterface
    {
        $oSeo_obj = $this->productFilter->getDB()->executeQueryPrepared(
            "SELECT tseo.cSeo, tseo.kSprache, tsuchanfrage.cSuche
                FROM tseo
                LEFT JOIN tsuchanfrage
                    ON tsuchanfrage.kSuchanfrage = tseo.kKey
                    AND tsuchanfrage.kSprache = tseo.kSprache
                WHERE cKey = 'kSuchanfrage'
                    AND kKey = :kkey",
            ['kkey' => $this->getValue()],
            ReturnType::SINGLE_OBJECT
        );
        foreach ($languages as $language) {
            $this->cSeo[$language->kSprache] = '';
            if (isset($oSeo_obj->kSprache) && $language->kSprache === $oSeo_obj->kSprache) {
                $this->cSeo[$language->kSprache] = $oSeo_obj->cSeo;
            }
        }
        if (!empty($oSeo_obj->cSuche)) {
            $this->setName($oSeo_obj->cSuche);
        } elseif (!empty($oSeo_obj->cSeo)) {
            $this->setName($oSeo_obj->cSeo);
        }


        return $this;
    }

    /**
     * @param string $searchTerm
     * @param int    $languageID
     * @return $this
     */
    public function setQueryID(string $searchTerm, int $languageID): FilterInterface
    {
        $searchQuery = null;
        if ($languageID > 0 && \strlen($searchTerm) > 0) {
            $searchQuery = $this->productFilter->getDB()->select(
                'tsuchanfrage',
                'cSuche',
                $searchTerm,
                'kSprache',
                $languageID
            );
        }
        $this->setValue((isset($searchQuery->kSuchanfrage) && $searchQuery->kSuchanfrage > 0)
            ? (int)$searchQuery->kSuchanfrage
            : 0);

        return $this;
    }

    /**
     * @return string
     */
    public function getPrimaryKeyRow(): string
    {
        return 'kSuchanfrage';
    }

    /**
     * @return string
     */
    public function getTableName(): string
    {
        return 'tsuchanfrage';
    }

    /**
     * @param int    $hits
     * @param string $query
     * @param bool   $real
     * @param int    $languageIDExt
     * @param bool   $filterSpam
     * @return bool
     * @former suchanfragenSpeichern
     */
    public function saveQuery(
        int $hits,
        string $query = '',
        bool $real = false,
        int $languageIDExt = 0,
        bool $filterSpam = true
    ): bool {
        if ($query === '') {
            $query = $this->getName();
        }
        if (empty($query)) {
            return false;
        }
        $Suchausdruck = \str_replace(["'", '\\', '*', '%'], '', $query);
        $languageID   = $languageIDExt > 0 ? $languageIDExt : $this->getLanguageID();
        // db füllen für auswertugnen / suggest, dabei Blacklist beachten
        $tempQueries = \explode(';', $Suchausdruck);
        $blacklist   = $this->productFilter->getDB()->select(
            'tsuchanfrageblacklist',
            'kSprache',
            $languageID,
            'cSuche',
            $this->productFilter->getDB()->escape($tempQueries[0])
        );
        if ($filterSpam && $blacklist !== null && !empty($blacklist->kSuchanfrageBlacklist)) {
            return false;
        }
        // Ist md5(IP) bereits X mal im Cache
        $max_ip_count = (int)$this->getConfig('artikeluebersicht')['livesuche_max_ip_count'] * 100;
        $ip_cache_erg = $this->productFilter->getDB()->executeQueryPrepared(
            'SELECT COUNT(*) AS anzahl
                FROM tsuchanfragencache
                WHERE kSprache = :lang
                AND cIP = :ip',
            ['lang' => $languageID, 'ip' => \Helpers\RequestHelper::getRealIP()],
            ReturnType::SINGLE_OBJECT
        );
        $ipUsed       = $this->productFilter->getDB()->select(
            'tsuchanfragencache',
            'kSprache',
            $languageID,
            'cSuche',
            $Suchausdruck,
            'cIP',
            \Helpers\RequestHelper::getRealIP(),
            false,
            'kSuchanfrageCache'
        );
        if (!$filterSpam
            || (isset($ip_cache_erg->anzahl) && $ip_cache_erg->anzahl < $max_ip_count
                && ($ipUsed === null || empty($ipUsed->kSuchanfrageCache)))
        ) {
            // Fülle Suchanfragencache
            $searchQueryCache           = new \stdClass();
            $searchQueryCache->kSprache = $languageID;
            $searchQueryCache->cIP      = \Helpers\RequestHelper::getRealIP();
            $searchQueryCache->cSuche   = $Suchausdruck;
            $searchQueryCache->dZeit    = 'NOW()';
            $this->productFilter->getDB()->insert('tsuchanfragencache', $searchQueryCache);
            // Cacheeinträge die > 1 Stunde sind, löschen
            $this->productFilter->getDB()->query(
                'DELETE
                    FROM tsuchanfragencache
                    WHERE dZeit < DATE_SUB(NOW(),INTERVAL 1 HOUR)',
                ReturnType::AFFECTED_ROWS
            );
            if ($hits > 0) {
                require_once \PFAD_ROOT . \PFAD_DBES . 'seo.php';
                $searchQuery                  = new \stdClass();
                $searchQuery->kSprache        = $languageID;
                $searchQuery->cSuche          = $Suchausdruck;
                $searchQuery->nAnzahlTreffer  = $hits;
                $searchQuery->nAnzahlGesuche  = 1;
                $searchQuery->dZuletztGesucht = 'NOW()';
                $searchQuery->cSeo            = \JTL\SeoHelper::getSeo($Suchausdruck);
                $searchQuery->cSeo            = \JTL\SeoHelper::checkSeo($searchQuery->cSeo);
                $previuousQuery               = $this->productFilter->getDB()->select(
                    'tsuchanfrage',
                    'kSprache',
                    (int)$searchQuery->kSprache,
                    'cSuche',
                    $Suchausdruck,
                    null,
                    null,
                    false,
                    'kSuchanfrage'
                );
                if ($real && $previuousQuery !== null && $previuousQuery->kSuchanfrage > 0) {
                    $this->productFilter->getDB()->query(
                        'UPDATE tsuchanfrage
                            SET nAnzahlTreffer = ' . (int)$searchQuery->nAnzahlTreffer . ',
                                nAnzahlGesuche = nAnzahlGesuche + 1,
                                dZuletztGesucht = NOW()
                            WHERE kSuchanfrage = ' . (int)$previuousQuery->kSuchanfrage,
                        ReturnType::AFFECTED_ROWS
                    );
                } elseif (!isset($previuousQuery->kSuchanfrage) || !$previuousQuery->kSuchanfrage) {
                    $this->productFilter->getDB()->delete(
                        'tsuchanfrageerfolglos',
                        ['kSprache', 'cSuche'],
                        [(int)$searchQuery->kSprache, $this->productFilter->getDB()->realEscape($Suchausdruck)]
                    );

                    return $this->productFilter->getDB()->insert('tsuchanfrage', $searchQuery) > 0;
                }
            } else {
                $queryMiss                  = new \stdClass();
                $queryMiss->kSprache        = $languageID;
                $queryMiss->cSuche          = $Suchausdruck;
                $queryMiss->nAnzahlGesuche  = 1;
                $queryMiss->dZuletztGesucht = 'NOW()';
                $queryMiss_old              = $this->productFilter->getDB()->select(
                    'tsuchanfrageerfolglos',
                    'kSprache',
                    (int)$queryMiss->kSprache,
                    'cSuche',
                    $Suchausdruck,
                    null,
                    null,
                    false,
                    'kSuchanfrageErfolglos'
                );
                if ($queryMiss_old !== null
                    && $queryMiss_old->kSuchanfrageErfolglos > 0
                    && $real
                ) {
                    $this->productFilter->getDB()->query(
                        'UPDATE tsuchanfrageerfolglos
                            SET nAnzahlGesuche = nAnzahlGesuche + 1,
                                dZuletztGesucht = NOW()
                            WHERE kSuchanfrageErfolglos = ' .
                        (int)$queryMiss_old->kSuchanfrageErfolglos,
                        ReturnType::AFFECTED_ROWS
                    );
                } else {
                    $this->productFilter->getDB()->delete(
                        'tsuchanfrage',
                        ['kSprache', 'cSuche'],
                        [(int)$queryMiss->kSprache, $Suchausdruck]
                    );
                    $this->productFilter->getDB()->insert('tsuchanfrageerfolglos', $queryMiss);
                }
            }
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function getSQLJoin()
    {
        $count        = 0;
        $searchCache  = [];
        $searchFilter = $this->productFilter->getSearchFilter();
        if (\is_array($searchFilter)) {
            $count       = \count($searchFilter);
            $searchCache = \array_map(function ($f) {
                /** @var Search $f */
                return $f->getValue();
            }, $searchFilter);
        } elseif ($searchFilter->getSearchCacheID() > 0) {
            $searchCache[] = $searchFilter->getSearchCacheID();
            $count         = 1;
        } elseif (($value = $searchFilter->getValue()) > 0) {
            $searchCache = [$value];
            $count       = 1;
        }

        return (new Join())
            ->setType('JOIN')
            ->setTable('(SELECT tsuchcachetreffer.kArtikel, tsuchcachetreffer.kSuchCache,
                            MIN(tsuchcachetreffer.nSort) AS nSort
                              FROM tsuchcachetreffer
                              JOIN tsuchcache
                                  ON tsuchcachetreffer.kSuchCache = tsuchcache.kSuchCache
                              JOIN tsuchanfrage
                                  ON tsuchanfrage.cSuche = tsuchcache.cSuche
                                  AND tsuchanfrage.kSuchanfrage IN (' . \implode(',', $searchCache) . ')
                              GROUP BY tsuchcachetreffer.kArtikel
                              HAVING COUNT(*) = ' . $count . '
                        ) AS jfSuche')
            ->setOn('jfSuche.kArtikel = tartikel.kArtikel')
            ->setComment('JOIN1 from ' . __METHOD__);
    }

    /**
     * generate search cache entries for activated search queries
     *
     * @return $this
     */
    private function generateSearchCaches(): self
    {
        $allQueries = $this->productFilter->getDB()->query(
            'SELECT tsuchanfrage.cSuche FROM tsuchanfrage
                LEFT JOIN tsuchcache
                    ON tsuchcache.cSuche = tsuchanfrage.cSuche
                WHERE tsuchanfrage.nAktiv = 1
                    AND tsuchcache.kSuchCache IS NULL',
            ReturnType::ARRAY_OF_OBJECTS
        );
        foreach ($allQueries as $nonCachedQuery) {
            $bsq = new BaseSearchQuery($this->productFilter);
            $bsq->init($nonCachedQuery->cSuche)
                ->setName($nonCachedQuery->cSuche);
            $bsq->editSearchCache();
        }

        return $this;
    }

    /**
     * @param null $data
     * @return Option[]
     */
    public function getOptions($data = null): array
    {
        if ($this->options !== null) {
            return $this->options;
        }
        $options = [];
        if ($this->getConfig('navigationsfilter')['suchtrefferfilter_nutzen'] === 'N') {
            return $options;
        }
        $this->generateSearchCaches();
        $nLimit = ($limit = (int)$this->getConfig('navigationsfilter')['suchtrefferfilter_anzahl']) > 0
            ? ' LIMIT ' . $limit
            : '';
        $sql    = (new StateSQL())->from($this->productFilter->getCurrentStateData());
        $sql->setSelect([
            'tsuchanfrage.kSuchanfrage',
            'tsuchcache.kSuchCache',
            'tsuchanfrage.cSuche',
            'tartikel.kArtikel'
        ]);
        $sql->setOrderBy(null);
        $sql->setLimit('');
        $sql->setGroupBy(['tsuchanfrage.kSuchanfrage', 'tartikel.kArtikel']);
        $sql->addJoin((new Join())
            ->setComment('JOIN1 from ' . __METHOD__)
            ->setType('JOIN')
            ->setTable('tsuchcachetreffer')
            ->setOn('tartikel.kArtikel = tsuchcachetreffer.kArtikel')
            ->setOrigin(__CLASS__));
        $sql->addJoin((new Join())
            ->setComment('JOIN2 from ' . __METHOD__)
            ->setType('JOIN')
            ->setTable('tsuchcache')
            ->setOn('tsuchcache.kSuchCache = tsuchcachetreffer.kSuchCache')
            ->setOrigin(__CLASS__));
        $sql->addJoin((new Join())
            ->setComment('JOIN3 from ' . __METHOD__)
            ->setType('JOIN')
            ->setTable('tsuchanfrage')
            ->setOn('tsuchanfrage.cSuche = tsuchcache.cSuche
                        AND tsuchanfrage.kSprache = ' . $this->getLanguageID())
            ->setOrigin(__CLASS__));
        $sql->addCondition('tsuchanfrage.nAktiv = 1');

        $baseQuery = $this->productFilter->getFilterSQL()->getBaseQuery($sql);
        $cacheID   = 'fltr_' . \str_replace('\\', '', __CLASS__) . \md5($baseQuery);
        if (($cached = $this->productFilter->getCache()->get($cacheID)) !== false) {
            $this->options = $cached;

            return $this->options;
        }
        $searchFilters = $this->productFilter->getDB()->query(
            'SELECT ssMerkmal.kSuchanfrage, ssMerkmal.kSuchCache, ssMerkmal.cSuche, COUNT(*) AS nAnzahl
                FROM (' . $baseQuery . ') AS ssMerkmal
                    GROUP BY ssMerkmal.kSuchanfrage
                    ORDER BY ssMerkmal.cSuche' . $nLimit,
            ReturnType::ARRAY_OF_OBJECTS
        );
        $searchQueries = [];
        if ($this->productFilter->hasSearch()) {
            $searchQueries[] = $this->productFilter->getSearch()->getValue();
        }
        if ($this->productFilter->hasSearchFilter()) {
            foreach ($this->productFilter->getSearchFilter() as $oSuchFilter) {
                if ($oSuchFilter->getValue() > 0) {
                    $searchQueries[] = (int)$oSuchFilter->getValue();
                }
            }
        }
        // entferne bereits gesetzte Filter aus dem Ergebnis-Array
        foreach ($searchFilters as $j => $searchFilter) {
            foreach ($searchQueries as $searchQuery) {
                if ($searchFilter->kSuchanfrage === $searchQuery) {
                    unset($searchFilters[$j]);
                    break;
                }
            }
        }
        if (\is_array($searchFilters)) {
            $searchFilters = \array_merge($searchFilters);
        }
        $additionalFilter = new self($this->productFilter);
        $nCount           = \count($searchFilters);
        $nPrioStep        = $nCount > 0
            ? ($searchFilters[0]->nAnzahl - $searchFilters[$nCount - 1]->nAnzahl) / 9
            : 0;
        $activeValues     = \array_map(
            function ($f) {
                // @todo: create method for this logic
                /** @var Search $f */
                return $f->getValue();
            },
            $this->productFilter->getSearchFilter()
        );
        foreach ($searchFilters as $searchFilter) {
            $class = \rand(1, 10);
            if (isset($searchFilter->kSuchCache) && $searchFilter->kSuchCache > 0 && $nPrioStep > 0) {
                $class = \round(($searchFilter->nAnzahl - $searchFilters[$nCount - 1]->nAnzahl) / $nPrioStep) + 1;
            }
            $options[] = (new Option())
                ->setURL($this->productFilter->getFilterURL()->getURL(
                    $additionalFilter->init((int)$searchFilter->kSuchanfrage)
                ))
                ->setData('cSuche', $searchFilter->cSuche)
                ->setData('kSuchanfrage', $searchFilter->kSuchanfrage)
                ->setIsActive(\in_array((int)$searchFilter->kSuchanfrage, $activeValues, true))
                ->setType($this->getType())
                ->setClassName($this->getClassName())
                ->setClass((string)$class)
                ->setParam($this->getUrlParam())
                ->setName($searchFilter->cSuche)
                ->setValue((int)$searchFilter->kSuchanfrage)
                ->setCount((int)$searchFilter->nAnzahl);
        }
        $this->options = $options;
        $this->productFilter->getCache()->set($cacheID, $options, [\CACHING_GROUP_FILTER]);

        return $options;
    }
}
