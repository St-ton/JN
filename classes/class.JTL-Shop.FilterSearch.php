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
        $oSeo_obj = $this->db->executeQueryPrepared("
            SELECT tseo.cSeo, tseo.kSprache, tsuchanfrage.cSuche
                FROM tseo
                LEFT JOIN tsuchanfrage
                    ON tsuchanfrage.kSuchanfrage = tseo.kKey
                    AND tsuchanfrage.kSprache = tseo.kSprache
                WHERE cKey = 'kSuchanfrage' 
                    AND kKey = :key", ['key' => $this->getValue()], 1
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
     * @param string $cSuche
     * @param int    $nAnzahlTreffer
     * @param bool   $bEchteSuche
     * @param int    $kSpracheExt
     * @param bool   $bSpamFilter
     * @return bool
     * @former suchanfragenSpeichern
     */
    public function saveQuery($cSuche = '', $nAnzahlTreffer, $bEchteSuche = false, $kSpracheExt = 0, $bSpamFilter = true)
    {
        if ($cSuche === '') {
            $cSuche = $this->cSuche;
        }
        if (strlen($cSuche) > 0) {
            $Suchausdruck = str_replace(["'", "\\", "*", "%"], '', $cSuche);
            $kSprache     = (int)$kSpracheExt > 0 ? (int)$kSpracheExt : $this->getLanguageID();
            //db füllen für auswertugnen / suggest, dabei Blacklist beachten
            $Suchausdruck_tmp_arr = explode(';', $Suchausdruck);
            $blacklist_erg        = $this->db->select(
                'tsuchanfrageblacklist',
                'kSprache',
                $kSprache,
                'cSuche',
                $this->db->escape($Suchausdruck_tmp_arr[0])
            );
            if (!$bSpamFilter || !isset($blacklist_erg->kSuchanfrageBlacklist) || $blacklist_erg->kSuchanfrageBlacklist == 0) {
                // Ist MD5(IP) bereits X mal im Cache
                $max_ip_count = (int)$this->getConfig()['artikeluebersicht']['livesuche_max_ip_count'] * 100;
                $ip_cache_erg = $this->db->executeQueryPrepared(
                    "SELECT count(*) AS anzahl
                        FROM tsuchanfragencache
                        WHERE kSprache = :lang
                        AND cIP = :ip",
                    ['lang' => $kSprache, 'ip' => gibIP()],
                    1
                );
                $ip_used = $this->db->select(
                    'tsuchanfragencache',
                    'kSprache',
                    $kSprache,
                    'cSuche',
                    $Suchausdruck,
                    'cIP',
                    gibIP(),
                    false,
                    'kSuchanfrageCache'
                );
                if (!$bSpamFilter
                    || (isset($ip_cache_erg->anzahl) && $ip_cache_erg->anzahl < $max_ip_count
                        && (!isset($ip_used->kSuchanfrageCache) || !$ip_used->kSuchanfrageCache))
                ) {
                    // Fülle Suchanfragencache
                    $tsuchanfragencache_obj           = new stdClass();
                    $tsuchanfragencache_obj->kSprache = $kSprache;
                    $tsuchanfragencache_obj->cIP      = gibIP();
                    $tsuchanfragencache_obj->cSuche   = $Suchausdruck;
                    $tsuchanfragencache_obj->dZeit    = 'now()';
                    $this->db->insert('tsuchanfragencache', $tsuchanfragencache_obj);
                    // Cacheeinträge die > 1 Stunde sind, löschen
                    $this->db->query("
                        DELETE 
                            FROM tsuchanfragencache 
                            WHERE dZeit < DATE_SUB(now(),INTERVAL 1 HOUR)", 4
                    );
                    if ($nAnzahlTreffer > 0) {
                        require_once PFAD_ROOT . PFAD_DBES . 'seo.php';
                        $suchanfrage = new stdClass();
                        $suchanfrage->kSprache        = $kSprache;
                        $suchanfrage->cSuche          = $Suchausdruck;
                        $suchanfrage->nAnzahlTreffer  = $nAnzahlTreffer;
                        $suchanfrage->nAnzahlGesuche  = 1;
                        $suchanfrage->dZuletztGesucht = 'now()';
                        $suchanfrage->cSeo            = getSeo($Suchausdruck);
                        $suchanfrage->cSeo            = checkSeo($suchanfrage->cSeo);
                        $suchanfrage_old              = $this->db->select(
                            'tsuchanfrage',
                            'kSprache', (int)$suchanfrage->kSprache,
                            'cSuche', $Suchausdruck,
                            null, null,
                            false,
                            'kSuchanfrage'
                        );
                        if (isset($suchanfrage_old->kSuchanfrage) && $suchanfrage_old->kSuchanfrage > 0 && $bEchteSuche) {
                            $this->db->query(
                                "UPDATE tsuchanfrage
                                    SET nAnzahlTreffer = $suchanfrage->nAnzahlTreffer, 
                                        nAnzahlGesuche = nAnzahlGesuche+1, 
                                        dZuletztGesucht = now()
                                    WHERE kSuchanfrage = " . (int)$suchanfrage_old->kSuchanfrage, 4
                            );
                        } elseif (!isset($suchanfrage_old->kSuchanfrage) || !$suchanfrage_old->kSuchanfrage) {
                            $this->db->delete(
                                'tsuchanfrageerfolglos',
                                ['kSprache', 'cSuche'],
                                [(int)$suchanfrage->kSprache, $this->db->realEscape($Suchausdruck)]
                            );
                            $kSuchanfrage = $this->db->insert('tsuchanfrage', $suchanfrage);
                            writeLog(PFAD_LOGFILES . 'suchanfragen.log', print_r($suchanfrage, true), 1);

                            return (int)$kSuchanfrage;
                        }
                    } else {
                        $suchanfrageerfolglos                  = new stdClass();
                        $suchanfrageerfolglos->kSprache        = $kSprache;
                        $suchanfrageerfolglos->cSuche          = $Suchausdruck;
                        $suchanfrageerfolglos->nAnzahlGesuche  = 1;
                        $suchanfrageerfolglos->dZuletztGesucht = 'now()';
                        $suchanfrageerfolglos_old              = $this->db->select(
                            'tsuchanfrageerfolglos',
                            'kSprache', (int)$suchanfrageerfolglos->kSprache,
                            'cSuche', $Suchausdruck,
                            null, null,
                            false,
                            'kSuchanfrageErfolglos'
                        );
                        if (isset($suchanfrageerfolglos_old->kSuchanfrageErfolglos) &&
                            $suchanfrageerfolglos_old->kSuchanfrageErfolglos > 0 &&
                            $bEchteSuche) {
                            $this->db->query(
                                "UPDATE tsuchanfrageerfolglos
                                    SET nAnzahlGesuche = nAnzahlGesuche+1, 
                                        dZuletztGesucht = now()
                                    WHERE kSuchanfrageErfolglos = " .
                                    (int)$suchanfrageerfolglos_old->kSuchanfrageErfolglos,
                                4
                            );
                        } else {
                            $this->db->delete(
                                'tsuchanfrage',
                                ['kSprache', 'cSuche'],
                                [(int)$suchanfrageerfolglos->kSprache, $this->db->realEscape($Suchausdruck)]
                            );
                            $this->db->insert('tsuchanfrageerfolglos', $suchanfrageerfolglos);
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
        $count           = 0;
        $kSucheCache_arr = [];
        $searchFilter    = $this->naviFilter->getActiveState();
        if (is_array($searchFilter)) {
            $count = count($searchFilter);
            foreach ($searchFilter as $oSuchFilter) {
                if (isset($oSuchFilter->kSuchCache)) {
                    $kSucheCache_arr[] = (int)$oSuchFilter->kSuchCache;
                }
            }
        } elseif (isset($searchFilter->kSuchCache)) {
            $kSucheCache_arr[] = (int)$searchFilter->kSuchCache;
            $count             = 1;
        } elseif (($value = $searchFilter->getValue()) > 0) {
            $kSucheCache_arr = [$value];
            $count           = 1;
        }

        return (new FilterJoin())->setType('JOIN')
                                 ->setTable('(SELECT tsuchcachetreffer.kArtikel, tsuchcachetreffer.kSuchCache, 
                                  MIN(tsuchcachetreffer.nSort) AS nSort
                                      FROM tsuchcachetreffer
                                      WHERE tsuchcachetreffer.kSuchCache IN (' . implode(',', $kSucheCache_arr) . ') 
                                      GROUP BY tsuchcachetreffer.kArtikel
                                      HAVING COUNT(*) = ' . $count . '
                                  ) AS jSuche')
                                 ->setOn('jSuche.kArtikel = tartikel.kArtikel')
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
                ? " LIMIT " . $limit
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
            $query            = "SELECT ssMerkmal.kSuchanfrage, ssMerkmal.cSuche, count(*) AS nAnzahl
                FROM (" . $query . ") AS ssMerkmal
                    GROUP BY ssMerkmal.kSuchanfrage
                    ORDER BY ssMerkmal.cSuche" . $nLimit;
            $searchFilters    = $this->db->query($query, 2);
            $kSuchanfrage_arr = [];
            if ($this->naviFilter->Suche->kSuchanfrage > 0) {
                $kSuchanfrage_arr[] = (int)$this->naviFilter->Suche->kSuchanfrage;
            }
            if (count($this->naviFilter->SuchFilter) > 0) {
                foreach ($this->naviFilter->SuchFilter as $oSuchFilter) {
                    if ($oSuchFilter->getValue() > 0) {
                        $kSuchanfrage_arr[] = (int)$oSuchFilter->getValue();
                    }
                }
            }
            // entferne bereits gesetzte Filter aus dem Ergebnis-Array
            foreach ($searchFilters as $j => $searchFilter) {
                foreach ($kSuchanfrage_arr as $searchQuery) {
                    if ($searchFilter->kSuchanfrage === $searchQuery) {
                        unset($searchFilters[$j]);
                        break;
                    }
                }
            }
            if (is_array($searchFilters)) {
                $searchFilters = array_merge($searchFilters);
            }
            //baue URL
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

        return $options;
    }
}
