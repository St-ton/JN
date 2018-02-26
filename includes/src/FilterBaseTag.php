<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class FilterBaseTag
 */
class FilterBaseTag extends AbstractFilter
{
    use MagicCompatibilityTrait;

    /**
     * @var array
     */
    private static $mapping = [
        'kTag'  => 'ValueCompat',
        'cName' => 'Name'
    ];

    /**
     * FilterBaseTag constructor.
     *
     * @param ProductFilter $productFilter
     */
    public function __construct(ProductFilter $productFilter)
    {
        parent::__construct($productFilter);
        $this->setFrontendName(Shop::Lang()->get('tags'))
             ->setIsCustom(false)
             ->setUrlParam('t');
    }

    /**
     * @param int $value
     * @return $this
     */
    public function setValue($value)
    {
        return parent::setValue((int)$value);
    }

    /**
     * @param array $languages
     * @return $this
     */
    public function setSeo($languages)
    {
        $oSeo_obj = Shop::DB()->queryPrepared(
            "SELECT tseo.cSeo, tseo.kSprache, ttag.cName
                FROM tseo
                LEFT JOIN ttag
                    ON tseo.kKey = ttag.kTag
                WHERE tseo.cKey = 'kTag' 
                    AND tseo.kKey = :val",
            ['val' => $this->getValue()],
            1
        );
        foreach ($languages as $language) {
            $this->cSeo[$language->kSprache] = '';
            if (isset($oSeo_obj->kSprache) && $language->kSprache === (int)$oSeo_obj->kSprache) {
                $this->cSeo[$language->kSprache] = $oSeo_obj->cSeo;
            }
        }
        if (!empty($oSeo_obj->cName)) {
            $this->setName($oSeo_obj->cName);
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getPrimaryKeyRow()
    {
        return 'kTag';
    }

    /**
     * @return string
     */
    public function getTableName()
    {
        return 'ttag';
    }

    /**
     * @return string
     */
    public function getSQLCondition()
    {
        return 'ttag.nAktiv = 1 AND ttagartikel.kTag = ' . $this->getValue();
    }

    /**
     * @return FilterJoin[]
     */
    public function getSQLJoin()
    {
        return [
            (new FilterJoin())
                ->setType('JOIN')
                ->setTable('ttagartikel')
                ->setOn('tartikel.kArtikel = ttagartikel.kArtikel')
                ->setComment('JOIN1 from ' . __METHOD__)
                ->setOrigin(__CLASS__),
            (new FilterJoin())
                ->setType('JOIN')
                ->setTable('ttag')
                ->setOn('ttagartikel.kTag = ttag.kTag')
                ->setComment('JOIN2 from ' . __METHOD__)
                ->setOrigin(__CLASS__)
        ];
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
        if ($this->getConfig()['navigationsfilter']['allgemein_tagfilter_benutzen'] !== 'N') {
            $joinedTables = [];
            $state        = $this->productFilter->getCurrentStateData();

            $state->joins[] = (new FilterJoin())
                ->setComment('join1 from ' . __METHOD__)
                ->setType('JOIN')
                ->setTable('ttagartikel')
                ->setOn('ttagartikel.kArtikel = tartikel.kArtikel')
                ->setOrigin(__CLASS__);
            $state->joins[] = (new FilterJoin())
                ->setComment('join2 from ' . __METHOD__)
                ->setType('JOIN')
                ->setTable('ttag')
                ->setOn('ttagartikel.kTag = ttag.kTag')
                ->setOrigin(__CLASS__);
            // remove duplicate joins
            foreach ($state->joins as $i => $stateJoin) {
                if (!in_array($stateJoin->getTable(), $joinedTables, true)) {
                    $joinedTables[] = $stateJoin->getTable();
                } else {
                    unset($state->joins[$i]);
                }
            }
            $state->conditions[] = 'ttag.nAktiv = 1';
            $state->conditions[] = 'ttag.kSprache = ' . $this->getLanguageID();
            $query               = $this->productFilter->getFilterSQL()->getBaseQuery(
                ['ttag.kTag',
                'ttag.cName',
                'ttagartikel.nAnzahlTagging',
                'tartikel.kArtikel'],
                $state->joins,
                $state->conditions,
                $state->having,
                null,
                '',
                ['ttag.kTag', 'tartikel.kArtikel']
            );
            $tags                = Shop::DB()->query(
                "SELECT tseo.cSeo, ssMerkmal.kTag, ssMerkmal.cName, 
                    COUNT(*) AS nAnzahl, SUM(ssMerkmal.nAnzahlTagging) AS nAnzahlTagging
                        FROM (" . $query . ") AS ssMerkmal
                    LEFT JOIN tseo ON tseo.kKey = ssMerkmal.kTag
                        AND tseo.cKey = 'kTag'
                        AND tseo.kSprache = " . $this->getLanguageID() . "
                    GROUP BY ssMerkmal.kTag
                    ORDER BY nAnzahl DESC LIMIT 0, " .
                    (int)$this->getConfig()['navigationsfilter']['tagfilter_max_anzeige'],
                NiceDB::RET_ARRAY_OF_OBJECTS
            );
            $additionalFilter = new FilterItemTag($this->productFilter);
            // Priorität berechnen
            $nPrioStep = 0;
            $nCount    = count($tags);
            if ($nCount > 0) {
                $nPrioStep = ($tags[0]->nAnzahlTagging - $tags[$nCount - 1]->nAnzahlTagging) / 9;
            }
            foreach ($tags as $tag) {
                $fo                 = (new FilterOption())
                    ->setType($this->getType())
                    ->setClassName($this->getClassName())
                    ->setParam($this->getUrlParam())
                    ->setName($tag->cName)
                    ->setValue((int)$tag->kTag)
                    ->setCount($tag->nAnzahl)
                    ->setURL($this->productFilter->getFilterURL()->getURL(
                        $additionalFilter->init((int)$tag->kTag)
                    ));
                $fo->nAnzahlTagging = (int)$tag->nAnzahlTagging;
                // generic attributes for new filter templates
                $class = $nPrioStep < 1
                    ? rand(1, 10)
                    : round(
                        ($fo->nAnzahlTagging - $tags[$nCount - 1]->nAnzahlTagging) /
                        $nPrioStep
                    ) + 1;
                $options[] = $fo->setClass($class);
            }
        }
        $this->options = $options;

        return $options;
    }
}
