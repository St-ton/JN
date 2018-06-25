<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Filter\States;

use DB\ReturnType;
use Filter\AbstractFilter;
use Filter\FilterJoin;
use Filter\FilterOption;
use Filter\FilterInterface;
use Filter\ProductFilter;
use function Functional\filter;

/**
 * Class BaseSearchQuery
 * @package Filter\States
 */
class BaseSearchQuery extends AbstractFilter
{
    use \MagicCompatibilityTrait;

    /**
     * @var array
     */
    private static $mapping = [
        'kSuchanfrage' => 'ID',
        'kSuchcache'   => 'SearchCacheID',
        'cSuche'       => 'Name',
        'Fehler'       => 'Error'
    ];

    /**
     * @former kSuchanfrage
     * @var int
     */
    private $id = 0;

    /**
     * @var int
     * @former kSuchCache
     */
    private $searchCacheID = 0;

    /**
     * @var string
     */
    public $error;

    /**
     * BaseSearchQuery constructor.
     *
     * @param ProductFilter $productFilter
     */
    public function __construct(ProductFilter $productFilter)
    {
        parent::__construct($productFilter);
        $this->setIsCustom(false)
             ->setUrlParam('suche')
             ->setUrlParamSEO(null);
    }

    /**
     * @return int
     */
    public function getSearchCacheID()
    {
        return $this->searchCacheID;
    }

    /**
     * @param int $id
     * @return $this
     */
    public function setSearchCacheID($id)
    {
        $this->searchCacheID = (int)$id;

        return $this;
    }

    /**
     * @param int $value
     * @return $this
     */
    public function setValue($value): FilterInterface
    {
        $this->value = (int)$value;

        return $this;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName($name): FilterInterface
    {
        $this->error = null;
        $minChars    = ($min = (int)$this->getConfig('artikeluebersicht')['suche_min_zeichen']) > 0
            ? $min
            : 3;
        if (strlen($name) > 0 || (isset($_GET['qs']) && $_GET['qs'] === '')) {
            preg_match("/[\w" . utf8_decode('äÄüÜöÖß') . "\.\-]{" . $minChars . ",}/",
                str_replace(' ', '', $name), $cTreffer_arr);
            if (count($cTreffer_arr) === 0) {
                $this->error = \Shop::Lang()->get('expressionHasTo') . ' ' .
                    $minChars . ' ' .
                    \Shop::Lang()->get('lettersDigits');
            }
        }

        return parent::setName($name);
    }

    /**
     * @return int
     */
    public function getValue()
    {
        return ($this->productFilter->getRealSearch() !== null && !$this->productFilter->hasSearchQuery())
            ? urlencode($this->productFilter->getRealSearch()->cSuche)
            : $this->value;
    }

    /**
     * @param int $id
     * @return $this
     */
    public function setID($id)
    {
        $this->id = (int)$id;

        return $this;
    }

    /**
     * @return int
     */
    public function getID()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getUrlParam(): string
    {
        if ($this->productFilter->getRealSearch() !== null && !$this->productFilter->hasSearchQuery()) {
            return 'suche';
        }

        return parent::getUrlParam();
    }

    /**
     * @param string $errorMsg
     * @return $this
     */
    public function setError($errorMsg)
    {
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
     * @inheritdoc
     */
    public function setSeo(array $languages): FilterInterface
    {
        $oSeo_obj = \Shop::Container()->getDB()->executeQueryPrepared(
            "SELECT tseo.cSeo, tseo.kSprache, tsuchanfrage.cSuche
                FROM tseo
                LEFT JOIN tsuchanfrage
                    ON tsuchanfrage.kSuchanfrage = tseo.kKey
                    AND tsuchanfrage.kSprache = tseo.kSprache
                WHERE cKey = 'kSuchanfrage' 
                    AND kKey = :key",
            ['key' => $this->getID()],
            ReturnType::SINGLE_OBJECT
        );
        foreach ($languages as $language) {
            $this->cSeo[$language->kSprache] = '';
            if (isset($oSeo_obj->kSprache) && $language->kSprache === (int)$oSeo_obj->kSprache) {
                $this->cSeo[$language->kSprache] = $oSeo_obj->cSeo;
            }
        }
        if (!empty($oSeo_obj->cSuche)) {
            $this->setName($oSeo_obj->cSuche);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getPrimaryKeyRow(): string
    {
        return 'kSuchanfrage';
    }

    /**
     * @inheritdoc
     */
    public function getTableName(): string
    {
        return 'tsuchanfrage';
    }

    /**
     * @inheritdoc
     */
    public function getSQLJoin()
    {
        $kSucheCache_arr = [];
        $searchFilter    = $this->productFilter->getBaseState();
        if (is_array($searchFilter)) {
            $count = count($searchFilter);
            foreach ($searchFilter as $oSuchFilter) {
                if ($oSuchFilter->getSearchCacheID() > 0) {
                    $kSucheCache_arr[] = $oSuchFilter->getSearchCacheID();
                }
            }
        } elseif ($searchFilter->getSearchCacheID() > 0) {
            $kSucheCache_arr[] = $searchFilter->getSearchCacheID();
            $count             = 1;
        } else {
            $kSucheCache_arr = [$searchFilter->getValue()];
            $count           = 1;
        }

        return (new FilterJoin())
            ->setType('JOIN')
            ->setTable('(SELECT tsuchcachetreffer.kArtikel, tsuchcachetreffer.kSuchCache, 
                          MIN(tsuchcachetreffer.nSort) AS nSort
                              FROM tsuchcachetreffer
                              WHERE tsuchcachetreffer.kSuchCache IN (' . implode(',', $kSucheCache_arr) . ') 
                              #JOIN tsuchcache
                              #    ON tsuchcachetreffer.kSuchCache = tsuchcache.kSuchCache
                              #JOIN tsuchanfrage
                              #    ON tsuchanfrage.cSuche = tsuchcache.cSuche
                              #    AND tsuchanfrage.kSuchanfrage IN (' . implode(',', $kSucheCache_arr) . ') 
                              GROUP BY tsuchcachetreffer.kArtikel
                              HAVING COUNT(*) = ' . $count . '
                          ) AS jSuche')
            ->setOn('jSuche.kArtikel = tartikel.kArtikel')
            ->setComment('JOIN1 from ' . __METHOD__)
            ->setOrigin(__CLASS__);
    }

    /**
     * @param null $data
     * @return FilterOption[]
     */
    public function getOptions($data = null): array
    {
        if ($this->options !== null) {
            return $this->options;
        }
        $options  = [];
        $naviConf = $this->getConfig('navigationsfilter');
        if ($naviConf['suchtrefferfilter_nutzen'] === 'N') {
            return $options;
        }
        $nLimit = (isset($naviConf['suchtrefferfilter_anzahl'])
            && ($limit = (int)$naviConf['suchtrefferfilter_anzahl']) > 0)
            ? ' LIMIT ' . $limit
            : '';
        $state  = $this->productFilter->getCurrentStateData();

        $state->addJoin((new FilterJoin())
            ->setComment('JOIN1 from ' . __METHOD__)
            ->setType('JOIN')
            ->setTable('tsuchcachetreffer')
            ->setOn('tartikel.kArtikel = tsuchcachetreffer.kArtikel')
            ->setOrigin(__CLASS__));
        $state->addJoin((new FilterJoin())
            ->setComment('JOIN2 from ' . __METHOD__)
            ->setType('JOIN')
            ->setTable('tsuchcache')
            ->setOn('tsuchcache.kSuchCache = tsuchcachetreffer.kSuchCache')
            ->setOrigin(__CLASS__));
        $state->addJoin((new FilterJoin())
            ->setComment('JOIN3 from ' . __METHOD__)
            ->setType('JOIN')
            ->setTable('tsuchanfrage')
            ->setOn('tsuchanfrage.cSuche = tsuchcache.cSuche 
                        AND tsuchanfrage.kSprache = ' . $this->getLanguageID())
            ->setOrigin(__CLASS__));

        $state->addCondition('tsuchanfrage.nAktiv = 1');

        $query            = $this->productFilter->getFilterSQL()->getBaseQuery(
            ['tsuchanfrage.kSuchanfrage', 'tsuchanfrage.cSuche', 'tartikel.kArtikel'],
            $state->getJoins(),
            $state->getConditions(),
            $state->getHaving(),
            null,
            '',
            ['tsuchanfrage.kSuchanfrage', 'tartikel.kArtikel']
        );
        $searchFilters    = \Shop::Container()->getDB()->query(
            'SELECT ssMerkmal.kSuchanfrage, ssMerkmal.cSuche, COUNT(*) AS nAnzahl
                FROM (' . $query . ') AS ssMerkmal
                    GROUP BY ssMerkmal.kSuchanfrage
                    ORDER BY ssMerkmal.cSuche' . $nLimit,
            ReturnType::ARRAY_OF_OBJECTS
        );
        $kSuchanfrage_arr = [];
        if ($this->productFilter->hasSearch()) {
            $kSuchanfrage_arr[] = (int)$this->productFilter->getSearch()->getValue();
        }
        if ($this->productFilter->hasSearchFilter()) {
            foreach ($this->productFilter->getSearchFilter() as $oSuchFilter) {
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
        $additionalFilter = new self($this->productFilter);
        // Priorität berechnen
        $nPrioStep = 0;
        $nCount    = count($searchFilters);
        if ($nCount > 0) {
            $nPrioStep = ($searchFilters[0]->nAnzahl - $searchFilters[$nCount - 1]->nAnzahl) / 9;
        }
        foreach ($searchFilters as $searchFilter) {
            $fo = (new FilterOption())
                ->setURL($this->productFilter->getFilterURL()->getURL(
                    $additionalFilter->init((int)$searchFilter->kSuchanfrage)
                ))
                ->setClass(rand(1, 10))
                ->setParam($this->getUrlParam())
                ->setType($this->getType())
                ->setClassName($this->getClassName())
                ->setName($searchFilter->cSuche)
                ->setValue((int)$searchFilter->kSuchanfrage)
                ->setCount((int)$searchFilter->nAnzahl);
            if (isset($searchFilter->kSuchCache) && $searchFilter->kSuchCache > 0 && $nPrioStep > 0) {
                $fo->setClass(round(
                        ($searchFilter->nAnzahl - $searchFilters[$nCount - 1]->nAnzahl) /
                        $nPrioStep
                    ) + 1
                );
            }
            $options[] = $fo;
        }
        $this->options = $options;

        return $options;
    }

    /**
     * @param string $Suchausdruck
     * @param int    $kSpracheExt
     * @return string
     * @former mappingBeachten
     */
    private function getQueryMapping($Suchausdruck, $kSpracheExt = 0): string
    {
        $kSprache = $kSpracheExt > 0
            ? (int)$kSpracheExt
            : $this->getLanguageID();
        if (strlen($Suchausdruck) > 0) {
            $SuchausdruckmappingTMP = \Shop::Container()->getDB()->select(
                'tsuchanfragemapping',
                'kSprache',
                $kSprache,
                'cSuche',
                $Suchausdruck
            );
            $Suchausdruckmapping    = $SuchausdruckmappingTMP;
            while (!empty($SuchausdruckmappingTMP->cSucheNeu)) {
                $SuchausdruckmappingTMP = \Shop::Container()->getDB()->select(
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
    public function editSearchCache($kSpracheExt = 0): int
    {
        require_once PFAD_ROOT . PFAD_INCLUDES . 'suche_inc.php';
        // Mapping beachten
        $cSuche = $this->getQueryMapping($this->getName(), $kSpracheExt);
        $this->setName($cSuche);
        $kSprache = $kSpracheExt > 0
            ? (int)$kSpracheExt
            : $this->getLanguageID();
        // Suchcache wurde zwar gefunden, ist jedoch nicht mehr gültig
        \Shop::Container()->getDB()->query(
            'DELETE tsuchcache, tsuchcachetreffer
                FROM tsuchcache
                LEFT JOIN tsuchcachetreffer 
                    ON tsuchcachetreffer.kSuchCache = tsuchcache.kSuchCache
                WHERE tsuchcache.dGueltigBis IS NOT NULL
                    AND DATE_ADD(tsuchcache.dGueltigBis, INTERVAL 5 MINUTE) < now()',
            ReturnType::AFFECTED_ROWS
        );

        // Suchcache checken, ob bereits vorhanden
        $oSuchCache = \Shop::Container()->getDB()->executeQueryPrepared(
            'SELECT kSuchCache
                FROM tsuchcache
                WHERE kSprache = :lang
                    AND cSuche = :search
                    AND (dGueltigBis > now() OR dGueltigBis IS NULL)',
            [
                'lang'   => $kSprache,
                'search' => $cSuche
            ],
            ReturnType::SINGLE_OBJECT
        );

        if (isset($oSuchCache->kSuchCache) && $oSuchCache->kSuchCache > 0) {
            return (int)$oSuchCache->kSuchCache; // Gib gültigen Suchcache zurück
        }
        // wenn kein Suchcache vorhanden
        $nMindestzeichen = ($min = (int)$this->getConfig('artikeluebersicht')['suche_min_zeichen']) > 0
            ? $min
            : 3;
        if (strlen($cSuche) < $nMindestzeichen) {
            require_once PFAD_ROOT . PFAD_INCLUDES . 'sprachfunktionen.php';
            $this->error = lang_suche_mindestanzahl($cSuche, $nMindestzeichen);

            return 0;
        }
        // Suchausdruck aufbereiten
        $cSuch_arr    = $this->prepareSearchQuery($cSuche);
        $cSuchTMP_arr = $cSuch_arr;
        if (count($cSuch_arr) === 0) {

            return 0;
        }
        // Array mit nach Prio sort. Suchspalten holen
        $searchColumnn_arr     = self::getSearchRows($this->getConfig());
        $searchColumns         = $this->getSearchColumnClasses($searchColumnn_arr);
        $oSuchCache            = new \stdClass();
        $oSuchCache->kSprache  = $kSprache;
        $oSuchCache->cSuche    = $cSuche;
        $oSuchCache->dErstellt = 'now()';
        $kSuchCache            = \Shop::Container()->getDB()->insert('tsuchcache', $oSuchCache);

        if ($this->getConfig('artikeluebersicht')['suche_fulltext'] !== 'N' && $this->isFulltextIndexActive()) {
            $oSuchCache->kSuchCache = $kSuchCache;

            return $this->editFullTextSearchCache(
                $oSuchCache,
                $searchColumnn_arr,
                $cSuch_arr,
                $this->getConfig('artikeluebersicht')['suche_max_treffer'],
                $this->getConfig('artikeluebersicht')['suche_fulltext']
            );
        }

        if ($kSuchCache <= 0) {
            return 0;
        }

        if ($this->getLanguageID() > 0 && !\Sprache::isDefaultLanguageActive()) {
            $cSQL = 'SELECT ' . $kSuchCache . ', IF(tartikel.kVaterArtikel > 0, 
                        tartikel.kVaterArtikel, tartikel.kArtikel) AS kArtikelTMP, ';
        } else {
            $cSQL = 'SELECT ' . $kSuchCache . ', IF(kVaterArtikel > 0, 
                        kVaterArtikel, kArtikel) AS kArtikelTMP, ';
        }
        // Shop2 Suche - mehr als 3 Suchwörter *
        if (count($cSuch_arr) > 3) {
            $cSQL .= " 1 ";
            if ($this->getLanguageID() > 0 && !\Sprache::isDefaultLanguageActive()) {
                $cSQL .= ' FROM tartikel
                                LEFT JOIN tartikelsprache
                                    ON tartikelsprache.kArtikel = tartikel.kArtikel
                                    AND tartikelsprache.kSprache = ' . $this->getLanguageID();
            } else {
                $cSQL .= ' FROM tartikel ';
            }
            $cSQL .= ' WHERE ';

            foreach ($searchColumnn_arr as $i => $searchColumnn) {
                if ($i > 0) {
                    $cSQL .= ' OR';
                }
                $cSQL .= '(';
                foreach ($cSuchTMP_arr as $j => $cSuch) {
                    if ($j > 0) {
                        $cSQL .= ' AND';
                    }
                    $cSQL .= ' ' . $searchColumnn . " LIKE '%" . $cSuch . "%'";
                }
                $cSQL .= ')';
            }
        } else {
            $brackets = 0;
            $nPrio    = 1;
            foreach ($searchColumnn_arr as $i => $searchColumnn) {
                // Fülle bei 1, 2 oder 3 Suchwörtern aufsplitten
                switch (count($cSuchTMP_arr)) {
                    case 1: // Fall 1, nur ein Suchwort
                        // "A"
                        $nonAllowed = [2];
                        if ($this->checkColumnClasses($searchColumns, $searchColumnn, $nonAllowed)) {
                            ++$brackets;
                            $cSQL .= "IF(" . $searchColumnn . " = '" . $cSuchTMP_arr[0] . "', " . ++$nPrio . ", ";
                        }
                        // "A_%"
                        $nonAllowed = [2, 3];
                        if ($this->checkColumnClasses($searchColumns, $searchColumnn, $nonAllowed)) {
                            ++$brackets;
                            $cSQL .= "IF(" . $searchColumnn . " LIKE '" . $cSuchTMP_arr[0] . " %', " . ++$nPrio . ", ";
                        }
                        // "%_A_%"
                        $nonAllowed = [3];
                        if ($this->checkColumnClasses($searchColumns, $searchColumnn, $nonAllowed)) {
                            ++$brackets;
                            $cSQL .= "IF(" . $searchColumnn . " LIKE '% " . $cSuchTMP_arr[0] . " %', " . ++$nPrio . ", ";
                        }
                        // "%_A"
                        $nonAllowed = [2, 3];
                        if ($this->checkColumnClasses($searchColumns, $searchColumnn, $nonAllowed)) {
                            ++$brackets;
                            $cSQL .= "IF(" . $searchColumnn . " LIKE '% " . $cSuchTMP_arr[0] . "', " . ++$nPrio . ", ";
                        }
                        // "%_A%"
                        $nonAllowed = [3];
                        if ($this->checkColumnClasses($searchColumns, $searchColumnn, $nonAllowed)) {
                            ++$brackets;
                            $cSQL .= "IF(" . $searchColumnn . " LIKE '% " . $cSuchTMP_arr[0] . "%', " . ++$nPrio . ", ";
                        }
                        // "%A_%"
                        $nonAllowed = [3];
                        if ($this->checkColumnClasses($searchColumns, $searchColumnn, $nonAllowed)) {
                            ++$brackets;
                            $cSQL .= "IF(" . $searchColumnn . " LIKE '%" . $cSuchTMP_arr[0] . " %', " . ++$nPrio . ", ";
                        }
                        // "A%"
                        $nonAllowed = [2, 3];
                        if ($this->checkColumnClasses($searchColumns, $searchColumnn, $nonAllowed)) {
                            ++$brackets;
                            $cSQL .= "IF(" . $searchColumnn . " LIKE '" . $cSuchTMP_arr[0] . "%', " . ++$nPrio . ", ";
                        }
                        // "%A"
                        $nonAllowed = [2, 3];
                        if ($this->checkColumnClasses($searchColumns, $searchColumnn, $nonAllowed)) {
                            ++$brackets;
                            $cSQL .= "IF(" . $searchColumnn . " LIKE '%" . $cSuchTMP_arr[0] . "', " . ++$nPrio . ", ";
                        }
                        // "%A%"
                        $nonAllowed = [3];
                        if ($this->checkColumnClasses($searchColumns, $searchColumnn, $nonAllowed)) {
                            ++$brackets;
                            $cSQL .= "IF(" . $searchColumnn . " LIKE '%" . $cSuchTMP_arr[0] . "%', " . ++$nPrio . ", ";
                        }
                        break;
                    case 2: // Fall 2, zwei Suchwörter
                        // "A_B"
                        $nonAllowed = [2];
                        if ($this->checkColumnClasses($searchColumns, $searchColumnn, $nonAllowed)) {
                            ++$brackets;
                            $cSQL .= "IF(" . $searchColumnn . " LIKE '" . $cSuchTMP_arr[0] . " " . $cSuchTMP_arr[1] . "', " . ++$nPrio . ", ";
                        }
                        // "B_A"
                        $nonAllowed = [2, 3];
                        if ($this->checkColumnClasses($searchColumns, $searchColumnn, $nonAllowed)) {
                            ++$brackets;
                            $cSQL .= "IF(" . $searchColumnn . " LIKE '" . $cSuchTMP_arr[1] . " " . $cSuchTMP_arr[0] . "', " . ++$nPrio . ", ";
                        }
                        // "A_B_%"
                        $nonAllowed = [2, 3];
                        if ($this->checkColumnClasses($searchColumns, $searchColumnn, $nonAllowed)) {
                            ++$brackets;
                            $cSQL .= "IF(" . $searchColumnn . " LIKE '" . $cSuchTMP_arr[0] . " " . $cSuchTMP_arr[1] . " %', " . ++$nPrio . ", ";
                        }
                        // "B_A_%"
                        $nonAllowed = [2, 3];
                        if ($this->checkColumnClasses($searchColumns, $searchColumnn, $nonAllowed)) {
                            ++$brackets;
                            $cSQL .= "IF(" . $searchColumnn . " LIKE '" . $cSuchTMP_arr[1] . " " . $cSuchTMP_arr[0] . " %', " . ++$nPrio . ", ";
                        }
                        // "%_A_B"
                        $nonAllowed = [2, 3];
                        if ($this->checkColumnClasses($searchColumns, $searchColumnn, $nonAllowed)) {
                            ++$brackets;
                            $cSQL .= "IF(" . $searchColumnn . " LIKE '% " . $cSuchTMP_arr[0] . " " . $cSuchTMP_arr[1] . "', " . ++$nPrio . ", ";
                        }
                        // "%_B_A"
                        $nonAllowed = [2, 3];
                        if ($this->checkColumnClasses($searchColumns, $searchColumnn, $nonAllowed)) {
                            ++$brackets;
                            $cSQL .= "IF(" . $searchColumnn . " LIKE '% " . $cSuchTMP_arr[1] . " " . $cSuchTMP_arr[0] . "', " . ++$nPrio . ", ";
                        }
                        // "%_A_B_%"
                        $nonAllowed = [3];
                        if ($this->checkColumnClasses($searchColumns, $searchColumnn, $nonAllowed)) {
                            ++$brackets;
                            $cSQL .= "IF(" . $searchColumnn . " LIKE '% " . $cSuchTMP_arr[0] . " " . $cSuchTMP_arr[1] . " %', " . ++$nPrio . ", ";
                        }
                        // "%_B_A_%"
                        $nonAllowed = [3];
                        if ($this->checkColumnClasses($searchColumns, $searchColumnn, $nonAllowed)) {
                            ++$brackets;
                            $cSQL .= "IF(" . $searchColumnn . " LIKE '% " . $cSuchTMP_arr[1] . " " . $cSuchTMP_arr[0] . " %', " . ++$nPrio . ", ";
                        }
                        // "%A_B_%"
                        $nonAllowed = [3];
                        if ($this->checkColumnClasses($searchColumns, $searchColumnn, $nonAllowed)) {
                            ++$brackets;
                            $cSQL .= "IF(" . $searchColumnn . " LIKE '%" . $cSuchTMP_arr[0] . " " . $cSuchTMP_arr[1] . " %', " . ++$nPrio . ", ";
                        }
                        // "%B_A_%"
                        $nonAllowed = [3];
                        if ($this->checkColumnClasses($searchColumns, $searchColumnn, $nonAllowed)) {
                            ++$brackets;
                            $cSQL .= "IF(" . $searchColumnn . " LIKE '%" . $cSuchTMP_arr[1] . " " . $cSuchTMP_arr[0] . " %', " . ++$nPrio . ", ";
                        }
                        // "%_A_B%"
                        $nonAllowed = [3];
                        if ($this->checkColumnClasses($searchColumns, $searchColumnn, $nonAllowed)) {
                            ++$brackets;
                            $cSQL .= "IF(" . $searchColumnn . " LIKE '% " . $cSuchTMP_arr[0] . " " . $cSuchTMP_arr[1] . "%', " . ++$nPrio . ", ";
                        }
                        // "%_B_A%"
                        $nonAllowed = [3];
                        if ($this->checkColumnClasses($searchColumns, $searchColumnn, $nonAllowed)) {
                            ++$brackets;
                            $cSQL .= "IF(" . $searchColumnn . " LIKE '% " . $cSuchTMP_arr[1] . " " . $cSuchTMP_arr[0] . "%', " . ++$nPrio . ", ";
                        }
                        // "%A_B%"
                        $nonAllowed = [3];
                        if ($this->checkColumnClasses($searchColumns, $searchColumnn, $nonAllowed)) {
                            ++$brackets;
                            $cSQL .= "IF(" . $searchColumnn . " LIKE '%" . $cSuchTMP_arr[0] . " " . $cSuchTMP_arr[1] . "%', " . ++$nPrio . ", ";
                        }
                        // "%B_A%"
                        $nonAllowed = [3];
                        if ($this->checkColumnClasses($searchColumns, $searchColumnn, $nonAllowed)) {
                            ++$brackets;
                            $cSQL .= "IF(" . $searchColumnn . " LIKE '%" . $cSuchTMP_arr[1] . " " . $cSuchTMP_arr[0] . "%', " . ++$nPrio . ", ";
                        }
                        // "%_A%_B_%"
                        $nonAllowed = [3];
                        if ($this->checkColumnClasses($searchColumns, $searchColumnn, $nonAllowed)) {
                            ++$brackets;
                            $cSQL .= "IF(" . $searchColumnn . " LIKE '% " . $cSuchTMP_arr[0] . "% " . $cSuchTMP_arr[1] . " %', " . ++$nPrio . ", ";
                        }
                        // "%_B%_A_%"
                        $nonAllowed = [3];
                        if ($this->checkColumnClasses($searchColumns, $searchColumnn, $nonAllowed)) {
                            ++$brackets;
                            $cSQL .= "IF(" . $searchColumnn . " LIKE '% " . $cSuchTMP_arr[1] . "% " . $cSuchTMP_arr[0] . " %', " . ++$nPrio . ", ";
                        }
                        // "%_A_%B_%"
                        $nonAllowed = [3];
                        if ($this->checkColumnClasses($searchColumns, $searchColumnn, $nonAllowed)) {
                            ++$brackets;
                            $cSQL .= "IF(" . $searchColumnn . " LIKE '% " . $cSuchTMP_arr[0] . " %" . $cSuchTMP_arr[1] . " %', " . ++$nPrio . ", ";
                        }
                        // "%_B_%A_%"
                        $nonAllowed = [3];
                        if ($this->checkColumnClasses($searchColumns, $searchColumnn, $nonAllowed)) {
                            ++$brackets;
                            $cSQL .= "IF(" . $searchColumnn . " LIKE '% " . $cSuchTMP_arr[1] . " %" . $cSuchTMP_arr[0] . " %', " . ++$nPrio . ", ";
                        }
                        // "%_A%_%B_%"
                        $nonAllowed = [2, 3];
                        if ($this->checkColumnClasses($searchColumns, $searchColumnn, $nonAllowed)) {
                            ++$brackets;
                            $cSQL .= "IF(" . $searchColumnn . " LIKE '% " . $cSuchTMP_arr[0] . "% %" . $cSuchTMP_arr[1] . " %', " . ++$nPrio . ", ";
                        }
                        // "%_B%_%A_%"
                        $nonAllowed = [2, 3];
                        if ($this->checkColumnClasses($searchColumns, $searchColumnn, $nonAllowed)) {
                            ++$brackets;
                            $cSQL .= "IF(" . $searchColumnn . " LIKE '% " . $cSuchTMP_arr[1] . "% %" . $cSuchTMP_arr[0] . " %', " . ++$nPrio . ", ";
                        }
                        break;
                    case 3: // Fall 3, drei Suchwörter
                        // "%A_%_B_%_C%"
                        $nonAllowed = [3];
                        if ($this->checkColumnClasses($searchColumns, $searchColumnn, $nonAllowed)) {
                            ++$brackets;
                            $cSQL .= "IF(" . $searchColumnn . " LIKE '%" . $cSuchTMP_arr[0] . " % " . $cSuchTMP_arr[1] . " % " . $cSuchTMP_arr[2] . "%', " . ++$nPrio . ", ";
                        }
                        // "%_A_% AND %_B_% AND %_C_%"
                        $nonAllowed = [3];
                        if ($this->checkColumnClasses($searchColumns, $searchColumnn, $nonAllowed)) {
                            ++$brackets;
                            $cSQL .= "IF((" . $searchColumnn . " LIKE '% " . $cSuchTMP_arr[0] . " %') AND (" . $searchColumnn .
                                " LIKE '% " . $cSuchTMP_arr[1] . " %') AND (" . $searchColumnn . " LIKE '% " . $cSuchTMP_arr[2] . " %'), " . ++$nPrio . ", ";
                        }
                        // "%_A_% AND %_B_% AND %C%"
                        $nonAllowed = [3];
                        if ($this->checkColumnClasses($searchColumns, $searchColumnn, $nonAllowed)) {
                            ++$brackets;
                            $cSQL .= "IF((" . $searchColumnn . " LIKE '" . $cSuchTMP_arr[0] . "') AND (" . $searchColumnn .
                                " LIKE '" . $cSuchTMP_arr[1] . "') AND (" . $searchColumnn . " LIKE '%" . $cSuchTMP_arr[2] . "%'), " . ++$nPrio . ", ";
                        }
                        // "%_A_% AND %B% AND %_C_%"
                        $nonAllowed = [3];
                        if ($this->checkColumnClasses($searchColumns, $searchColumnn, $nonAllowed)) {
                            ++$brackets;
                            $cSQL .= "IF((" . $searchColumnn . " LIKE '% " . $cSuchTMP_arr[0] . " %') AND (" . $searchColumnn .
                                " LIKE '%" . $cSuchTMP_arr[1] . "%') AND (" . $searchColumnn . " LIKE '% " . $cSuchTMP_arr[2] . " %'), " . ++$nPrio . ", ";
                        }
                        // "%_A_% AND %B% AND %C%"
                        $nonAllowed = [3];
                        if ($this->checkColumnClasses($searchColumns, $searchColumnn, $nonAllowed)) {
                            ++$brackets;
                            $cSQL .= "IF((" . $searchColumnn . " LIKE '% " . $cSuchTMP_arr[0] . " %') AND (" . $searchColumnn .
                                " LIKE '%" . $cSuchTMP_arr[1] . "%') AND (" . $searchColumnn . " LIKE '%" . $cSuchTMP_arr[2] . "%'), " . ++$nPrio . ", ";
                        }
                        // "%A% AND %_B_% AND %_C_%"
                        $nonAllowed = [3];
                        if ($this->checkColumnClasses($searchColumns, $searchColumnn, $nonAllowed)) {
                            ++$brackets;
                            $cSQL .= "IF((" . $searchColumnn . " LIKE '%" . $cSuchTMP_arr[0] . "%') AND (" . $searchColumnn .
                                " LIKE '% " . $cSuchTMP_arr[1] . " %') AND (" . $searchColumnn . " LIKE '% " . $cSuchTMP_arr[2] . " %'), " . ++$nPrio . ", ";
                        }
                        // "%A% AND %_B_% AND %C%"
                        $nonAllowed = [3];
                        if ($this->checkColumnClasses($searchColumns, $searchColumnn, $nonAllowed)) {
                            ++$brackets;
                            $cSQL .= "IF((" . $searchColumnn . " LIKE '%" . $cSuchTMP_arr[0] . "%') AND (" . $searchColumnn .
                                " LIKE '% " . $cSuchTMP_arr[1] . " %') AND (" . $searchColumnn . " LIKE '%" . $cSuchTMP_arr[2] . "%'), " . ++$nPrio . ", ";
                        }
                        // "%A% AND %B% AND %_C_%"
                        $nonAllowed = [3];
                        if ($this->checkColumnClasses($searchColumns, $searchColumnn, $nonAllowed)) {
                            ++$brackets;
                            $cSQL .= "IF((" . $searchColumnn . " LIKE '%" . $cSuchTMP_arr[0] . "%') AND (" . $searchColumnn .
                                " LIKE '%" . $cSuchTMP_arr[1] . "%') AND (" . $searchColumnn . " LIKE '% " . $cSuchTMP_arr[2] . " %'), " . ++$nPrio . ", ";
                        }
                        // "%A%B%C%"
                        $nonAllowed = [3];
                        if ($this->checkColumnClasses($searchColumns, $searchColumnn, $nonAllowed)) {
                            ++$brackets;
                            $cSQL .= "IF(" . $searchColumnn . " LIKE '%" . $cSuchTMP_arr[0] . "%" . $cSuchTMP_arr[1] . "%" . $cSuchTMP_arr[2] . "%', " . ++$nPrio . ", ";
                        }
                        // "%A% AND %B% AND %C%"
                        $nonAllowed = [3];
                        if ($this->checkColumnClasses($searchColumns, $searchColumnn, $nonAllowed)) {
                            ++$brackets;
                            $cSQL .= "IF((" . $searchColumnn . " LIKE '%" . $cSuchTMP_arr[0] . "%') AND (" . $searchColumnn .
                                " LIKE '%" . $cSuchTMP_arr[1] . "%') AND (" . $searchColumnn . " LIKE '%" . $cSuchTMP_arr[2] . "%'), " . ++$nPrio . ", ";
                        }
                        break;
                }

                if ($i === (count($searchColumnn_arr) - 1)) {
                    $cSQL .= '254)';
                }
            }

            for ($i = 0; $i < ($brackets - 1); ++$i) {
                $cSQL .= ')';
            }

            if ($this->getLanguageID() > 0 && !\Sprache::isDefaultLanguageActive()) {
                $cSQL .= ' FROM tartikel
                            LEFT JOIN tartikelsprache
                                ON tartikelsprache.kArtikel = tartikel.kArtikel
                                AND tartikelsprache.kSprache = ' . $this->getLanguageID();
            } else {
                $cSQL .= ' FROM tartikel ';
            }
            $cSQL .= " WHERE ";

            foreach ($searchColumnn_arr as $i => $searchColumnn) {
                if ($i > 0) {
                    $cSQL .= ' OR';
                }
                $cSQL .= '(';

                foreach ($cSuchTMP_arr as $j => $cSuch) {
                    if ($j > 0) {
                        $cSQL .= ' AND';
                    }
                    $cSQL .= " " . $searchColumnn . " LIKE '%" . $cSuch . "%'";
                }
                $cSQL .= ')';
            }
        }
        \Shop::Container()->getDB()->query(
            'INSERT INTO tsuchcachetreffer ' .
            $cSQL .
            ' GROUP BY kArtikelTMP
                LIMIT ' . (int)$this->getConfig('artikeluebersicht')['suche_max_treffer'],
            ReturnType::AFFECTED_ROWS
        );

        return $kSuchCache;
    }

    /**
     * @param string $query
     * @return array
     */
    public function prepareSearchQuery($query): array
    {
        $query          = str_replace(["'", '\\', '*', '%'], '', strip_tags($query));
        $searchArray    = [];
        $cSuchTMP_arr   = explode(' ', $query);
        $query_stripped = stripslashes($query);
        if ($query_stripped{0} !== '"' || $query_stripped{strlen($query_stripped) - 1} !== '"') {
            foreach ($cSuchTMP_arr as $i => $cSuchTMP) {
                if (strpos($cSuchTMP, '+') !== false) {
                    $searchPart = explode('+', $cSuchTMP);
                    foreach ($searchPart as $part) {
                        $part = trim($part);
                        if ($part) {
                            $searchArray[] = $part;
                        }
                    }
                } else {
                    $cSuchTMP = trim($cSuchTMP);
                    if ($cSuchTMP) {
                        $searchArray[] = $cSuchTMP;
                    }
                }
            }
        } else {
            $searchArray[] = str_replace('"', '', $query_stripped);
        }

        return $searchArray;
    }

    /**
     * @param \stdClass $oSuchCache
     * @param array     $searchColumnn_arr
     * @param array     $cSuch_arr
     * @param int       $nLimit
     * @param string    $cFullText
     * @return int
     * @former bearbeiteSuchCacheFulltext
     */
    private function editFullTextSearchCache(
        $oSuchCache,
        $searchColumnn_arr,
        $cSuch_arr,
        $nLimit = 0,
        $cFullText = 'Y'
    ): int {
        $nLimit = (int)$nLimit;

        if ($oSuchCache->kSuchCache > 0) {
            $cArtikelSpalten_arr = array_map(function ($item) {
                $item_arr = explode('.', $item, 2);

                return 'tartikel.' . $item_arr[1];
            }, $searchColumnn_arr);

            $cSprachSpalten_arr = array_filter($searchColumnn_arr, function ($item) {
                return preg_match('/tartikelsprache\.(.*)/', $item) ? true : false;
            });

            $score = "MATCH (" . implode(', ', $cArtikelSpalten_arr) . ")
                        AGAINST ('" . implode(' ', $cSuch_arr) . "' IN NATURAL LANGUAGE MODE)";
            if ($cFullText === 'B') {
                $match = "MATCH (" . implode(', ', $cArtikelSpalten_arr) . ")
                        AGAINST ('" . implode('* ', $cSuch_arr) . "*' IN BOOLEAN MODE)";
            } else {
                $match = $score;
            }

            $cSQL = "SELECT {$oSuchCache->kSuchCache} AS kSuchCache,
                    IF(tartikel.kVaterArtikel > 0, tartikel.kVaterArtikel, tartikel.kArtikel) AS kArtikelTMP,
                    $score AS score
                    FROM tartikel
                    WHERE $match " . $this->productFilter->getFilterSQL()->getStockFilterSQL() . " ";

            if (\Shop::getLanguage() > 0 && !\Sprache::isDefaultLanguageActive()) {
                $score = "MATCH (" . implode(', ', $cSprachSpalten_arr) . ")
                            AGAINST ('" . implode(' ', $cSuch_arr) . "' IN NATURAL LANGUAGE MODE)";
                if ($cFullText === 'B') {
                    $score = "MATCH (" . implode(', ', $cSprachSpalten_arr) . ")
                            AGAINST ('" . implode('* ', $cSuch_arr) . "*' IN BOOLEAN MODE)";
                } else {
                    $match = $score;
                }
                $cSQL .= "UNION DISTINCT
                SELECT {$oSuchCache->kSuchCache} AS kSuchCache,
                    IF(tartikel.kVaterArtikel > 0, tartikel.kVaterArtikel, tartikel.kArtikel) AS kArtikelTMP,
                    $score AS score
                    FROM tartikel
                    INNER JOIN tartikelsprache ON tartikelsprache.kArtikel = tartikel.kArtikel
                    WHERE $match " . $this->productFilter->getFilterSQL()->getStockFilterSQL() . " ";
            }

            $cISQL = "INSERT INTO tsuchcachetreffer
                    SELECT kSuchCache, kArtikelTMP, ROUND(MAX(15 - score) * 10)
                    FROM ($cSQL) AS i
                    LEFT JOIN tartikelsichtbarkeit 
                        ON tartikelsichtbarkeit.kArtikel = i.kArtikelTMP
                        AND tartikelsichtbarkeit.kKundengruppe = " . \Session::CustomerGroup()->getID() . "
                    WHERE tartikelsichtbarkeit.kKundengruppe IS NULL
                    GROUP BY kSuchCache, kArtikelTMP" . ($nLimit > 0 ? " LIMIT $nLimit" : '');

            \Shop::Container()->getDB()->query($cISQL, ReturnType::AFFECTED_ROWS);
        }

        return $oSuchCache->kSuchCache;
    }

    /**
     * @param array $searchColumns
     * @return array
     */
    public function getSearchColumnClasses($searchColumns): array
    {
        $result = [];
        if (is_array($searchColumns) && count($searchColumns) > 0) {
            foreach ($searchColumns as $columns) {
                // Klasse 1: Artikelname und Artikel SEO
                if (strpos($columns, 'cName') !== false
                    || strpos($columns, 'cSeo') !== false
                    || strpos($columns, 'cSuchbegriffe') !== false
                ) {
                    $result[1][] = $columns;
                }
                // Klasse 2: Artikelname und Artikel SEO
                if (strpos($columns, 'cKurzBeschreibung') !== false
                    || strpos($columns, 'cBeschreibung') !== false
                    || strpos($columns, 'cAnmerkung') !== false
                ) {
                    $result[2][] = $columns;
                }
                // Klasse 3: Artikelname und Artikel SEO
                if (strpos($columns, 'cArtNr') !== false
                    || strpos($columns, 'cBarcode') !== false
                    || strpos($columns, 'cISBN') !== false
                    || strpos($columns, 'cHAN') !== false
                ) {
                    $result[3][] = $columns;
                }
            }
        }

        return $result;
    }

    /**
     * @param array  $searchColumns
     * @param string $searchColumn
     * @param array  $nonAllowed
     * @return bool
     */
    public function checkColumnClasses($searchColumns, $searchColumn, $nonAllowed): bool
    {
        if (is_array($searchColumns)
            && is_array($nonAllowed)
            && count($searchColumns) > 0
            && strlen($searchColumn) > 0
            && count($nonAllowed) > 0
        ) {
            foreach ($nonAllowed as $class) {
                if (isset($searchColumns[$class]) && count($searchColumns[$class]) > 0) {
                    foreach ($searchColumns[$class] as $searchColumnnKlasse) {
                        if ($searchColumnnKlasse === $searchColumn) {
                            return false;
                        }
                    }
                }
            }
        }

        return true;
    }

    /**
     * @return bool
     */
    private function isFulltextIndexActive(): bool
    {
        static $active = null;

        if ($active === null) {
            $active = \Shop::Container()->getDB()->query(
                    "SHOW INDEX FROM tartikel 
                    WHERE KEY_NAME = 'idx_tartikel_fulltext'",
                    ReturnType::SINGLE_OBJECT)
                && \Shop::Container()->getDB()->query(
                    "SHOW INDEX 
                    FROM tartikelsprache 
                    WHERE KEY_NAME = 'idx_tartikelsprache_fulltext'",
                    ReturnType::SINGLE_OBJECT);
        }

        return $active;
    }

    /**
     * @param array $config
     * @return array
     * @former gibSuchSpalten()
     */
    public static function getSearchRows(array $config = null): array
    {
        $searchRows = [];
        $config     = $config ?? \Shop::getSettings([CONF_ARTIKELUEBERSICHT]);
        for ($i = 0; $i < 10; ++$i) {
            $searchRows[] = self::getPrioritizedRows($searchRows, $config);
        }

        return filter($searchRows, function ($r) {
            return $r !== '';
        });
    }

    /**
     * @param array $exclude
     * @param array $conf
     * @return string
     * @former gibMaxPrioSpalte()
     */
    public static function getPrioritizedRows(array $exclude, array $conf = null): string
    {
        $max     = 0;
        $current = '';
        $prefix  = 'tartikel.';
        $conf    = $conf['artikeluebersicht'] ?? \Shop::getSettings([CONF_ARTIKELUEBERSICHT])['artikeluebersicht'];
        if (!\Sprache::isDefaultLanguageActive()) {
            $prefix = 'tartikelsprache.';
        }
        if (!in_array($prefix . 'cName', $exclude, true) && $conf['suche_prio_name'] > $max) {
            $max     = $conf['suche_prio_name'];
            $current = $prefix . 'cName';
        }
        if (!in_array($prefix . 'cSeo', $exclude, true) && $conf['suche_prio_name'] > $max) {
            $max     = $conf['suche_prio_name'];
            $current = $prefix . 'cSeo';
        }
        if (!in_array('tartikel.cSuchbegriffe', $exclude, true) && $conf['suche_prio_suchbegriffe'] > $max) {
            $max     = $conf['suche_prio_suchbegriffe'];
            $current = 'tartikel.cSuchbegriffe';
        }
        if (!in_array('tartikel.cArtNr', $exclude, true) && $conf['suche_prio_artikelnummer'] > $max) {
            $max     = $conf['suche_prio_artikelnummer'];
            $current = 'tartikel.cArtNr';
        }
        if (!in_array($prefix . 'cKurzBeschreibung', $exclude, true) && $conf['suche_prio_kurzbeschreibung'] > $max) {
            $max     = $conf['suche_prio_kurzbeschreibung'];
            $current = $prefix . 'cKurzBeschreibung';
        }
        if (!in_array($prefix . 'cBeschreibung', $exclude, true) && $conf['suche_prio_beschreibung'] > $max) {
            $max     = $conf['suche_prio_beschreibung'];
            $current = $prefix . 'cBeschreibung';
        }
        if (!in_array('tartikel.cBarcode', $exclude, true) && $conf['suche_prio_ean'] > $max) {
            $max     = $conf['suche_prio_ean'];
            $current = 'tartikel.cBarcode';
        }
        if (!in_array('tartikel.cISBN', $exclude, true) && $conf['suche_prio_isbn'] > $max) {
            $max     = $conf['suche_prio_isbn'];
            $current = 'tartikel.cISBN';
        }
        if (!in_array('tartikel.cHAN', $exclude, true) && $conf['suche_prio_han'] > $max) {
            $max     = $conf['suche_prio_han'];
            $current = 'tartikel.cHAN';
        }
        if (!in_array('tartikel.cAnmerkung', $exclude, true) && $conf['suche_prio_anmerkung'] > $max) {
            $current = 'tartikel.cAnmerkung';
        }

        return $current;
    }
}
