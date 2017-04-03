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
        $join = new FilterJoin();
        $join->setType('JOIN')
             ->setTable('(SELECT tsuchcachetreffer.kArtikel, tsuchcachetreffer.kSuchCache, 
                          MIN(tsuchcachetreffer.nSort) AS nSort
                              FROM tsuchcachetreffer
                              WHERE tsuchcachetreffer.kSuchCache IN (' . implode(',', $kSucheCache_arr) . ') 
                              GROUP BY tsuchcachetreffer.kArtikel
                              HAVING COUNT(*) = ' . $count . '
                          ) AS jSuche')
             ->setOn('jSuche.kArtikel = tartikel.kArtikel')
             ->setComment('JOIN1 from FilterSearch');

        return $join;
    }

    /**
     * @param null $mixed
     * @return array|int|object
     */
    public function getOptions($mixed = null)
    {
        $oSuchFilterDB_arr = [];
        if ($this->getConfig()['navigationsfilter']['suchtrefferfilter_nutzen'] !== 'N') {
            $naviFilter = Shop::getNaviFilter();
            $nLimit     = (isset($this->getConfig()['navigationsfilter']['suchtrefferfilter_anzahl']) &&
                ($limit = (int)$this->getConfig()['navigationsfilter']['suchtrefferfilter_anzahl']) > 0)
                ? " LIMIT " . $limit
                : '';
            $order      = $naviFilter->getOrder();
            $state      = $naviFilter->getCurrentStateData();

            $state->joins[] = $order->join;
            $join           = new FilterJoin();
            $join->setComment('join1 from getSearchFilterOptions')
                 ->setType('JOIN')
                 ->setTable('tsuchcachetreffer')
                 ->setOn('tartikel.kArtikel = tsuchcachetreffer.kArtikel');
            $state->joins[] = $join;

            $join = new FilterJoin();
            $join->setComment('join2 from getSearchFilterOptions')
                 ->setType('JOIN')
                 ->setTable('tsuchcache')
                 ->setOn('tsuchcache.kSuchCache = tsuchcachetreffer.kSuchCache');
            $state->joins[] = $join;

            $join = new FilterJoin();
            $join->setComment('join3 from getSearchFilterOptions')
                 ->setType('JOIN')
                 ->setTable('tsuchanfrage')
                 ->setOn('tsuchanfrage.cSuche = tsuchcache.cSuche 
                              AND tsuchanfrage.kSprache = ' . $this->getLanguageID());
            $state->joins[] = $join;

            $state->conditions[] = "tsuchanfrage.nAktiv = 1";

            $query = $naviFilter->getBaseQuery(['tsuchanfrage.kSuchanfrage', 'tsuchanfrage.cSuche', 'tartikel.kArtikel'],
                $state->joins, $state->conditions, $state->having, $order->orderBy, '',
                ['tsuchanfrage.kSuchanfrage', 'tartikel.kArtikel']);

            $query = "SELECT ssMerkmal.kSuchanfrage, ssMerkmal.cSuche, count(*) AS nAnzahl
                FROM (" . $query . ") AS ssMerkmal
                    GROUP BY ssMerkmal.kSuchanfrage
                    ORDER BY ssMerkmal.cSuche" . $nLimit;

            $oSuchFilterDB_arr = Shop::DB()->query($query, 2);

            $kSuchanfrage_arr = [];
            if ($naviFilter->Suche->kSuchanfrage > 0) {
                $kSuchanfrage_arr[] = (int)$naviFilter->Suche->kSuchanfrage;
            }
            if (count($naviFilter->SuchFilter) > 0) {
                foreach ($naviFilter->SuchFilter as $oSuchFilter) {
                    if (isset($oSuchFilter->kSuchanfrage)) {
                        $kSuchanfrage_arr[] = (int)$oSuchFilter->kSuchanfrage;
                    }
                }
            }
            // Werfe bereits gesetzte Filter aus dem Ergebnis Array
            $nCount = count($oSuchFilterDB_arr);
            $count  = count($kSuchanfrage_arr);
            for ($j = 0; $j < $nCount; ++$j) {
                for ($i = 0; $i < $count; ++$i) {
                    if ($oSuchFilterDB_arr[$j]->kSuchanfrage == $kSuchanfrage_arr[$i]) {
                        unset($oSuchFilterDB_arr[$j]);
                        break;
                    }
                }
            }
            if (is_array($oSuchFilterDB_arr)) {
                $oSuchFilterDB_arr = array_merge($oSuchFilterDB_arr);
            }
            //baue URL
            $count = count($oSuchFilterDB_arr);
            for ($i = 0; $i < $count; ++$i) {
                $oZusatzFilter                           = new stdClass();
                $oZusatzFilter->SuchFilter               = new stdClass();
                $oZusatzFilter->SuchFilter->kSuchanfrage = (int)$oSuchFilterDB_arr[$i]->kSuchanfrage;
                $oSuchFilterDB_arr[$i]->cURL             = $naviFilter->getURL(true, $oZusatzFilter);
            }
            // Priorität berechnen
            $nPrioStep = 0;
            $nCount    = count($oSuchFilterDB_arr);
            if ($nCount > 0) {
                $nPrioStep = ($oSuchFilterDB_arr[0]->nAnzahl - $oSuchFilterDB_arr[$nCount - 1]->nAnzahl) / 9;
            }
            foreach ($oSuchFilterDB_arr as $i => $oSuchFilterDB) {
                $oSuchFilterDB_arr[$i]->Klasse = rand(1, 10);
                if (isset($oSuchFilterDB->kSuchCache) && $oSuchFilterDB->kSuchCache > 0 && $nPrioStep >= 0) {
                    $oSuchFilterDB_arr[$i]->Klasse = round(
                            ($oSuchFilterDB->nAnzahl - $oSuchFilterDB_arr[$nCount - 1]->nAnzahl) /
                            $nPrioStep
                        ) + 1;
                }
            }
        }

        return $oSuchFilterDB_arr;
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
}