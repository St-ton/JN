<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class FilterSearch
 */
class FilterSearch extends AbstractFilter
{
    use MagicCompatibilityTrait;

    /**
     * @var int
     */
    public $kSuchCache = 0;

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
     * @var array
     */
    private static $mapping = [
        'kSuchanfrage' => 'Value',
        'cSuche'       => 'Name',
        'Fehler'       => 'Error'
    ];

    /**
     * FilterSearch constructor.
     *
     * @param ProductFilter $productFilter
     */
    public function __construct(ProductFilter $productFilter)
    {
        parent::__construct($productFilter);
        $this->isCustom    = false;
        $this->urlParam    = 'sf';
        $this->urlParamSEO = null;
    }

    /**
     * @param string $errorMsg
     * @return $this
     */
    public function setError($errorMsg) {
        $this->error = $errorMsg;

        return $this;
    }

    /**
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @param int $id
     * @return $this
     */
    public function setValue($id)
    {
        $this->searchID = (int)$id;

        return $this;
    }

    /**
     * @return int
     */
    public function getValue()
    {
        return $this->searchID;
    }

    /**
     * @param array $languages
     * @return $this
     */
    public function setSeo($languages)
    {
        $oSeo_obj = Shop::DB()->executeQueryPrepared(
            "SELECT tseo.cSeo, tseo.kSprache, tsuchanfrage.cSuche
                FROM tseo
                LEFT JOIN tsuchanfrage
                    ON tsuchanfrage.kSuchanfrage = tseo.kKey
                    AND tsuchanfrage.kSprache = tseo.kSprache
                WHERE cKey = 'kSuchanfrage' 
                    AND kKey = :kkey",
            ['kkey' => $this->getValue()],
            1
        );
        foreach ($languages as $language) {
            $this->cSeo[$language->kSprache] = '';
            if (isset($oSeo_obj->kSprache) && $language->kSprache === $oSeo_obj->kSprache) {
                $this->cSeo[$language->kSprache] = $oSeo_obj->cSeo;
            }
        }
        if (!empty($oSeo_obj->cSuche)) {
            $this->setName($oSeo_obj->cSuche);
        }

        return $this;
    }

    /**
     * @param int    $languageID
     * @param string $searchTerm
     * @return $this
     */
    public function setQueryID($languageID, $searchTerm)
    {
        $searchQuery = null;
        if ($languageID > 0 && strlen($searchTerm) > 0) {
            $searchQuery = Shop::DB()->select(
                'tsuchanfrage',
                'cSuche', Shop::DB()->escape($searchTerm),
                'kSprache', $languageID
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
    public function getPrimaryKeyRow()
    {
        return 'kSuchanfrage';
    }

    /**
     * @return string
     */
    public function getTableName()
    {
        return 'tsuchanfrage';
    }

    /**
     * @return string
     */
    public function getSQLCondition()
    {
        return '';
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
    public function saveQuery($hits, $query = '', $real = false, $languageIDExt = 0, $filterSpam = true)
    {
        if ($query === '') {
            $query = $this->getName();
        }
        if (empty($query)) {
            return false;
        }
        $Suchausdruck = str_replace(["'", "\\", "*", "%"], '', $query);
        $languageID   = (int)$languageIDExt > 0 ? (int)$languageIDExt : $this->getLanguageID();
        // db füllen für auswertugnen / suggest, dabei Blacklist beachten
        $tempQueries = explode(';', $Suchausdruck);
        $blacklist   = Shop::DB()->select(
            'tsuchanfrageblacklist',
            'kSprache',
            $languageID,
            'cSuche',
            Shop::DB()->escape($tempQueries[0])
        );
        if ($filterSpam && !empty($blacklist->kSuchanfrageBlacklist)) {
            return false;
        }
        // Ist MD5(IP) bereits X mal im Cache
        $max_ip_count = (int)$this->getConfig()['artikeluebersicht']['livesuche_max_ip_count'] * 100;
        $ip_cache_erg = Shop::DB()->executeQueryPrepared(
            'SELECT count(*) AS anzahl
                FROM tsuchanfragencache
                WHERE kSprache = :lang
                AND cIP = :ip',
            ['lang' => $languageID, 'ip' => gibIP()],
            1
        );
        $ipUsed       = Shop::DB()->select(
            'tsuchanfragencache',
            'kSprache',
            $languageID,
            'cSuche',
            $Suchausdruck,
            'cIP',
            gibIP(),
            false,
            'kSuchanfrageCache'
        );
        if (!$filterSpam
            || (isset($ip_cache_erg->anzahl) && $ip_cache_erg->anzahl < $max_ip_count
                && (!isset($ipUsed->kSuchanfrageCache) || !$ipUsed->kSuchanfrageCache))
        ) {
            // Fülle Suchanfragencache
            $searchQueryCache           = new stdClass();
            $searchQueryCache->kSprache = $languageID;
            $searchQueryCache->cIP      = gibIP();
            $searchQueryCache->cSuche   = $Suchausdruck;
            $searchQueryCache->dZeit    = 'now()';
            Shop::DB()->insert('tsuchanfragencache', $searchQueryCache);
            // Cacheeinträge die > 1 Stunde sind, löschen
            Shop::DB()->query(
                'DELETE 
                    FROM tsuchanfragencache 
                    WHERE dZeit < DATE_SUB(now(),INTERVAL 1 HOUR)', 4
            );
            if ($hits > 0) {
                require_once PFAD_ROOT . PFAD_DBES . 'seo.php';
                $searchQuery = new stdClass();
                $searchQuery->kSprache        = $languageID;
                $searchQuery->cSuche          = $Suchausdruck;
                $searchQuery->nAnzahlTreffer  = $hits;
                $searchQuery->nAnzahlGesuche  = 1;
                $searchQuery->dZuletztGesucht = 'now()';
                $searchQuery->cSeo            = getSeo($Suchausdruck);
                $searchQuery->cSeo            = checkSeo($searchQuery->cSeo);
                $previuousQuery               = Shop::DB()->select(
                    'tsuchanfrage',
                    'kSprache', (int)$searchQuery->kSprache,
                    'cSuche', $Suchausdruck,
                    null, null,
                    false,
                    'kSuchanfrage'
                );
                if ($real && $previuousQuery!== null && $previuousQuery->kSuchanfrage > 0) {
                    Shop::DB()->query(
                        'UPDATE tsuchanfrage
                            SET nAnzahlTreffer = ' . (int)$searchQuery->nAnzahlTreffer . ',
                                nAnzahlGesuche = nAnzahlGesuche+1, 
                                dZuletztGesucht = now()
                            WHERE kSuchanfrage = ' . (int)$previuousQuery->kSuchanfrage, 4
                    );
                } elseif (!isset($previuousQuery->kSuchanfrage) || !$previuousQuery->kSuchanfrage) {
                    Shop::DB()->delete(
                        'tsuchanfrageerfolglos',
                        ['kSprache', 'cSuche'],
                        [(int)$searchQuery->kSprache, Shop::DB()->realEscape($Suchausdruck)]
                    );

                    return Shop::DB()->insert('tsuchanfrage', $searchQuery);
                }
            } else {
                $queryMiss                  = new stdClass();
                $queryMiss->kSprache        = $languageID;
                $queryMiss->cSuche          = $Suchausdruck;
                $queryMiss->nAnzahlGesuche  = 1;
                $queryMiss->dZuletztGesucht = 'now()';
                $queryMiss_old              = Shop::DB()->select(
                    'tsuchanfrageerfolglos',
                    'kSprache', (int)$queryMiss->kSprache,
                    'cSuche', $Suchausdruck,
                    null, null,
                    false,
                    'kSuchanfrageErfolglos'
                );
                if ($queryMiss_old !== null
                    && $queryMiss_old->kSuchanfrageErfolglos > 0
                    && $real
                ) {
                    Shop::DB()->query(
                        'UPDATE tsuchanfrageerfolglos
                            SET nAnzahlGesuche = nAnzahlGesuche+1, 
                                dZuletztGesucht = now()
                            WHERE kSuchanfrageErfolglos = ' .
                            (int)$queryMiss_old->kSuchanfrageErfolglos,
                        4
                    );
                } else {
                    Shop::DB()->delete(
                        'tsuchanfrage',
                        ['kSprache', 'cSuche'],
                        [(int)$queryMiss->kSprache, Shop::DB()->realEscape($Suchausdruck)]
                    );
                    Shop::DB()->insert('tsuchanfrageerfolglos', $queryMiss);
                }
            }
        }

        return false;
    }

    /**
     * @return FilterJoin
     */
    public function getSQLJoin()
    {
        $count        = 0;
        $searchCache  = [];
        $searchFilter = $this->productFilter->getBaseState();
        if (is_array($searchFilter)) {
            $count = count($searchFilter);
            foreach ($searchFilter as $oSuchFilter) {
                if (isset($oSuchFilter->kSuchCache)) {
                    $searchCache[] = (int)$oSuchFilter->kSuchCache;
                }
            }
        } elseif (isset($searchFilter->kSuchCache)) {
            $searchCache[] = (int)$searchFilter->kSuchCache;
            $count         = 1;
        } elseif (($value = $searchFilter->getValue()) > 0) {
            $searchCache = [$value];
            $count       = 1;
        }

        return (new FilterJoin())
            ->setType('JOIN')
            ->setTable('(SELECT tsuchcachetreffer.kArtikel, tsuchcachetreffer.kSuchCache, 
                            MIN(tsuchcachetreffer.nSort) AS nSort
                              FROM tsuchcachetreffer
                              WHERE tsuchcachetreffer.kSuchCache IN (' . implode(',', $searchCache) . ') 
                              GROUP BY tsuchcachetreffer.kArtikel
                              HAVING COUNT(*) = ' . $count . '
                        ) AS jfSuche')
            ->setOn('jfSuche.kArtikel = tartikel.kArtikel')
            ->setComment('JOIN1 from FilterSearch');
    }

    /**
     * @param null $data
     * @return FilterOption[]
     */
    public function getOptions($data = null)
    {
        if ($this->options !== null) {
            return $this->options;
        }
        $options = [];
        if ($this->getConfig()['navigationsfilter']['suchtrefferfilter_nutzen'] !== 'N') {
            $nLimit = (isset($this->getConfig()['navigationsfilter']['suchtrefferfilter_anzahl'])
                && ($limit = (int)$this->getConfig()['navigationsfilter']['suchtrefferfilter_anzahl']) > 0)
                ? ' LIMIT ' . $limit
                : '';
            $state  = $this->productFilter->getCurrentStateData();

            $state->joins[] = (new FilterJoin())
                ->setComment('join1 from getSearchFilterOptions')
                ->setType('JOIN')
                ->setTable('tsuchcachetreffer')
                ->setOn('tartikel.kArtikel = tsuchcachetreffer.kArtikel')
                ->setOrigin(__CLASS__);
            $state->joins[] = (new FilterJoin())
                ->setComment('join2 from getSearchFilterOptions')
                ->setType('JOIN')
                ->setTable('tsuchcache')
                ->setOn('tsuchcache.kSuchCache = tsuchcachetreffer.kSuchCache')
                ->setOrigin(__CLASS__);
            $state->joins[] = (new FilterJoin())
                ->setComment('join3 from getSearchFilterOptions')
                ->setType('JOIN')
                ->setTable('tsuchanfrage')
                ->setOn('tsuchanfrage.cSuche = tsuchcache.cSuche 
                            AND tsuchanfrage.kSprache = ' . $this->getLanguageID())
                ->setOrigin(__CLASS__);

            $state->conditions[] = 'tsuchanfrage.nAktiv = 1';

            $query         = $this->productFilter->getFilterSQL()->getBaseQuery(
                ['tsuchanfrage.kSuchanfrage', 'tsuchanfrage.cSuche', 'tartikel.kArtikel'],
                $state->joins,
                $state->conditions,
                $state->having,
                null,
                '',
                ['tsuchanfrage.kSuchanfrage', 'tartikel.kArtikel']
            );
            $query         = 'SELECT ssMerkmal.kSuchanfrage, ssMerkmal.cSuche, count(*) AS nAnzahl
                FROM (' . $query . ') AS ssMerkmal
                    GROUP BY ssMerkmal.kSuchanfrage
                    ORDER BY ssMerkmal.cSuche' . $nLimit;
            $searchFilters = Shop::DB()->query($query, 2);
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
            if (is_array($searchFilters)) {
                $searchFilters = array_merge($searchFilters);
            }
            $additionalFilter = new FilterBaseSearchQuery($this->productFilter);
            $nCount           = count($searchFilters);
            $nPrioStep        = $nCount > 0
                ? ($searchFilters[0]->nAnzahl - $searchFilters[$nCount - 1]->nAnzahl) / 9
                : 0;
            foreach ($searchFilters as $searchFilter) {
                $class = rand(1, 10);
                if (isset($searchFilter->kSuchCache) && $searchFilter->kSuchCache > 0 && $nPrioStep >= 0) {
                    $class = round(
                            ($searchFilter->nAnzahl - $searchFilters[$nCount - 1]->nAnzahl) /
                            $nPrioStep
                        ) + 1;
                }
                $fo = (new FilterOption())
                    ->setType($this->getType())
                    ->setClassName($this->getClassName())
                    ->setClass($class)
                    ->setParam($this->getUrlParam())
                    ->setName($searchFilter->cSuche)
                    ->setValue((int)$searchFilter->kSuchanfrage)
                    ->setCount($searchFilter->nAnzahl)
                    ->setURL($this->productFilter->getFilterURL()->getURL(
                        $additionalFilter->init((int)$searchFilter->kSuchanfrage)
                    ));
                $fo->cSuche       = $searchFilter->cSuche;
                $fo->kSuchanfrage = $searchFilter->kSuchanfrage;

                $options[] = $fo;
            }
        }
        $this->options = $options;

        return $options;
    }
}
