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
    use FilterItemTrait;

    /**
     * @var int
     */
    public $kSuchanfrage = 0;

    /**
     * @var string
     */
    public $cSuche;

    /**
     * @var int
     */
    public $kSuchCache = 0;

    /**
     * @var string
     */
    public $Fehler;

    /**
     * FilterSearch constructor.
     *
     * @param Navigationsfilter $naviFilter
     */
    public function __construct($naviFilter)
    {
        parent::__construct($naviFilter);
        $this->isCustom    = false;
        $this->urlParam    = 'sf';
        $this->urlParamSEO = null;
    }

    /**
     * @param int $id
     * @return $this
     */
    public function setValue($id)
    {
        $this->kSuchanfrage = (int)$id;

        return $this;
    }

    /**
     * @return int
     */
    public function getValue()
    {
        return $this->kSuchanfrage;
    }

    /**
     * @param array $languages
     * @return $this
     */
    public function setSeo($languages)
    {
        $oSeo_obj = $this->db->executeQueryPrepared(
            "SELECT tseo.cSeo, tseo.kSprache, tsuchanfrage.cSuche
                FROM tseo
                LEFT JOIN tsuchanfrage
                    ON tsuchanfrage.kSuchanfrage = tseo.kKey
                    AND tsuchanfrage.kSprache = tseo.kSprache
                WHERE cKey = 'kSuchanfrage' 
                    AND kKey = :key", 
            ['key' => $this->getValue()], 
            1
        );
        foreach ($languages as $language) {
            $this->cSeo[$language->kSprache] = '';
            if (isset($oSeo_obj->kSprache) && $language->kSprache === $oSeo_obj->kSprache) {
                $this->cSeo[$language->kSprache] = $oSeo_obj->cSeo;
            }
        }
        if (!empty($oSeo_obj->cSuche)) {
            $this->cName = $oSeo_obj->cSuche;
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->cSuche;
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
            $query = $this->cSuche;
        }
        if (strlen($query) > 0) {
            $Suchausdruck = str_replace(["'", "\\", "*", "%"], '', $query);
            $languageID   = (int)$languageIDExt > 0 ? (int)$languageIDExt : $this->getLanguageID();
            // db füllen für auswertugnen / suggest, dabei Blacklist beachten
            $tempQueries = explode(';', $Suchausdruck);
            $blacklist   = $this->db->select(
                'tsuchanfrageblacklist',
                'kSprache',
                $languageID,
                'cSuche',
                $this->db->escape($tempQueries[0])
            );
            if (!$filterSpam || !isset($blacklist->kSuchanfrageBlacklist) || $blacklist->kSuchanfrageBlacklist == 0) {
                // Ist MD5(IP) bereits X mal im Cache
                $max_ip_count = (int)$this->getConfig()['artikeluebersicht']['livesuche_max_ip_count'] * 100;
                $ip_cache_erg = $this->db->executeQueryPrepared(
                    "SELECT count(*) AS anzahl
                        FROM tsuchanfragencache
                        WHERE kSprache = :lang
                        AND cIP = :ip",
                    ['lang' => $languageID, 'ip' => gibIP()],
                    1
                );
                $ipUsed = $this->db->select(
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
                    $this->db->insert('tsuchanfragencache', $searchQueryCache);
                    // Cacheeinträge die > 1 Stunde sind, löschen
                    $this->db->query("
                        DELETE 
                            FROM tsuchanfragencache 
                            WHERE dZeit < DATE_SUB(now(),INTERVAL 1 HOUR)", 4
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
                        $previuousQuery              = $this->db->select(
                            'tsuchanfrage',
                            'kSprache', (int)$searchQuery->kSprache,
                            'cSuche', $Suchausdruck,
                            null, null,
                            false,
                            'kSuchanfrage'
                        );
                        if (isset($previuousQuery->kSuchanfrage) && $previuousQuery->kSuchanfrage > 0 && $real) {
                            $this->db->query(
                                "UPDATE tsuchanfrage
                                    SET nAnzahlTreffer = $searchQuery->nAnzahlTreffer, 
                                        nAnzahlGesuche = nAnzahlGesuche+1, 
                                        dZuletztGesucht = now()
                                    WHERE kSuchanfrage = " . (int)$previuousQuery->kSuchanfrage, 4
                            );
                        } elseif (!isset($previuousQuery->kSuchanfrage) || !$previuousQuery->kSuchanfrage) {
                            $this->db->delete(
                                'tsuchanfrageerfolglos',
                                ['kSprache', 'cSuche'],
                                [(int)$searchQuery->kSprache, $this->db->realEscape($Suchausdruck)]
                            );
                            $queryID = $this->db->insert('tsuchanfrage', $searchQuery);
                            writeLog(PFAD_LOGFILES . 'suchanfragen.log', print_r($searchQuery, true), 1);

                            return (int)$queryID;
                        }
                    } else {
                        $queryMiss                  = new stdClass();
                        $queryMiss->kSprache        = $languageID;
                        $queryMiss->cSuche          = $Suchausdruck;
                        $queryMiss->nAnzahlGesuche  = 1;
                        $queryMiss->dZuletztGesucht = 'now()';
                        $queryMiss_old              = $this->db->select(
                            'tsuchanfrageerfolglos',
                            'kSprache', (int)$queryMiss->kSprache,
                            'cSuche', $Suchausdruck,
                            null, null,
                            false,
                            'kSuchanfrageErfolglos'
                        );
                        if (isset($queryMiss_old->kSuchanfrageErfolglos) &&
                            $queryMiss_old->kSuchanfrageErfolglos > 0 &&
                            $real
                        ) {
                            $this->db->query(
                                "UPDATE tsuchanfrageerfolglos
                                    SET nAnzahlGesuche = nAnzahlGesuche+1, 
                                        dZuletztGesucht = now()
                                    WHERE kSuchanfrageErfolglos = " .
                                    (int)$queryMiss_old->kSuchanfrageErfolglos,
                                4
                            );
                        } else {
                            $this->db->delete(
                                'tsuchanfrage',
                                ['kSprache', 'cSuche'],
                                [(int)$queryMiss->kSprache, $this->db->realEscape($Suchausdruck)]
                            );
                            $this->db->insert('tsuchanfrageerfolglos', $queryMiss);
                        }
                    }
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
        $searchFilter = $this->naviFilter->getActiveState();
        if (is_array($searchFilter)) {
            $count = count($searchFilter);
            foreach ($searchFilter as $oSuchFilter) {
                if (isset($oSuchFilter->kSuchCache)) {
                    $searchCache[] = (int)$oSuchFilter->kSuchCache;
                }
            }
        } elseif (isset($searchFilter->kSuchCache)) {
            $searchCache[] = (int)$searchFilter->kSuchCache;
            $count             = 1;
        } elseif (($value = $searchFilter->getValue()) > 0) {
            $searchCache = [$value];
            $count           = 1;
        }

        return (new FilterJoin())->setType('JOIN')
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
     * @param null $mixed
     * @return array
     */
    public function getOptions($mixed = null)
    {
        if ($this->options !== null) {
            return $this->options;
        }
        $options = [];
        if ($this->getConfig()['navigationsfilter']['suchtrefferfilter_nutzen'] !== 'N') {
            $nLimit     = (isset($this->getConfig()['navigationsfilter']['suchtrefferfilter_anzahl']) &&
                ($limit = (int)$this->getConfig()['navigationsfilter']['suchtrefferfilter_anzahl']) > 0)
                ? ' LIMIT ' . $limit
                : '';
            $order      = $this->naviFilter->getOrder();
            $state      = $this->naviFilter->getCurrentStateData();

            $state->joins[] = $order->join;
            $state->joins[] = (new FilterJoin())->setComment('join1 from getSearchFilterOptions')
                                                ->setType('JOIN')
                                                ->setTable('tsuchcachetreffer')
                                                ->setOn('tartikel.kArtikel = tsuchcachetreffer.kArtikel');
            $state->joins[] = (new FilterJoin())->setComment('join2 from getSearchFilterOptions')
                                                ->setType('JOIN')
                                                ->setTable('tsuchcache')
                                                ->setOn('tsuchcache.kSuchCache = tsuchcachetreffer.kSuchCache');
            $state->joins[] = (new FilterJoin())->setComment('join3 from getSearchFilterOptions')
                                                ->setType('JOIN')
                                                ->setTable('tsuchanfrage')
                                                ->setOn('tsuchanfrage.cSuche = tsuchcache.cSuche 
                                                            AND tsuchanfrage.kSprache = ' . $this->getLanguageID());

            $state->conditions[] = 'tsuchanfrage.nAktiv = 1';

            $query            = $this->naviFilter->getBaseQuery(
                ['tsuchanfrage.kSuchanfrage', 'tsuchanfrage.cSuche', 'tartikel.kArtikel'],
                $state->joins,
                $state->conditions,
                $state->having,
                $order->orderBy,
                '',
                ['tsuchanfrage.kSuchanfrage', 'tartikel.kArtikel']
            );
            $query         = "SELECT ssMerkmal.kSuchanfrage, ssMerkmal.cSuche, count(*) AS nAnzahl
                FROM (" . $query . ") AS ssMerkmal
                    GROUP BY ssMerkmal.kSuchanfrage
                    ORDER BY ssMerkmal.cSuche" . $nLimit;
            $searchFilters = $this->db->query($query, 2);
            $searchQueries = [];
            if ($this->naviFilter->Suche->kSuchanfrage > 0) {
                $searchQueries[] = (int)$this->naviFilter->Suche->kSuchanfrage;
            }
            if (count($this->naviFilter->SuchFilter) > 0) {
                foreach ($this->naviFilter->SuchFilter as $oSuchFilter) {
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
            $additionalFilter = new FilterBaseSearchQuery($this->naviFilter);
            // Priorität berechnen
            $nPrioStep = 0;
            $nCount    = count($searchFilters);
            if ($nCount > 0) {
                $nPrioStep = ($searchFilters[0]->nAnzahl - $searchFilters[$nCount - 1]->nAnzahl) / 9;
            }
            foreach ($searchFilters as $searchFilter) {
                $class = rand(1, 10);
                if (isset($searchFilter->kSuchCache) && $searchFilter->kSuchCache > 0 && $nPrioStep >= 0) {
                    $class = round(
                            ($searchFilter->nAnzahl - $searchFilters[$nCount - 1]->nAnzahl) /
                            $nPrioStep
                        ) + 1;
                }
                $fe = (new FilterExtra())
                    ->setType($this->getType())
                    ->setClassName($this->getClassName())
                    ->setClass($class)
                    ->setParam($this->getUrlParam())
                    ->setName('@todo: setName for FilterSearch')
                    ->setValue((int)$searchFilter->kSuchanfrage)
                    ->setCount($searchFilter->nAnzahl)
                    ->setURL($this->naviFilter->getURL(
                        true,
                        $additionalFilter->init((int)$searchFilter->kSuchanfrage)
                    ));
                $fe->cSuche       = $searchFilter->cSuche;
                $fe->kSuchanfrage = $searchFilter->kSuchanfrage;

                $options[] = $fe;
            }
        }
        $this->options = $options;

        return $options;
    }
}
