<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class FilterBaseSearchQuery
 */
class FilterBaseSearchQuery extends AbstractFilter
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
     * FilterBaseSearchQuery constructor.
     *
     * @param int|null   $languageID
     * @param int|null   $customerGroupID
     * @param array|null $config
     * @param array|null $languages
     */
    public function __construct($languageID = null, $customerGroupID = null, $config = null, $languages = null)
    {
        parent::__construct($languageID, $customerGroupID, $config, $languages);
        $this->isCustom    = false;
        $this->urlParam    = 'l';
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
        $nf = Shop::getNaviFilter();
        if (!empty($nf->EchteSuche->cSuche) && empty($nf->Suchanfrage->kSuchanfrage)) {
            return urlencode($nf->EchteSuche->cSuche);
        }

        return $this->kSuchanfrage;
    }

    /**
     * @return string
     */
    public function getUrlParam()
    {
        $nf = Shop::getNaviFilter();
        if (!empty($nf->EchteSuche->cSuche) && empty($nf->Suchanfrage->kSuchanfrage)) {
            return 'suche';
        }

        return parent::getUrlParam();
    }

    /**
     * @param array $languages
     * @return $this
     */
    public function setSeo($languages)
    {
        $oSeo_obj = Shop::DB()->executeQueryPrepared("
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
            if (isset($oSeo_obj->kSprache) && $language->kSprache === (int)$oSeo_obj->kSprache) {
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
     * @return FilterJoin
     */
    public function getSQLJoin()
    {
        $count           = 0;
        $kSucheCache_arr = [];
        $searchFilter    = Shop::getNaviFilter()->getActiveState();
        if (is_array($searchFilter)) {
            $count = count($searchFilter);
            foreach ($searchFilter as $oSuchFilter) {
                if (isset($oSuchFilter->kSuchCache)) {
                    $kSucheCache_arr[] = (int)$oSuchFilter->kSuchCache;
                }
            }
        } elseif (isset($searchFilter->kSuchCache)) {
            $kSucheCache_arr[] = (int)$searchFilter->kSuchCache;
            $count = 1;
        } else {
            $kSucheCache_arr = [$searchFilter->getValue()];
            $count = 1;
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
     * @return array|int|object
     */
    public function getOptions($mixed = null)
    {
        if ($this->options !== null) {
            return $this->options;
        }
        $options = [];
        if ($this->getConfig()['navigationsfilter']['suchtrefferfilter_nutzen'] !== 'N') {
            $naviFilter = Shop::getNaviFilter();
            $nLimit     = (isset($this->getConfig()['navigationsfilter']['suchtrefferfilter_anzahl']) &&
                ($limit = (int)$this->getConfig()['navigationsfilter']['suchtrefferfilter_anzahl']) > 0)
                ? " LIMIT " . $limit
                : '';
            $order      = $naviFilter->getOrder();
            $state      = $naviFilter->getCurrentStateData();

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

            $state->conditions[] = "tsuchanfrage.nAktiv = 1";

            $query         = $naviFilter->getBaseQuery(
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
            $searchFilters = Shop::DB()->query($query, 2);

            $kSuchanfrage_arr = [];
            if ($naviFilter->Suche->getValue() > 0) {
                $kSuchanfrage_arr[] = (int)$naviFilter->Suche->getValue();
            }
            if (count($naviFilter->SuchFilter) > 0) {
                foreach ($naviFilter->SuchFilter as $oSuchFilter) {
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
            $additionalFilter = new FilterBaseSearchQuery(
                $this->getLanguageID(),
                $this->getCustomerGroupID(),
                $this->getConfig(),
                $this->getAvailableLanguages()
            );
            // Priorität berechnen
            $nPrioStep = 0;
            $nCount    = count($searchFilters);
            if ($nCount > 0) {
                $nPrioStep = ($searchFilters[0]->nAnzahl - $searchFilters[$nCount - 1]->nAnzahl) / 9;
            }
            foreach ($searchFilters as $searchFilter) {
                $fe = (new FilterExtra())
                    ->setType($this->getType())
                    ->setClassName($this->getClassName())
                    ->setParam($this->getUrlParam())
                    ->setName($searchFilter->cSuche)
                    ->setValue((int)$searchFilter->kSuchanfrage)
                    ->setCount($searchFilter->nAnzahl)
                    ->setURL($naviFilter->getURL(
                        true,
                        $additionalFilter->init((int)$searchFilter->kSuchanfrage)
                    ))
                    ->setClass(rand(1, 10));
                if (isset($searchFilter->kSuchCache) && $searchFilter->kSuchCache > 0 && $nPrioStep >= 0) {
                    $fe->setClass(round(
                            ($searchFilter->nAnzahl - $searchFilters[$nCount - 1]->nAnzahl) /
                            $nPrioStep
                        ) + 1
                    );
                }
                $options[] = $fe;
            }
        }

        return $options;
    }

    /**
     * @param string $Suchausdruck
     * @param int    $kSpracheExt
     * @return string
     * @former mappingBeachten
     */
    private function getMapping($Suchausdruck, $kSpracheExt = 0)
    {
        $kSprache = ($kSpracheExt > 0)
            ? (int)$kSpracheExt
            : $this->getLanguageID();
        if (strlen($Suchausdruck) > 0) {
            $SuchausdruckmappingTMP = Shop::DB()->select(
                'tsuchanfragemapping',
                'kSprache',
                $kSprache,
                'cSuche',
                $Suchausdruck
            );
            $Suchausdruckmapping    = $SuchausdruckmappingTMP;
            while (!empty($SuchausdruckmappingTMP->cSucheNeu)) {
                $SuchausdruckmappingTMP = Shop::DB()->select(
                    'tsuchanfragemapping',
                    'kSprache',
                    $kSprache,
                    'cSuche',
                    $SuchausdruckmappingTMP->cSucheNeu
                );
                if (!empty($SuchausdruckmappingTMP->cSucheNeu)) {
                    $Suchausdruckmapping = $SuchausdruckmappingTMP;
                }
            }
            if (!empty($Suchausdruckmapping->cSucheNeu)) {
                $Suchausdruck = $Suchausdruckmapping->cSucheNeu;
            }
        }

        return $Suchausdruck;
    }

    /**
     * @param int $kSpracheExt
     * @return int
     */
    public function editSearchCache($kSpracheExt = 0)
    {
        require_once PFAD_ROOT . PFAD_INCLUDES . 'suche_inc.php';
        // Mapping beachten
        $cSuche       = $this->getMapping($this->cSuche, $kSpracheExt);
        $this->cSuche = $cSuche;
        $kSprache     = ($kSpracheExt > 0)
            ? (int)$kSpracheExt
            : $this->getLanguageID();
        // Suchcache wurde zwar gefunden, ist jedoch nicht mehr gültig
        Shop::DB()->query("
            DELETE tsuchcache, tsuchcachetreffer
                FROM tsuchcache
                LEFT JOIN tsuchcachetreffer 
                    ON tsuchcachetreffer.kSuchCache = tsuchcache.kSuchCache
                WHERE tsuchcache.kSprache = " . $kSprache . "
                    AND tsuchcache.dGueltigBis IS NOT NULL
                    AND DATE_ADD(tsuchcache.dGueltigBis, INTERVAL 5 MINUTE) < now()", 3
        );

        // Suchcache checken, ob bereits vorhanden
        $oSuchCache = Shop::DB()->query("
            SELECT kSuchCache
                FROM tsuchcache
                WHERE kSprache =  " . $kSprache . "
                    AND cSuche = '" . Shop::DB()->escape($cSuche) . "'
                    AND (dGueltigBis > now() OR dGueltigBis IS NULL)", 1
        );
        $keySuche = $cSuche . ';' .
            $this->getConfig()['global']['artikel_artikelanzeigefilter'] . ';' .
            (int)$_SESSION['Kundengruppe']->kKundengruppe;
        // Suchcache checken, ob bereits vorhanden
        $oSuchCache = Shop::DB()->executeQueryPrepared(
            "SELECT kSuchCache
                FROM tsuchcache
                WHERE kSprache =  :lang
                    AND cSuche = :search
                    AND (dGueltigBis > now() OR dGueltigBis IS NULL)",
            ['lang' => $kSprache, 'search' => Shop::DB()->escape($keySuche)],
            1
        );

        if (isset($oSuchCache->kSuchCache) && $oSuchCache->kSuchCache > 0) {
            return (int)$oSuchCache->kSuchCache; // Gib gültigen Suchcache zurück
        }
        // wenn kein Suchcache vorhanden
        $nMindestzeichen = ((int)$this->getConfig()['artikeluebersicht']['suche_min_zeichen'] > 0)
            ? (int)$this->getConfig()['artikeluebersicht']['suche_min_zeichen']
            : 3;
        if (strlen($cSuche) < $nMindestzeichen) {
            require_once PFAD_ROOT . PFAD_INCLUDES . 'sprachfunktionen.php';
            $this->Fehler = lang_suche_mindestanzahl($cSuche, $nMindestzeichen);

            return 0;
        }
        // Suchausdruck aufbereiten
        $cSuch_arr    = suchausdruckVorbereiten($cSuche);
        $cSuchTMP_arr = $cSuch_arr;
        if (count($cSuch_arr) === 0) {

            return 0;
        }
        // Array mit nach Prio sort. Suchspalten holen
        $cSuchspalten_arr       = gibSuchSpalten();
        $cSuchspaltenKlasse_arr = gibSuchspaltenKlassen($cSuchspalten_arr);
        $oSuchCache             = new stdClass();
        $oSuchCache->kSprache   = $kSprache;
        $oSuchCache->cSuche     = $cSuche;
        $oSuchCache->dErstellt  = 'now()';
        $kSuchCache             = Shop::DB()->insert('tsuchcache', $oSuchCache);

        if ($this->getConfig()['artikeluebersicht']['suche_fulltext'] === 'Y' &&
            $this->isFulltextIndexActive()
        ) {
            $oSuchCache->kSuchCache = $kSuchCache;

            return $this->editFullTextSearchCache(
                $oSuchCache,
                $cSuchspalten_arr,
                $cSuch_arr, $this->getConfig()['artikeluebersicht']['suche_max_treffer']
            );
        }

        if ($kSuchCache <= 0) {
            return 0;
        }

        if ($this->getLanguageID() > 0 && !standardspracheAktiv()) {
            $cSQL = "SELECT " . $kSuchCache . ", IF(tartikel.kVaterArtikel > 0, 
                        tartikel.kVaterArtikel, tartikelsprache.kArtikel) AS kArtikelTMP, ";
        } else {
            $cSQL = "SELECT " . $kSuchCache . ", IF(kVaterArtikel > 0, 
                        kVaterArtikel, kArtikel) AS kArtikelTMP, ";
        }
        // Shop2 Suche - mehr als 3 Suchwörter *
        if (count($cSuch_arr) > 3) {
            $cSQL .= " 1 ";
            if ($this->getLanguageID() > 0 && !standardspracheAktiv()) {
                $cSQL .= " FROM tartikelsprache
                                LEFT JOIN tartikel 
                                    ON tartikelsprache.kArtikel = tartikel.kArtikel";
            } else {
                $cSQL .= " FROM tartikel ";
            }
            $cSQL .= " WHERE ";

            foreach ($cSuchspalten_arr as $i => $cSuchspalten) {
                if ($i > 0) {
                    $cSQL .= " OR";
                }
                $cSQL .= "(";
                foreach ($cSuchTMP_arr as $j => $cSuch) {
                    if ($j > 0) {
                        $cSQL .= " AND";
                    }
                    $cSQL .= " " . $cSuchspalten . " LIKE '%" . $cSuch . "%'";
                }
                $cSQL .= ")";
            }
        } else {
            $nKlammern = 0;
            $nPrio     = 1;
            foreach ($cSuchspalten_arr as $i => $cSuchspalten) {
                // Fülle bei 1, 2 oder 3 Suchwörtern aufsplitten
                switch (count($cSuchTMP_arr)) {
                    case 1: // Fall 1, nur ein Suchwort
                        // "A"
                        $nNichtErlaubteKlasse_arr = [2];
                        if (pruefeSuchspaltenKlassen($cSuchspaltenKlasse_arr, $cSuchspalten, $nNichtErlaubteKlasse_arr)) {
                            $nKlammern++;
                            $cSQL .= "IF(" . $cSuchspalten . " = '" . $cSuchTMP_arr[0] . "', " . ++$nPrio . ", ";
                        }
                        // "A_%"
                        $nNichtErlaubteKlasse_arr = [2, 3];
                        if (pruefeSuchspaltenKlassen($cSuchspaltenKlasse_arr, $cSuchspalten, $nNichtErlaubteKlasse_arr)) {
                            $nKlammern++;
                            $cSQL .= "IF(" . $cSuchspalten . " LIKE '" . $cSuchTMP_arr[0] . " %', " . ++$nPrio . ", ";
                        }
                        // "%_A_%"
                        $nNichtErlaubteKlasse_arr = [3];
                        if (pruefeSuchspaltenKlassen($cSuchspaltenKlasse_arr, $cSuchspalten, $nNichtErlaubteKlasse_arr)) {
                            $nKlammern++;
                            $cSQL .= "IF(" . $cSuchspalten . " LIKE '% " . $cSuchTMP_arr[0] . " %', " . ++$nPrio . ", ";
                        }
                        // "%_A"
                        $nNichtErlaubteKlasse_arr = [2, 3];
                        if (pruefeSuchspaltenKlassen($cSuchspaltenKlasse_arr, $cSuchspalten, $nNichtErlaubteKlasse_arr)) {
                            $nKlammern++;
                            $cSQL .= "IF(" . $cSuchspalten . " LIKE '% " . $cSuchTMP_arr[0] . "', " . ++$nPrio . ", ";
                        }
                        // "%_A%"
                        $nNichtErlaubteKlasse_arr = [3];
                        if (pruefeSuchspaltenKlassen($cSuchspaltenKlasse_arr, $cSuchspalten, $nNichtErlaubteKlasse_arr)) {
                            $nKlammern++;
                            $cSQL .= "IF(" . $cSuchspalten . " LIKE '% " . $cSuchTMP_arr[0] . "%', " . ++$nPrio . ", ";
                        }
                        // "%A_%"
                        $nNichtErlaubteKlasse_arr = [3];
                        if (pruefeSuchspaltenKlassen($cSuchspaltenKlasse_arr, $cSuchspalten, $nNichtErlaubteKlasse_arr)) {
                            $nKlammern++;
                            $cSQL .= "IF(" . $cSuchspalten . " LIKE '%" . $cSuchTMP_arr[0] . " %', " . ++$nPrio . ", ";
                        }
                        // "A%"
                        $nNichtErlaubteKlasse_arr = [2, 3];
                        if (pruefeSuchspaltenKlassen($cSuchspaltenKlasse_arr, $cSuchspalten, $nNichtErlaubteKlasse_arr)) {
                            $nKlammern++;
                            $cSQL .= "IF(" . $cSuchspalten . " LIKE '" . $cSuchTMP_arr[0] . "%', " . ++$nPrio . ", ";
                        }
                        // "%A"
                        $nNichtErlaubteKlasse_arr = [2, 3];
                        if (pruefeSuchspaltenKlassen($cSuchspaltenKlasse_arr, $cSuchspalten, $nNichtErlaubteKlasse_arr)) {
                            $nKlammern++;
                            $cSQL .= "IF(" . $cSuchspalten . " LIKE '%" . $cSuchTMP_arr[0] . "', " . ++$nPrio . ", ";
                        }
                        // "%A%"
                        $nNichtErlaubteKlasse_arr = [3];
                        if (pruefeSuchspaltenKlassen($cSuchspaltenKlasse_arr, $cSuchspalten, $nNichtErlaubteKlasse_arr)) {
                            $nKlammern++;
                            $cSQL .= "IF(" . $cSuchspalten . " LIKE '%" . $cSuchTMP_arr[0] . "%', " . ++$nPrio . ", ";
                        }
                        break;
                    case 2: // Fall 2, zwei Suchwörter
                        // "A_B"
                        $nNichtErlaubteKlasse_arr = [2];
                        if (pruefeSuchspaltenKlassen($cSuchspaltenKlasse_arr, $cSuchspalten, $nNichtErlaubteKlasse_arr)) {
                            $nKlammern++;
                            $cSQL .= "IF(" . $cSuchspalten . " LIKE '" . $cSuchTMP_arr[0] . " " . $cSuchTMP_arr[1] . "', " . ++$nPrio . ", ";
                        }
                        // "B_A"
                        $nNichtErlaubteKlasse_arr = [2, 3];
                        if (pruefeSuchspaltenKlassen($cSuchspaltenKlasse_arr, $cSuchspalten, $nNichtErlaubteKlasse_arr)) {
                            $nKlammern++;
                            $cSQL .= "IF(" . $cSuchspalten . " LIKE '" . $cSuchTMP_arr[1] . " " . $cSuchTMP_arr[0] . "', " . ++$nPrio . ", ";
                        }
                        // "A_B_%"
                        $nNichtErlaubteKlasse_arr = [2, 3];
                        if (pruefeSuchspaltenKlassen($cSuchspaltenKlasse_arr, $cSuchspalten, $nNichtErlaubteKlasse_arr)) {
                            $nKlammern++;
                            $cSQL .= "IF(" . $cSuchspalten . " LIKE '" . $cSuchTMP_arr[0] . " " . $cSuchTMP_arr[1] . " %', " . ++$nPrio . ", ";
                        }
                        // "B_A_%"
                        $nNichtErlaubteKlasse_arr = [2, 3];
                        if (pruefeSuchspaltenKlassen($cSuchspaltenKlasse_arr, $cSuchspalten, $nNichtErlaubteKlasse_arr)) {
                            $nKlammern++;
                            $cSQL .= "IF(" . $cSuchspalten . " LIKE '" . $cSuchTMP_arr[1] . " " . $cSuchTMP_arr[0] . " %', " . ++$nPrio . ", ";
                        }
                        // "%_A_B"
                        $nNichtErlaubteKlasse_arr = [2, 3];
                        if (pruefeSuchspaltenKlassen($cSuchspaltenKlasse_arr, $cSuchspalten, $nNichtErlaubteKlasse_arr)) {
                            $nKlammern++;
                            $cSQL .= "IF(" . $cSuchspalten . " LIKE '% " . $cSuchTMP_arr[0] . " " . $cSuchTMP_arr[1] . "', " . ++$nPrio . ", ";
                        }
                        // "%_B_A"
                        $nNichtErlaubteKlasse_arr = [2, 3];
                        if (pruefeSuchspaltenKlassen($cSuchspaltenKlasse_arr, $cSuchspalten, $nNichtErlaubteKlasse_arr)) {
                            $nKlammern++;
                            $cSQL .= "IF(" . $cSuchspalten . " LIKE '% " . $cSuchTMP_arr[1] . " " . $cSuchTMP_arr[0] . "', " . ++$nPrio . ", ";
                        }
                        // "%_A_B_%"
                        $nNichtErlaubteKlasse_arr = [3];
                        if (pruefeSuchspaltenKlassen($cSuchspaltenKlasse_arr, $cSuchspalten, $nNichtErlaubteKlasse_arr)) {
                            $nKlammern++;
                            $cSQL .= "IF(" . $cSuchspalten . " LIKE '% " . $cSuchTMP_arr[0] . " " . $cSuchTMP_arr[1] . " %', " . ++$nPrio . ", ";
                        }
                        // "%_B_A_%"
                        $nNichtErlaubteKlasse_arr = [3];
                        if (pruefeSuchspaltenKlassen($cSuchspaltenKlasse_arr, $cSuchspalten, $nNichtErlaubteKlasse_arr)) {
                            $nKlammern++;
                            $cSQL .= "IF(" . $cSuchspalten . " LIKE '% " . $cSuchTMP_arr[1] . " " . $cSuchTMP_arr[0] . " %', " . ++$nPrio . ", ";
                        }
                        // "%A_B_%"
                        $nNichtErlaubteKlasse_arr = [3];
                        if (pruefeSuchspaltenKlassen($cSuchspaltenKlasse_arr, $cSuchspalten, $nNichtErlaubteKlasse_arr)) {
                            $nKlammern++;
                            $cSQL .= "IF(" . $cSuchspalten . " LIKE '%" . $cSuchTMP_arr[0] . " " . $cSuchTMP_arr[1] . " %', " . ++$nPrio . ", ";
                        }
                        // "%B_A_%"
                        $nNichtErlaubteKlasse_arr = [3];
                        if (pruefeSuchspaltenKlassen($cSuchspaltenKlasse_arr, $cSuchspalten, $nNichtErlaubteKlasse_arr)) {
                            $nKlammern++;
                            $cSQL .= "IF(" . $cSuchspalten . " LIKE '%" . $cSuchTMP_arr[1] . " " . $cSuchTMP_arr[0] . " %', " . ++$nPrio . ", ";
                        }
                        // "%_A_B%"
                        $nNichtErlaubteKlasse_arr = [3];
                        if (pruefeSuchspaltenKlassen($cSuchspaltenKlasse_arr, $cSuchspalten, $nNichtErlaubteKlasse_arr)) {
                            $nKlammern++;
                            $cSQL .= "IF(" . $cSuchspalten . " LIKE '% " . $cSuchTMP_arr[0] . " " . $cSuchTMP_arr[1] . "%', " . ++$nPrio . ", ";
                        }
                        // "%_B_A%"
                        $nNichtErlaubteKlasse_arr = [3];
                        if (pruefeSuchspaltenKlassen($cSuchspaltenKlasse_arr, $cSuchspalten, $nNichtErlaubteKlasse_arr)) {
                            $nKlammern++;
                            $cSQL .= "IF(" . $cSuchspalten . " LIKE '% " . $cSuchTMP_arr[1] . " " . $cSuchTMP_arr[0] . "%', " . ++$nPrio . ", ";
                        }
                        // "%A_B%"
                        $nNichtErlaubteKlasse_arr = [3];
                        if (pruefeSuchspaltenKlassen($cSuchspaltenKlasse_arr, $cSuchspalten, $nNichtErlaubteKlasse_arr)) {
                            $nKlammern++;
                            $cSQL .= "IF(" . $cSuchspalten . " LIKE '%" . $cSuchTMP_arr[0] . " " . $cSuchTMP_arr[1] . "%', " . ++$nPrio . ", ";
                        }
                        // "%B_A%"
                        $nNichtErlaubteKlasse_arr = [3];
                        if (pruefeSuchspaltenKlassen($cSuchspaltenKlasse_arr, $cSuchspalten, $nNichtErlaubteKlasse_arr)) {
                            $nKlammern++;
                            $cSQL .= "IF(" . $cSuchspalten . " LIKE '%" . $cSuchTMP_arr[1] . " " . $cSuchTMP_arr[0] . "%', " . ++$nPrio . ", ";
                        }
                        // "%_A%_B_%"
                        $nNichtErlaubteKlasse_arr = [3];
                        if (pruefeSuchspaltenKlassen($cSuchspaltenKlasse_arr, $cSuchspalten, $nNichtErlaubteKlasse_arr)) {
                            $nKlammern++;
                            $cSQL .= "IF(" . $cSuchspalten . " LIKE '% " . $cSuchTMP_arr[0] . "% " . $cSuchTMP_arr[1] . " %', " . ++$nPrio . ", ";
                        }
                        // "%_B%_A_%"
                        $nNichtErlaubteKlasse_arr = [3];
                        if (pruefeSuchspaltenKlassen($cSuchspaltenKlasse_arr, $cSuchspalten, $nNichtErlaubteKlasse_arr)) {
                            $nKlammern++;
                            $cSQL .= "IF(" . $cSuchspalten . " LIKE '% " . $cSuchTMP_arr[1] . "% " . $cSuchTMP_arr[0] . " %', " . ++$nPrio . ", ";
                        }
                        // "%_A_%B_%"
                        $nNichtErlaubteKlasse_arr = [3];
                        if (pruefeSuchspaltenKlassen($cSuchspaltenKlasse_arr, $cSuchspalten, $nNichtErlaubteKlasse_arr)) {
                            $nKlammern++;
                            $cSQL .= "IF(" . $cSuchspalten . " LIKE '% " . $cSuchTMP_arr[0] . " %" . $cSuchTMP_arr[1] . " %', " . ++$nPrio . ", ";
                        }
                        // "%_B_%A_%"
                        $nNichtErlaubteKlasse_arr = [3];
                        if (pruefeSuchspaltenKlassen($cSuchspaltenKlasse_arr, $cSuchspalten, $nNichtErlaubteKlasse_arr)) {
                            $nKlammern++;
                            $cSQL .= "IF(" . $cSuchspalten . " LIKE '% " . $cSuchTMP_arr[1] . " %" . $cSuchTMP_arr[0] . " %', " . ++$nPrio . ", ";
                        }
                        // "%_A%_%B_%"
                        $nNichtErlaubteKlasse_arr = [2, 3];
                        if (pruefeSuchspaltenKlassen($cSuchspaltenKlasse_arr, $cSuchspalten, $nNichtErlaubteKlasse_arr)) {
                            $nKlammern++;
                            $cSQL .= "IF(" . $cSuchspalten . " LIKE '% " . $cSuchTMP_arr[0] . "% %" . $cSuchTMP_arr[1] . " %', " . ++$nPrio . ", ";
                        }
                        // "%_B%_%A_%"
                        $nNichtErlaubteKlasse_arr = [2, 3];
                        if (pruefeSuchspaltenKlassen($cSuchspaltenKlasse_arr, $cSuchspalten, $nNichtErlaubteKlasse_arr)) {
                            $nKlammern++;
                            $cSQL .= "IF(" . $cSuchspalten . " LIKE '% " . $cSuchTMP_arr[1] . "% %" . $cSuchTMP_arr[0] . " %', " . ++$nPrio . ", ";
                        }
                        break;
                    case 3: // Fall 3, drei Suchwörter
                        // "%A_%_B_%_C%"
                        $nNichtErlaubteKlasse_arr = [3];
                        if (pruefeSuchspaltenKlassen($cSuchspaltenKlasse_arr, $cSuchspalten, $nNichtErlaubteKlasse_arr)) {
                            $nKlammern++;
                            $cSQL .= "IF(" . $cSuchspalten . " LIKE '%" . $cSuchTMP_arr[0] . " % " . $cSuchTMP_arr[1] . " % " . $cSuchTMP_arr[2] . "%', " . ++$nPrio . ", ";
                        }
                        // "%_A_% AND %_B_% AND %_C_%"
                        $nNichtErlaubteKlasse_arr = [3];
                        if (pruefeSuchspaltenKlassen($cSuchspaltenKlasse_arr, $cSuchspalten, $nNichtErlaubteKlasse_arr)) {
                            $nKlammern++;
                            $cSQL .= "IF((" . $cSuchspalten . " LIKE '% " . $cSuchTMP_arr[0] . " %') AND (" . $cSuchspalten .
                                " LIKE '% " . $cSuchTMP_arr[1] . " %') AND (" . $cSuchspalten . " LIKE '% " . $cSuchTMP_arr[2] . " %'), " . ++$nPrio . ", ";
                        }
                        // "%_A_% AND %_B_% AND %C%"
                        $nNichtErlaubteKlasse_arr = [3];
                        if (pruefeSuchspaltenKlassen($cSuchspaltenKlasse_arr, $cSuchspalten, $nNichtErlaubteKlasse_arr)) {
                            $nKlammern++;
                            $cSQL .= "IF((" . $cSuchspalten . " LIKE '" . $cSuchTMP_arr[0] . "') AND (" . $cSuchspalten .
                                " LIKE '" . $cSuchTMP_arr[1] . "') AND (" . $cSuchspalten . " LIKE '%" . $cSuchTMP_arr[2] . "%'), " . ++$nPrio . ", ";
                        }
                        // "%_A_% AND %B% AND %_C_%"
                        $nNichtErlaubteKlasse_arr = [3];
                        if (pruefeSuchspaltenKlassen($cSuchspaltenKlasse_arr, $cSuchspalten, $nNichtErlaubteKlasse_arr)) {
                            $nKlammern++;
                            $cSQL .= "IF((" . $cSuchspalten . " LIKE '% " . $cSuchTMP_arr[0] . " %') AND (" . $cSuchspalten .
                                " LIKE '%" . $cSuchTMP_arr[1] . "%') AND (" . $cSuchspalten . " LIKE '% " . $cSuchTMP_arr[2] . " %'), " . ++$nPrio . ", ";
                        }
                        // "%_A_% AND %B% AND %C%"
                        $nNichtErlaubteKlasse_arr = [3];
                        if (pruefeSuchspaltenKlassen($cSuchspaltenKlasse_arr, $cSuchspalten, $nNichtErlaubteKlasse_arr)) {
                            $nKlammern++;
                            $cSQL .= "IF((" . $cSuchspalten . " LIKE '% " . $cSuchTMP_arr[0] . " %') AND (" . $cSuchspalten .
                                " LIKE '%" . $cSuchTMP_arr[1] . "%') AND (" . $cSuchspalten . " LIKE '%" . $cSuchTMP_arr[2] . "%'), " . ++$nPrio . ", ";
                        }
                        // "%A% AND %_B_% AND %_C_%"
                        $nNichtErlaubteKlasse_arr = [3];
                        if (pruefeSuchspaltenKlassen($cSuchspaltenKlasse_arr, $cSuchspalten, $nNichtErlaubteKlasse_arr)) {
                            $nKlammern++;
                            $cSQL .= "IF((" . $cSuchspalten . " LIKE '%" . $cSuchTMP_arr[0] . "%') AND (" . $cSuchspalten .
                                " LIKE '% " . $cSuchTMP_arr[1] . " %') AND (" . $cSuchspalten . " LIKE '% " . $cSuchTMP_arr[2] . " %'), " . ++$nPrio . ", ";
                        }
                        // "%A% AND %_B_% AND %C%"
                        $nNichtErlaubteKlasse_arr = [3];
                        if (pruefeSuchspaltenKlassen($cSuchspaltenKlasse_arr, $cSuchspalten, $nNichtErlaubteKlasse_arr)) {
                            $nKlammern++;
                            $cSQL .= "IF((" . $cSuchspalten . " LIKE '%" . $cSuchTMP_arr[0] . "%') AND (" . $cSuchspalten .
                                " LIKE '% " . $cSuchTMP_arr[1] . " %') AND (" . $cSuchspalten . " LIKE '%" . $cSuchTMP_arr[2] . "%'), " . ++$nPrio . ", ";
                        }
                        // "%A% AND %B% AND %_C_%"
                        $nNichtErlaubteKlasse_arr = [3];
                        if (pruefeSuchspaltenKlassen($cSuchspaltenKlasse_arr, $cSuchspalten, $nNichtErlaubteKlasse_arr)) {
                            $nKlammern++;
                            $cSQL .= "IF((" . $cSuchspalten . " LIKE '%" . $cSuchTMP_arr[0] . "%') AND (" . $cSuchspalten .
                                " LIKE '%" . $cSuchTMP_arr[1] . "%') AND (" . $cSuchspalten . " LIKE '% " . $cSuchTMP_arr[2] . " %'), " . ++$nPrio . ", ";
                        }
                        // "%A%B%C%"
                        $nNichtErlaubteKlasse_arr = [3];
                        if (pruefeSuchspaltenKlassen($cSuchspaltenKlasse_arr, $cSuchspalten, $nNichtErlaubteKlasse_arr)) {
                            $nKlammern++;
                            $cSQL .= "IF(" . $cSuchspalten . " LIKE '%" . $cSuchTMP_arr[0] . "%" . $cSuchTMP_arr[1] . "%" . $cSuchTMP_arr[2] . "%', " . ++$nPrio . ", ";
                        }
                        // "%A% AND %B% AND %C%"
                        $nNichtErlaubteKlasse_arr = [3];
                        if (pruefeSuchspaltenKlassen($cSuchspaltenKlasse_arr, $cSuchspalten, $nNichtErlaubteKlasse_arr)) {
                            $nKlammern++;
                            $cSQL .= "IF((" . $cSuchspalten . " LIKE '%" . $cSuchTMP_arr[0] . "%') AND (" . $cSuchspalten .
                                " LIKE '%" . $cSuchTMP_arr[1] . "%') AND (" . $cSuchspalten . " LIKE '%" . $cSuchTMP_arr[2] . "%'), " . ++$nPrio . ", ";
                        }
                        break;
                }

                if ($i === (count($cSuchspalten_arr) - 1)) {
                    $cSQL .= "254)";
                }
            }

            for ($i = 0; $i < ($nKlammern - 1); ++$i) {
                $cSQL .= ")";
            }

            if ($this->getLanguageID() > 0 && !standardspracheAktiv()) {
                $cSQL .= " FROM tartikelsprache
                            LEFT JOIN tartikel 
                                ON tartikelsprache.kArtikel = tartikel.kArtikel";
            } else {
                $cSQL .= " FROM tartikel ";
            }
            $cSQL .= " WHERE ";
            if ($this->getLanguageID() > 0 && !standardspracheAktiv()) {
                $cSQL .= " tartikelsprache.kSprache = " . $this->getLanguageID() . " AND ";
            }
            foreach ($cSuchspalten_arr as $i => $cSuchspalten) {
                if ($i > 0) {
                    $cSQL .= " OR";
                }
                $cSQL .= "(";

                foreach ($cSuchTMP_arr as $j => $cSuch) {
                    if ($j > 0) {
                        $cSQL .= " AND";
                    }
                    $cSQL .= " " . $cSuchspalten . " LIKE '%" . $cSuch . "%'";
                }
                $cSQL .= ")";
            }
        }
        Shop::DB()->query("
            INSERT INTO tsuchcachetreffer " .
            $cSQL . "
                GROUP BY kArtikelTMP
                LIMIT " . (int)$this->getConfig()['artikeluebersicht']['suche_max_treffer'], 3
        );

        return (int)$kSuchCache;
    }

    /**
     * @param stdClass $oSuchCache
     * @param array $cSuchspalten_arr
     * @param array $cSuch_arr
     * @param int $nLimit
     * @return int
     * @former bearbeiteSuchCacheFulltext
     */
    private function editFullTextSearchCache($oSuchCache, $cSuchspalten_arr, $cSuch_arr, $nLimit = 0)
    {
        $nLimit = (int)$nLimit;

        if ($oSuchCache->kSuchCache > 0) {
            $cArtikelSpalten_arr = array_map(function ($item) {
                $item_arr = explode('.', $item, 2);

                return 'tartikel.' . $item_arr[1];
            }, $cSuchspalten_arr);

            $cSprachSpalten_arr = array_filter($cSuchspalten_arr, function ($item) {
                return preg_match('/tartikelsprache\.(.*)/', $item) ? true : false;
            });

            $match = "MATCH (" . implode(', ', $cArtikelSpalten_arr) . ") AGAINST ('" . implode(' ', $cSuch_arr) . "' IN NATURAL LANGUAGE MODE)";
            $cSQL  = "SELECT {$oSuchCache->kSuchCache} AS kSuchCache,
                    IF(tartikel.kVaterArtikel > 0, tartikel.kVaterArtikel, tartikel.kArtikel) AS kArtikelTMP,
                    $match AS score
                    FROM tartikel
                    WHERE $match " . gibLagerfilter() . " ";

            if (Shop::$kSprache > 0 && !standardspracheAktiv()) {
                $match  = "MATCH (" . implode(', ', $cSprachSpalten_arr) . ") AGAINST ('" . implode(' ', $cSuch_arr) . "' IN NATURAL LANGUAGE MODE)";
                $cSQL  .= "UNION DISTINCT
                SELECT {$oSuchCache->kSuchCache} AS kSuchCache,
                    IF(tartikel.kVaterArtikel > 0, tartikel.kVaterArtikel, tartikel.kArtikel) AS kArtikelTMP,
                    $match AS score
                    FROM tartikel
                    INNER JOIN tartikelsprache ON tartikelsprache.kArtikel = tartikel.kArtikel
                    WHERE $match " . gibLagerfilter() . " ";
            }

            $cISQL = "INSERT INTO tsuchcachetreffer
                    SELECT kSuchCache, kArtikelTMP, ROUND(MAX(15 - score) * 10)
                    FROM ($cSQL) AS i
                    LEFT JOIN tartikelsichtbarkeit ON tartikelsichtbarkeit.kArtikel = i.kArtikelTMP
                        AND tartikelsichtbarkeit.kKundengruppe = " . ((int)$_SESSION['Kundengruppe']->kKundengruppe) . "
                    WHERE tartikelsichtbarkeit.kKundengruppe IS NULL
                    GROUP BY kSuchCache, kArtikelTMP" . ($nLimit > 0 ? " LIMIT $nLimit" : '');

            Shop::DB()->query($cISQL, 3);
        }

        return $oSuchCache->kSuchCache;
    }

    /**
     * @return bool
     */
    private function isFulltextIndexActive()
    {
        static $active = null;

        if ($active === null) {
            $active = Shop::DB()->query("SHOW INDEX FROM tartikel WHERE KEY_NAME = 'idx_tartikel_fulltext'", 1)
            && Shop::DB()->query("SHOW INDEX FROM tartikelsprache WHERE KEY_NAME = 'idx_tartikelsprache_fulltext'", 1);
        }

        return $active;
    }
}