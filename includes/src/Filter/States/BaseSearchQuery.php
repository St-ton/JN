<?php declare(strict_types=1);

namespace JTL\Filter\States;

use JTL\Filter\AbstractFilter;
use JTL\Filter\FilterInterface;
use JTL\Filter\Join;
use JTL\Filter\Option;
use JTL\Filter\ProductFilter;
use JTL\Filter\StateSQL;
use JTL\Helpers\Request;
use JTL\Language\LanguageHelper;
use JTL\MagicCompatibilityTrait;
use JTL\Router\RoutableTrait;
use JTL\Router\Router;
use JTL\Session\Frontend;
use JTL\Shop;
use stdClass;
use function Functional\filter;

/**
 * Class BaseSearchQuery
 * @package JTL\Filter\States
 */
class BaseSearchQuery extends AbstractFilter
{
    use MagicCompatibilityTrait;
    use RoutableTrait;

    /**
     * @var array
     */
    public static array $mapping = [
        'kSuchanfrage' => 'ID',
        'kSuchcache'   => 'SearchCacheID',
        'cSuche'       => 'Name',
        'Fehler'       => 'Error'
    ];

    /**
     * @former kSuchanfrage
     * @var int
     */
    private int $id = 0;

    /**
     * @var int
     * @former kSuchCache
     */
    private int $searchCacheID = 0;

    /**
     * @var string|null
     */
    public ?string $error = null;

    /**
     * BaseSearchQuery constructor.
     *
     * @param ProductFilter $productFilter
     */
    public function __construct(ProductFilter $productFilter)
    {
        parent::__construct($productFilter);
        $this->setRouteType(Router::TYPE_SEARCH_QUERY);
        $this->setIsCustom(false)
            ->setUrlParam('suche')
            ->setUrlParamSEO(null);
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
     * @inheritdoc
     */
    public function setValue($value): FilterInterface
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setName($name): FilterInterface
    {
        $this->error = null;
        $minChars    = ($min = (int)$this->getConfig('artikeluebersicht')['suche_min_zeichen']) > 0
            ? $min
            : 3;
        if (\mb_strlen($name) > 0 || Request::getVar('qs') === '') {
            \preg_match(
                '/[\S]{' . $minChars . ',}/u',
                \str_replace(' ', '', $name),
                $hits
            );
            if (\count($hits) === 0) {
                $this->error = Shop::Lang()->get('expressionHasTo')
                    . ' ' . $minChars . ' ' . Shop::Lang()->get('lettersDigits');
            }
        }

        return parent::setName($name);
    }

    /**
     * @inheritdoc
     */
    public function getValue()
    {
        return ($this->productFilter->getRealSearch() !== null && !$this->productFilter->hasSearchQuery())
            ? \urlencode($this->productFilter->getRealSearch()->cSuche)
            : $this->value;
    }

    /**
     * @param int $id
     * @return $this
     */
    public function setID($id): FilterInterface
    {
        $this->id = (int)$id;

        return $this;
    }

    /**
     * @return int
     */
    public function getID(): int
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function getUrlParam(): string
    {
        return $this->productFilter->getRealSearch() !== null && !$this->productFilter->hasSearchQuery()
            ? 'suche'
            : parent::getUrlParam();
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
     * @inheritdoc
     */
    public function setSeo(array $languages): FilterInterface
    {
        $seo = $this->productFilter->getDB()->getSingleObject(
            "SELECT tseo.cSeo, tseo.kSprache, tsuchanfrage.cSuche
                FROM tseo
                LEFT JOIN tsuchanfrage
                    ON tsuchanfrage.kSuchanfrage = tseo.kKey
                    AND tsuchanfrage.kSprache = tseo.kSprache
                WHERE cKey = 'kSuchanfrage' 
                    AND kKey = :key",
            ['key' => $this->getID()]
        );
        foreach ($languages as $language) {
            $this->cSeo[$language->kSprache] = '';
            if ($seo !== null && $language->kSprache === (int)$seo->kSprache) {
                $this->setSlug($seo->cSeo, $language->kSprache);
            }
        }
        if ($seo === null) {
            return $this;
        }
        $this->createBySlug($this->getID());
        foreach ($this->getURLPaths() as $langID => $slug) {
            $this->cSeo[$langID] = \ltrim($slug, '/');
        }
        if (!empty($seo->cSuche)) {
            $this->setName($seo->cSuche);
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
        $searchCacheIDs = [];
        $searchFilter   = $this->productFilter->getBaseState();
        if (\is_array($searchFilter)) {
            $count = \count($searchFilter);
            foreach ($searchFilter as $item) {
                if ($item->getSearchCacheID() > 0) {
                    $searchCacheIDs[] = $item->getSearchCacheID();
                }
            }
        } elseif ($searchFilter->getSearchCacheID() > 0) {
            $searchCacheIDs[] = $searchFilter->getSearchCacheID();
            $count            = 1;
        } else {
            $searchCacheIDs = [$searchFilter->getValue()];
            $count          = 1;
        }

        return (new Join())
            ->setType('JOIN')
            ->setTable('(SELECT tsuchcachetreffer.kArtikel, tsuchcachetreffer.kSuchCache, 
                          MIN(tsuchcachetreffer.nSort) AS nSort
                              FROM tsuchcachetreffer
                              WHERE tsuchcachetreffer.kSuchCache IN (' . \implode(',', $searchCacheIDs) . ') 
                              #JOIN tsuchcache
                              #    ON tsuchcachetreffer.kSuchCache = tsuchcache.kSuchCache
                              #JOIN tsuchanfrage
                              #    ON tsuchanfrage.cSuche = tsuchcache.cSuche
                              #    AND tsuchanfrage.kSuchanfrage IN (' . \implode(',', $searchCacheIDs) . ') 
                              GROUP BY tsuchcachetreffer.kArtikel
                              HAVING COUNT(*) = ' . $count . '
                          ) AS jSuche')
            ->setOn('jSuche.kArtikel = tartikel.kArtikel')
            ->setComment('JOIN1 from ' . __METHOD__)
            ->setOrigin(__CLASS__);
    }

    /**
     * @inheritdoc
     */
    public function getOptions($mixed = null): array
    {
        if ($this->options !== null) {
            return $this->options;
        }
        $options  = [];
        $naviConf = $this->getConfig('navigationsfilter');
        if ($naviConf['suchtrefferfilter_nutzen'] === 'N') {
            return $options;
        }
        $max   = (int)($naviConf['suchtrefferfilter_anzahl'] ?? 0);
        $limit = $max > 0 ? (' LIMIT ' . $max) : '';

        $sql = (new StateSQL())->from($this->productFilter->getCurrentStateData());
        $sql->setSelect(['tsuchanfrage.kSuchanfrage', 'tsuchanfrage.cSuche', 'tartikel.kArtikel']);
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
        $cacheID   = $this->getCacheID($baseQuery);
        if (($searchFilters = $this->productFilter->getCache()->get($cacheID)) === false) {
            $searchFilters = $this->productFilter->getDB()->getObjects(
                'SELECT ssMerkmal.kSuchanfrage AS id, ssMerkmal.cSuche, COUNT(*) AS cnt
                FROM (' . $baseQuery . ') AS ssMerkmal
                    GROUP BY ssMerkmal.kSuchanfrage
                    ORDER BY ssMerkmal.cSuche' . $limit
            );
            foreach ($searchFilters as $searchFilter) {
                $searchFilter->id  = (int)$searchFilter->id;
                $searchFilter->cnt = (int)$searchFilter->cnt;
            }
            $this->productFilter->getCache()->set($cacheID, $options, [\CACHING_GROUP_FILTER]);
        }
        $searchQueryIDs = [];
        if ($this->productFilter->hasSearch()) {
            $searchQueryIDs[] = (int)$this->productFilter->getSearch()->getValue();
        }
        if ($this->productFilter->hasSearchFilter()) {
            foreach ($this->productFilter->getSearchFilter() as $searchFilter) {
                if ($searchFilter->getValue() > 0) {
                    $searchQueryIDs[] = (int)$searchFilter->getValue();
                }
            }
        }
        // entferne bereits gesetzte Filter aus dem Ergebnis-Array
        foreach ($searchFilters as $j => $searchFilter) {
            foreach ($searchQueryIDs as $searchQuery) {
                if ($searchFilter->id === $searchQuery) {
                    unset($searchFilters[$j]);
                    break;
                }
            }
        }
        if (\is_array($searchFilters)) {
            $searchFilters = \array_merge($searchFilters);
        }
        //baue URL
        $additionalFilter = new self($this->productFilter);
        // Priorität berechnen
        $nPrioStep = 0;
        $nCount    = \count($searchFilters);
        if ($nCount > 0) {
            $nPrioStep = ($searchFilters[0]->cnt - $searchFilters[$nCount - 1]->cnt) / 9;
        }
        foreach ($searchFilters as $searchFilter) {
            $fo = new Option();
            $fo->setURL($this->productFilter->getFilterURL()->getURL(
                $additionalFilter->init($searchFilter->id)
            ))
                ->setClass((string)\random_int(1, 10))
                ->setParam($this->getUrlParam())
                ->setType($this->getType())
                ->setClassName($this->getClassName())
                ->setName($searchFilter->cSuche)
                ->setValue($searchFilter->id)
                ->setCount($searchFilter->cnt);
            if (isset($searchFilter->kSuchCache) && $searchFilter->kSuchCache > 0 && $nPrioStep > 0) {
                $fo->setClass(
                    (string)(\round(
                        ($searchFilter->cnt - $searchFilters[$nCount - 1]->cnt) /
                            $nPrioStep
                    ) + 1)
                );
            }
            $options[] = $fo;
        }
        $this->options = $options;

        return $options;
    }

    /**
     * @param string $query
     * @param int    $langIDExt
     * @return string
     * @former mappingBeachten
     */
    private function getQueryMapping(string $query, int $langIDExt = 0): string
    {
        $langID = $langIDExt > 0
            ? $langIDExt
            : $this->getLanguageID();
        if (\mb_strlen($query) > 0) {
            $querymappingTMP = $this->productFilter->getDB()->select(
                'tsuchanfragemapping',
                'kSprache',
                $langID,
                'cSuche',
                $query
            );
            $querymapping    = $querymappingTMP;
            while ($querymappingTMP !== null && !empty($querymappingTMP->cSucheNeu)) {
                $querymappingTMP = $this->productFilter->getDB()->select(
                    'tsuchanfragemapping',
                    'kSprache',
                    $langID,
                    'cSuche',
                    $querymappingTMP->cSucheNeu
                );
                if (!empty($querymappingTMP->cSucheNeu)) {
                    $querymapping = $querymappingTMP;
                }
            }
            if ($querymapping !== null && !empty($querymapping->cSucheNeu)) {
                $query = $querymapping->cSucheNeu;
            }
        }

        return $query;
    }

    /**
     * @param int $langIDExt
     * @return int
     */
    public function editSearchCache(int $langIDExt = 0): int
    {
        // Mapping beachten
        $langID = $langIDExt > 0
            ? $langIDExt
            : $this->getLanguageID();
        $query  = $this->getQueryMapping($this->getName() ?? '', $langID);
        $this->setName($query);
        // Suchcache wurde zwar gefunden, ist jedoch nicht mehr gültig
        $this->productFilter->getDB()->query(
            'DELETE tsuchcache, tsuchcachetreffer
                FROM tsuchcache
                LEFT JOIN tsuchcachetreffer 
                    ON tsuchcachetreffer.kSuchCache = tsuchcache.kSuchCache
                WHERE tsuchcache.dGueltigBis IS NOT NULL
                    AND DATE_ADD(tsuchcache.dGueltigBis, INTERVAL 5 MINUTE) < NOW()'
        );
        // Suchcache checken, ob bereits vorhanden
        $searchCache = $this->productFilter->getDB()->getSingleInt(
            'SELECT kSuchCache
                FROM tsuchcache
                WHERE kSprache = :lang
                    AND cSuche = :search
                    AND (dGueltigBis > NOW() OR dGueltigBis IS NULL)',
            'kSuchCache',
            [
                'lang'   => $langID,
                'search' => $query
            ]
        );
        if ($searchCache > 0) {
            return $searchCache;
        }
        // wenn kein Suchcache vorhanden
        $minChars = ($min = (int)$this->getConfig('artikeluebersicht')['suche_min_zeichen']) > 0
            ? $min
            : 3;
        if (\mb_strlen($query) < $minChars) {
            $this->error = Shop::Lang()->get('searchQueryMinLength', 'messages', $minChars, $query);

            return 0;
        }
        // Suchausdruck aufbereiten
        $search = $this->prepareSearchQuery($query);
        $tmp    = $search;
        if (\count($search) === 0) {
            return 0;
        }
        // Array mit nach Prio sort. Suchspalten holen
        $rows                   = self::getSearchRows($this->getConfig());
        $cols                   = $this->getSearchColumnClasses($rows);
        $searchCache            = new stdClass();
        $searchCache->kSprache  = $langID;
        $searchCache->cSuche    = $query;
        $searchCache->dErstellt = 'NOW()';
        $cacheID                = $this->productFilter->getDB()->insert('tsuchcache', $searchCache);

        if ($this->getConfig('artikeluebersicht')['suche_fulltext'] !== 'N' && $this->isFulltextIndexActive()) {
            $searchCache->kSuchCache = $cacheID;

            return $this->editFullTextSearchCache(
                $searchCache,
                $rows,
                $search,
                $this->getConfig('artikeluebersicht')['suche_max_treffer'],
                $this->getConfig('artikeluebersicht')['suche_fulltext']
            );
        }

        if ($cacheID <= 0) {
            return 0;
        }
        $prep = ['lmt' => (int)$this->getConfig('artikeluebersicht')['suche_max_treffer']];

        if ($this->getLanguageID() > 0 && !LanguageHelper::isDefaultLanguageActive()) {
            $sql = 'SELECT ' . $cacheID . ', IF(tartikel.kVaterArtikel > 0, 
                        tartikel.kVaterArtikel, tartikel.kArtikel) AS kArtikelTMP, ';
        } else {
            $sql = 'SELECT ' . $cacheID . ', IF(kVaterArtikel > 0, 
                        kVaterArtikel, kArtikel) AS kArtikelTMP, ';
        }
        // mehr als 3 Suchwörter
        if (\count($search) > 3) {
            $sql .= ' 1 ';
            if ($this->getLanguageID() > 0 && !LanguageHelper::isDefaultLanguageActive()) {
                $prep['lid'] = $this->getLanguageID();

                $sql .= ' FROM tartikel
                          LEFT JOIN tartikelsprache
                              ON tartikelsprache.kArtikel = tartikel.kArtikel
                              AND tartikelsprache.kSprache = :lid';
            } else {
                $sql .= ' FROM tartikel ';
            }
            $sql .= ' WHERE ';

            foreach ($rows as $i => $col) {
                if ($i > 0) {
                    $sql .= ' OR';
                }
                $sql .= '(';
                foreach ($tmp as $j => $cSuch) {
                    $idx = 'qry' . $j;
                    if ($j > 0) {
                        $sql .= ' AND';
                    }
                    $sql       .= ' ' . $col . ' LIKE :' . $idx;
                    $prep[$idx] = '%' . $cSuch . '%';
                }
                $sql .= ')';
            }
        } else {
            $brackets = 0;
            $prio     = 1;
            foreach ($rows as $i => $col) {
                // Fülle bei 1, 2 oder 3 Suchwörtern aufsplitten
                switch (\count($tmp)) {
                    case 1: // Fall 1, nur ein Suchwort
                        // "A"
                        $nonAllowed = [2];
                        if ($this->checkColumnClasses($cols, $col, $nonAllowed)) {
                            $prep['sq0'] = $tmp[0];
                            ++$brackets;
                            $sql .= 'IF(' . $col . ' = :sq0, ' . ++$prio . ', ';
                        }
                        // "A_%"
                        $nonAllowed = [2, 3];
                        if ($this->checkColumnClasses($cols, $col, $nonAllowed)) {
                            $prep['sq1'] = $tmp[0] . ' %';
                            ++$brackets;
                            $sql .= 'IF(' . $col . ' LIKE :sq1, ' . ++$prio . ', ';
                        }
                        // "%_A_%"
                        $nonAllowed = [3];
                        if ($this->checkColumnClasses($cols, $col, $nonAllowed)) {
                            $prep['sq2'] = '% ' . $tmp[0] . ' %';
                            ++$brackets;
                            $sql .= 'IF(' . $col . ' LIKE :sq2, ' . ++$prio . ', ';
                        }
                        // "%_A"
                        $nonAllowed = [2, 3];
                        if ($this->checkColumnClasses($cols, $col, $nonAllowed)) {
                            $prep['sq3'] = '% ' . $tmp[0];
                            ++$brackets;
                            $sql .= 'IF(' . $col . ' LIKE :sq3, ' . ++$prio . ', ';
                        }
                        // "%_A%"
                        $nonAllowed = [3];
                        if ($this->checkColumnClasses($cols, $col, $nonAllowed)) {
                            $prep['sq4'] = '% ' . $tmp[0] . '%';
                            ++$brackets;
                            $sql .= 'IF(' . $col . ' LIKE :sq4, ' . ++$prio . ', ';
                        }
                        // "%A_%"
                        $nonAllowed = [3];
                        if ($this->checkColumnClasses($cols, $col, $nonAllowed)) {
                            $prep['sq5'] = '%' . $tmp[0] . ' %';
                            ++$brackets;
                            $sql .= 'IF(' . $col . ' LIKE :sq5, ' . ++$prio . ', ';
                        }
                        // "A%"
                        $nonAllowed = [2, 3];
                        if ($this->checkColumnClasses($cols, $col, $nonAllowed)) {
                            $prep['sq6'] = $tmp[0] . '%';
                            ++$brackets;
                            $sql .= 'IF(' . $col . ' LIKE :sq6, ' . ++$prio . ', ';
                        }
                        // "%A"
                        $nonAllowed = [2, 3];
                        if ($this->checkColumnClasses($cols, $col, $nonAllowed)) {
                            $prep['sq7'] = '%' . $tmp[0];
                            ++$brackets;
                            $sql .= 'IF(' . $col . ' LIKE :sq7, ' . ++$prio . ', ';
                        }
                        // "%A%"
                        $nonAllowed = [3];
                        if ($this->checkColumnClasses($cols, $col, $nonAllowed)) {
                            $prep['sq8'] = '%' . $tmp[0] . '%';
                            ++$brackets;
                            $sql .= 'IF(' . $col . ' LIKE :sq8, ' . ++$prio . ', ';
                        }
                        break;
                    case 2: // Fall 2, zwei Suchwörter
                        // "A_B"
                        $nonAllowed = [2];
                        if ($this->checkColumnClasses($cols, $col, $nonAllowed)) {
                            $prep['sq0'] = $tmp[0] . ' ' . $tmp[1];
                            ++$brackets;
                            $sql .= 'IF(' . $col . ' LIKE :sq0, ' . ++$prio . ', ';
                        }
                        // "B_A"
                        $nonAllowed = [2, 3];
                        if ($this->checkColumnClasses($cols, $col, $nonAllowed)) {
                            $prep['sq1'] = $tmp[1] . ' ' . $tmp[0];
                            ++$brackets;
                            $sql .= 'IF(' . $col . ' LIKE :sq1, ' . ++$prio . ', ';
                        }
                        // "A_B_%"
                        $nonAllowed = [2, 3];
                        if ($this->checkColumnClasses($cols, $col, $nonAllowed)) {
                            $prep['sq2'] = $tmp[0] . ' ' . $tmp[1] . ' %';
                            ++$brackets;
                            $sql .= 'IF(' . $col . ' LIKE :sq2, ' . ++$prio . ', ';
                        }
                        // "B_A_%"
                        $nonAllowed = [2, 3];
                        if ($this->checkColumnClasses($cols, $col, $nonAllowed)) {
                            $prep['sq3'] = $tmp[1] . ' ' . $tmp[0] . ' %';
                            ++$brackets;
                            $sql .= 'IF(' . $col . ' LIKE :sq3, ' . ++$prio . ', ';
                        }
                        // "%_A_B"
                        $nonAllowed = [2, 3];
                        if ($this->checkColumnClasses($cols, $col, $nonAllowed)) {
                            $prep['sq4'] = '% ' . $tmp[0] . ' ' . $tmp[1];
                            ++$brackets;
                            $sql .= 'IF(' . $col . ' LIKE :sq4, ' . ++$prio . ', ';
                        }
                        // "%_B_A"
                        $nonAllowed = [2, 3];
                        if ($this->checkColumnClasses($cols, $col, $nonAllowed)) {
                            $prep['sq5'] = '% ' . $tmp[1] . ' ' . $tmp[0];
                            ++$brackets;
                            $sql .= 'IF(' . $col . ' LIKE :sq5, ' . ++$prio . ', ';
                        }
                        // "%_A_B_%"
                        $nonAllowed = [3];
                        if ($this->checkColumnClasses($cols, $col, $nonAllowed)) {
                            $prep['sq6'] = '% ' . $tmp[0] . ' ' . $tmp[1] . ' %';
                            ++$brackets;
                            $sql .= 'IF(' . $col . ' LIKE :sq6, ' . ++$prio . ', ';
                        }
                        // "%_B_A_%"
                        $nonAllowed = [3];
                        if ($this->checkColumnClasses($cols, $col, $nonAllowed)) {
                            $prep['sq7'] = '% ' . $tmp[1] . ' ' . $tmp[0] . ' %';
                            ++$brackets;
                            $sql .= 'IF(' . $col . ' LIKE :sq7, ' . ++$prio . ', ';
                        }
                        // "%A_B_%"
                        $nonAllowed = [3];
                        if ($this->checkColumnClasses($cols, $col, $nonAllowed)) {
                            $prep['sq8'] = '%' . $tmp[0] . ' ' . $tmp[1] . ' %';
                            ++$brackets;
                            $sql .= 'IF(' . $col . ' LIKE :sq8, ' . ++$prio . ', ';
                        }
                        // "%B_A_%"
                        $nonAllowed = [3];
                        if ($this->checkColumnClasses($cols, $col, $nonAllowed)) {
                            $prep['sq9'] = '%' . $tmp[1] . ' ' . $tmp[0] . ' %';
                            ++$brackets;
                            $sql .= 'IF(' . $col . ' LIKE :sq9, ' . ++$prio . ', ';
                        }
                        // "%_A_B%"
                        $nonAllowed = [3];
                        if ($this->checkColumnClasses($cols, $col, $nonAllowed)) {
                            $prep['sq10'] = '% ' . $tmp[0] . ' ' . $tmp[1] . '%';
                            ++$brackets;
                            $sql .= 'IF(' . $col . ' LIKE :sq10, ' . ++$prio . ', ';
                        }
                        // "%_B_A%"
                        $nonAllowed = [3];
                        if ($this->checkColumnClasses($cols, $col, $nonAllowed)) {
                            $prep['sq11'] = '% ' . $tmp[1] . ' ' . $tmp[0] . '%';
                            ++$brackets;
                            $sql .= 'IF(' . $col . ' LIKE :sq11, ' . ++$prio . ', ';
                        }
                        // "%A_B%"
                        $nonAllowed = [3];
                        if ($this->checkColumnClasses($cols, $col, $nonAllowed)) {
                            $prep['sq12'] = '%' . $tmp[0] . ' ' . $tmp[1] . '%';
                            ++$brackets;
                            $sql .= 'IF(' . $col . ' LIKE :sq12, ' . ++$prio . ', ';
                        }
                        // "%B_A%"
                        $nonAllowed = [3];
                        if ($this->checkColumnClasses($cols, $col, $nonAllowed)) {
                            $prep['sq13'] = '%' . $tmp[1] . ' ' . $tmp[0] . '%';
                            ++$brackets;
                            $sql .= 'IF(' . $col . ' LIKE :sq13, ' . ++$prio . ', ';
                        }
                        // "%_A%_B_%"
                        $nonAllowed = [3];
                        if ($this->checkColumnClasses($cols, $col, $nonAllowed)) {
                            $prep['sq14'] = '% ' . $tmp[0] . '% ' . $tmp[1] . ' %';
                            ++$brackets;
                            $sql .= 'IF(' . $col . ' LIKE :sq14, ' . ++$prio . ', ';
                        }
                        // "%_B%_A_%"
                        $nonAllowed = [3];
                        if ($this->checkColumnClasses($cols, $col, $nonAllowed)) {
                            $prep['sq15'] = '% ' . $tmp[1] . '% ' . $tmp[0] . ' %';
                            ++$brackets;
                            $sql .= 'IF(' . $col . ' LIKE :sq15, ' . ++$prio . ', ';
                        }
                        // "%_A_%B_%"
                        $nonAllowed = [3];
                        if ($this->checkColumnClasses($cols, $col, $nonAllowed)) {
                            $prep['sq16'] = '% ' . $tmp[0] . ' %' . $tmp[1] . ' %';
                            ++$brackets;
                            $sql .= 'IF(' . $col . ' LIKE :sq16, ' . ++$prio . ', ';
                        }
                        // "%_B_%A_%"
                        $nonAllowed = [3];
                        if ($this->checkColumnClasses($cols, $col, $nonAllowed)) {
                            $prep['sq17'] = '% ' . $tmp[1] . ' %' . $tmp[0] . ' %';
                            ++$brackets;
                            $sql .= 'IF(' . $col . ' LIKE :sq17, ' . ++$prio . ', ';
                        }
                        // "%_A%_%B_%"
                        $nonAllowed = [2, 3];
                        if ($this->checkColumnClasses($cols, $col, $nonAllowed)) {
                            $prep['sq18'] = '% ' . $tmp[0] . '% %' . $tmp[1] . ' %';
                            ++$brackets;
                            $sql .= 'IF(' . $col . ' LIKE :sq18, ' . ++$prio . ', ';
                        }
                        // "%_B%_%A_%"
                        $nonAllowed = [2, 3];
                        if ($this->checkColumnClasses($cols, $col, $nonAllowed)) {
                            $prep['sq19'] = '% ' . $tmp[1] . '% %' . $tmp[0] . ' %';
                            ++$brackets;
                            $sql .= 'IF(' . $col . ' LIKE :sq19, ' . ++$prio . ', ';
                        }
                        break;
                    case 3: // Fall 3, drei Suchwörter
                        // "%A_%_B_%_C%"
                        $nonAllowed = [3];
                        if ($this->checkColumnClasses($cols, $col, $nonAllowed)) {
                            $prep['sq0'] = '%' . $tmp[0] . ' % ' . $tmp[1] . ' % ' . $tmp[2] . '%';
                            ++$brackets;
                            $sql .= 'IF(' . $col . ' LIKE :sq0, ' . ++$prio . ', ';
                        }
                        // "%_A_% AND %_B_% AND %_C_%"
                        $nonAllowed = [3];
                        if ($this->checkColumnClasses($cols, $col, $nonAllowed)) {
                            $prep['sq1'] = '% ' . $tmp[0] . ' %';
                            $prep['sq2'] = '% ' . $tmp[1] . ' %';
                            $prep['sq3'] = '% ' . $tmp[2] . ' %';
                            ++$brackets;
                            $sql .= 'IF((' . $col . ' LIKE :sq1) 
                                AND (' . $col . ' LIKE :sq2) 
                                AND (' . $col . ' LIKE :sq3), ' . ++$prio . ', ';
                        }
                        // "%_A_% AND %_B_% AND %C%"
                        $nonAllowed = [3];
                        if ($this->checkColumnClasses($cols, $col, $nonAllowed)) {
                            $prep['sq4'] = $tmp[0];
                            $prep['sq5'] = $tmp[1];
                            $prep['sq6'] = '%' . $tmp[2] . '%';
                            ++$brackets;
                            $sql .= 'IF((' . $col . ' LIKE :sq4) 
                                AND (' . $col . ' LIKE :sq5) 
                                AND (' . $col . ' LIKE :sq6), ' . ++$prio . ', ';
                        }
                        // "%_A_% AND %B% AND %_C_%"
                        $nonAllowed = [3];
                        if ($this->checkColumnClasses($cols, $col, $nonAllowed)) {
                            $prep['sq7'] = '% ' . $tmp[0] . ' %';
                            $prep['sq8'] = '%' . $tmp[1] . '%';
                            $prep['sq9'] = '% ' . $tmp[2] . ' %';
                            ++$brackets;
                            $sql .= 'IF((' . $col . ' LIKE :sq7) 
                                AND (' . $col . ' LIKE :sq8) 
                                AND (' . $col . ' LIKE :sq9), ' . ++$prio . ', ';
                        }
                        // "%_A_% AND %B% AND %C%"
                        $nonAllowed = [3];
                        if ($this->checkColumnClasses($cols, $col, $nonAllowed)) {
                            $prep['sq10'] = '% ' . $tmp[0] . ' %';
                            $prep['sq11'] = '%' . $tmp[1] . '%';
                            $prep['sq12'] = '%' . $tmp[2] . '%';
                            ++$brackets;
                            $sql .= 'IF((' . $col . ' LIKE :sq10) 
                                AND (' . $col . ' LIKE :sq11) 
                                AND (' . $col . ' LIKE :sq12), ' . ++$prio . ', ';
                        }
                        // "%A% AND %_B_% AND %_C_%"
                        $nonAllowed = [3];
                        if ($this->checkColumnClasses($cols, $col, $nonAllowed)) {
                            $prep['sq13'] = '%' . $tmp[0] . '%';
                            $prep['sq14'] = '% ' . $tmp[1] . ' %';
                            $prep['sq15'] = '% ' . $tmp[2] . ' %';
                            ++$brackets;
                            $sql .= 'IF((' . $col . ' LIKE :sq13) 
                                AND (' . $col . ' LIKE :sq14) 
                                AND (' . $col . ' LIKE :sq15), ' . ++$prio . ', ';
                        }
                        // "%A% AND %_B_% AND %C%"
                        $nonAllowed = [3];
                        if ($this->checkColumnClasses($cols, $col, $nonAllowed)) {
                            $prep['sq16'] = '%' . $tmp[0] . '%';
                            $prep['sq17'] = '% ' . $tmp[1] . ' %';
                            $prep['sq18'] = '%' . $tmp[2] . '%';
                            ++$brackets;
                            $sql .= 'IF((' . $col . ' LIKE :sq16) 
                                AND (' . $col . ' LIKE :sq17) 
                                AND (' . $col . ' LIKE :sq18), ' . ++$prio . ', ';
                        }
                        // "%A% AND %B% AND %_C_%"
                        $nonAllowed = [3];
                        if ($this->checkColumnClasses($cols, $col, $nonAllowed)) {
                            $prep['sq19'] = '%' . $tmp[0] . '%';
                            $prep['sq20'] = '%' . $tmp[1] . '%';
                            $prep['sq21'] = '% ' . $tmp[2] . ' %';
                            ++$brackets;
                            $sql .= 'IF((' . $col . ' LIKE :sq19) 
                                AND (' . $col . ' LIKE :sq20) 
                                AND (' . $col . ' LIKE :sq21), ' . ++$prio . ', ';
                        }
                        // "%A%B%C%"
                        $nonAllowed = [3];
                        if ($this->checkColumnClasses($cols, $col, $nonAllowed)) {
                            $prep['sq22'] = '%' . $tmp[0] . '%' . $tmp[1] . '%' . $tmp[2] . '%';
                            ++$brackets;
                            $sql .= 'IF(' . $col . ' LIKE :sq22, ' . ++$prio . ', ';
                        }
                        // "%A% AND %B% AND %C%"
                        $nonAllowed = [3];
                        if ($this->checkColumnClasses($cols, $col, $nonAllowed)) {
                            $prep['sq23'] = '%' . $tmp[0] . '%';
                            $prep['sq24'] = '%' . $tmp[1] . '%';
                            $prep['sq25'] = '%' . $tmp[2] . '%';
                            ++$brackets;
                            $sql .= 'IF((' . $col . ' LIKE :sq23) 
                                AND (' . $col . ' LIKE :sq24) 
                                AND (' . $col . ' LIKE :sq25), ' . ++$prio . ', ';
                        }
                        break;
                }

                if ($i === (\count($rows) - 1)) {
                    $sql .= '254)';
                }
            }

            $sql .= \str_repeat(')', ($brackets - 1));

            if ($this->getLanguageID() > 0 && !LanguageHelper::isDefaultLanguageActive()) {
                $prep['lid'] = $this->getLanguageID();
                $sql        .= ' FROM tartikel
                            LEFT JOIN tartikelsprache
                                ON tartikelsprache.kArtikel = tartikel.kArtikel
                                AND tartikelsprache.kSprache = :lid';
            } else {
                $sql .= ' FROM tartikel ';
            }
            $sql .= ' WHERE ';

            foreach ($rows as $i => $col) {
                if ($i > 0) {
                    $sql .= ' OR';
                }
                $sql .= '(';

                foreach ($tmp as $j => $cSuch) {
                    $idx = 'qry' . $j;
                    if ($j > 0) {
                        $sql .= ' AND';
                    }
                    $sql       .= ' ' . $col . ' LIKE :' . $idx;
                    $prep[$idx] = '%' . $cSuch . '%';
                }
                $sql .= ')';
            }
        }
        $this->productFilter->getDB()->queryPrepared(
            'INSERT INTO tsuchcachetreffer ' .
            $sql .
            ' GROUP BY kArtikelTMP
                LIMIT :lmt',
            $prep
        );

        return $cacheID;
    }

    /**
     * @param string $query
     * @return array
     */
    public function prepareSearchQuery(string $query): array
    {
        $query       = \str_replace(["'", '\\', '*', '%'], '', \strip_tags($query));
        $searchArray = [];
        $parts       = \explode(' ', $query);
        $stripped    = \stripslashes($query);
        if ($stripped[0] !== '"' || $stripped[\mb_strlen($stripped) - 1] !== '"') {
            foreach ($parts as $searchString) {
                if (\str_contains($searchString, '+')) {
                    $searchPart = \explode('+', $searchString);
                    foreach ($searchPart as $part) {
                        $part = \trim($part);
                        if ($part) {
                            $searchArray[] = $part;
                        }
                    }
                } else {
                    $searchString = \trim($searchString);
                    if ($searchString) {
                        $searchArray[] = $searchString;
                    }
                }
            }
        } else {
            $searchArray[] = \str_replace('"', '', $stripped);
        }

        return $searchArray;
    }

    /**
     * @param stdClass $searchCache
     * @param array    $searchCols
     * @param array    $searchQueries
     * @param int      $limit
     * @param string   $fullText
     * @return int
     * @former bearbeiteSuchCacheFulltext
     */
    private function editFullTextSearchCache(
        stdClass $searchCache,
        array    $searchCols,
        array    $searchQueries,
        int      $limit = 0,
        string   $fullText = 'Y'
    ): int {
        $searchCache->kSuchCache = (int)$searchCache->kSuchCache;
        if ($searchCache->kSuchCache <= 0) {
            return $searchCache->kSuchCache;
        }
        $productCols = \array_map(static function ($item): string {
            $items = \explode('.', $item, 2);

            return 'tartikel.' . $items[1];
        }, $searchCols);

        $langCols = \array_filter($searchCols, static function ($item) {
            return (bool)\preg_match('/tartikelsprache\.(.*)/', $item);
        });

        $score = 'MATCH (' . \implode(', ', $productCols) . ")
                    AGAINST ('" . \implode(' ', $searchQueries) . "' IN NATURAL LANGUAGE MODE) ";
        if ($fullText === 'B') {
            $match = 'MATCH (' . \implode(', ', $productCols) . ")
                    AGAINST ('" . \implode('* ', $searchQueries) . "*' IN BOOLEAN MODE) ";
        } else {
            $match = $score;
        }

        $sql = 'SELECT ' . $searchCache->kSuchCache . ' AS kSuchCache,
                IF(tartikel.kVaterArtikel > 0, tartikel.kVaterArtikel, tartikel.kArtikel) AS kArtikelTMP, '
            . $score . ' AS score
                FROM tartikel
                WHERE ' . $match . $this->productFilter->getFilterSQL()->getStockFilterSQL() . ' ';

        if (Shop::getLanguageID() > 0 && !LanguageHelper::isDefaultLanguageActive()) {
            $score = 'MATCH (' . \implode(', ', $langCols) . ")
                        AGAINST ('" . \implode(' ', $searchQueries) . "' IN NATURAL LANGUAGE MODE)";
            if ($fullText === 'B') {
                $score = 'MATCH (' . \implode(', ', $langCols) . ")
                        AGAINST ('" . \implode('* ', $searchQueries) . "*' IN BOOLEAN MODE)";
            } else {
                $match = $score;
            }
            $sql .= 'UNION DISTINCT
            SELECT ' . $searchCache->kSuchCache . ' AS kSuchCache,
                IF(tartikel.kVaterArtikel > 0, tartikel.kVaterArtikel, tartikel.kArtikel) AS kArtikelTMP, '
                . $score . ' AS score
                FROM tartikel
                INNER JOIN tartikelsprache ON tartikelsprache.kArtikel = tartikel.kArtikel
                WHERE ' . $match . $this->productFilter->getFilterSQL()->getStockFilterSQL() . ' ';
        }

        $this->productFilter->getDB()->query(
            'INSERT INTO tsuchcachetreffer
                    SELECT kSuchCache, kArtikelTMP, ROUND(MAX(score) * -10)
                    FROM ( ' . $sql . ' ) AS i
                    LEFT JOIN tartikelsichtbarkeit 
                        ON tartikelsichtbarkeit.kArtikel = i.kArtikelTMP
                        AND tartikelsichtbarkeit.kKundengruppe = ' . Frontend::getCustomerGroup()->getID() . '
                    WHERE tartikelsichtbarkeit.kKundengruppe IS NULL
                    GROUP BY kSuchCache, kArtikelTMP' . ($limit > 0 ? ' LIMIT ' . $limit : '')
        );

        return $searchCache->kSuchCache;
    }

    /**
     * @param array $searchCols
     * @return array
     */
    public function getSearchColumnClasses(array $searchCols): array
    {
        $result = [];
        foreach ($searchCols as $columns) {
            // Klasse 1: Artikelname und Artikel SEO
            if (\str_contains($columns, 'cName')
                || \str_contains($columns, 'cSeo')
                || \str_contains($columns, 'cSuchbegriffe')
            ) {
                $result[1][] = $columns;
            }
            // Klasse 2: Artikelname und Artikel SEO
            if (\str_contains($columns, 'cKurzBeschreibung')
                || \str_contains($columns, 'cBeschreibung')
                || \str_contains($columns, 'cAnmerkung')
            ) {
                $result[2][] = $columns;
            }
            // Klasse 3: Artikelname und Artikel SEO
            if (\str_contains($columns, 'cArtNr')
                || \str_contains($columns, 'cBarcode')
                || \str_contains($columns, 'cISBN')
                || \str_contains($columns, 'cHAN')
            ) {
                $result[3][] = $columns;
            }
        }

        return $result;
    }

    /**
     * @param array  $searchCols
     * @param string $searchCol
     * @param array  $nonAllowed
     * @return bool
     */
    public function checkColumnClasses(array $searchCols, string $searchCol, array $nonAllowed): bool
    {
        if (\count($searchCols) > 0
            && \mb_strlen($searchCol) > 0
            && \count($nonAllowed) > 0
        ) {
            foreach ($nonAllowed as $class) {
                if (isset($searchCols[$class]) && \count($searchCols[$class]) > 0) {
                    foreach ($searchCols[$class] as $searchColumnnKlasse) {
                        if ($searchColumnnKlasse === $searchCol) {
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
            $active = $this->productFilter->getDB()->getSingleObject(
                "SHOW INDEX FROM tartikel 
                    WHERE KEY_NAME = 'idx_tartikel_fulltext'"
            )
                && $this->productFilter->getDB()->getSingleObject(
                    "SHOW INDEX 
                        FROM tartikelsprache 
                        WHERE KEY_NAME = 'idx_tartikelsprache_fulltext'"
                );
        }

        return $active;
    }

    /**
     * @param array|null $config
     * @return array
     * @former gibSuchSpalten()
     */
    public static function getSearchRows(array $config = null): array
    {
        $searchRows = [];
        $config     = $config ?? Shop::getSettings([\CONF_ARTIKELUEBERSICHT]);
        for ($i = 0; $i < 10; ++$i) {
            $searchRows[] = self::getPrioritizedRows($searchRows, $config);
        }

        return filter($searchRows, static function ($r): bool {
            return $r !== '';
        });
    }

    /**
     * @param array      $exclude
     * @param array|null $conf
     * @return string
     * @former gibMaxPrioSpalte()
     */
    public static function getPrioritizedRows(array $exclude, array $conf = null): string
    {
        $max     = 0;
        $current = '';
        $prefix  = 'tartikel.';
        $conf    = $conf['artikeluebersicht'] ?? Shop::getSettingSection(\CONF_ARTIKELUEBERSICHT);
        if (!LanguageHelper::isDefaultLanguageActive()) {
            $prefix = 'tartikelsprache.';
        }
        if ($conf['suche_prio_name'] > $max && !\in_array($prefix . 'cName', $exclude, true)) {
            $max     = $conf['suche_prio_name'];
            $current = $prefix . 'cName';
        }
        if ($conf['suche_prio_name'] > $max && !\in_array($prefix . 'cSeo', $exclude, true)) {
            $max     = $conf['suche_prio_name'];
            $current = $prefix . 'cSeo';
        }
        if ($conf['suche_prio_suchbegriffe'] > $max && !\in_array('tartikel.cSuchbegriffe', $exclude, true)) {
            $max     = $conf['suche_prio_suchbegriffe'];
            $current = 'tartikel.cSuchbegriffe';
        }
        if ($conf['suche_prio_artikelnummer'] > $max && !\in_array('tartikel.cArtNr', $exclude, true)) {
            $max     = $conf['suche_prio_artikelnummer'];
            $current = 'tartikel.cArtNr';
        }
        if ($conf['suche_prio_kurzbeschreibung'] > $max && !\in_array($prefix . 'cKurzBeschreibung', $exclude, true)) {
            $max     = $conf['suche_prio_kurzbeschreibung'];
            $current = $prefix . 'cKurzBeschreibung';
        }
        if ($conf['suche_prio_beschreibung'] > $max && !\in_array($prefix . 'cBeschreibung', $exclude, true)) {
            $max     = $conf['suche_prio_beschreibung'];
            $current = $prefix . 'cBeschreibung';
        }
        if ($conf['suche_prio_ean'] > $max && !\in_array('tartikel.cBarcode', $exclude, true)) {
            $max     = $conf['suche_prio_ean'];
            $current = 'tartikel.cBarcode';
        }
        if ($conf['suche_prio_isbn'] > $max && !\in_array('tartikel.cISBN', $exclude, true)) {
            $max     = $conf['suche_prio_isbn'];
            $current = 'tartikel.cISBN';
        }
        if ($conf['suche_prio_han'] > $max && !\in_array('tartikel.cHAN', $exclude, true)) {
            $max     = $conf['suche_prio_han'];
            $current = 'tartikel.cHAN';
        }
        if ($conf['suche_prio_anmerkung'] > $max && !\in_array('tartikel.cAnmerkung', $exclude, true)) {
            $current = 'tartikel.cAnmerkung';
        }

        return $current;
    }
}
