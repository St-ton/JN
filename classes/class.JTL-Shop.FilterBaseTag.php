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
    /**
     * @var int
     */
    public $kTag = 0;

    /**
     * FilterBaseTag constructor.
     *
     * @param Navigationsfilter $naviFilter
     */
    public function __construct($naviFilter)
    {
        parent::__construct($naviFilter);
        $this->isCustom    = false;
        $this->urlParam    = 't';
    }

    /**
     * @param int $id
     * @return $this
     */
    public function setValue($id)
    {
        $this->kTag = (int)$id;

        return $this;
    }

    /**
     * @return int
     */
    public function getValue()
    {
        return $this->kTag;
    }

    /**
     * @param array $languages
     * @return $this
     */
    public function setSeo($languages)
    {
        $oSeo_obj = Shop::DB()->query("
                SELECT tseo.cSeo, tseo.kSprache, ttag.cName
                    FROM tseo
                    LEFT JOIN ttag
                        ON tseo.kKey = ttag.kTag
                    WHERE tseo.cKey = 'kTag' 
                        AND tseo.kKey = " . $this->getValue(), 1
        );
        foreach ($languages as $language) {
            $this->cSeo[$language->kSprache] = '';
            if (isset($oSeo_obj->kSprache) && $language->kSprache === (int)$oSeo_obj->kSprache) {
                $this->cSeo[$language->kSprache] = $oSeo_obj->cSeo;
            }
        }
        if (!empty($oSeo_obj->cName)) {
            $this->cName = $oSeo_obj->cName;
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
                ->setComment('JOIN1 from FilterBaseTag')
                ->setOrigin(__CLASS__),
            (new FilterJoin())
                ->setType('JOIN')
                ->setTable('ttag')
                ->setOn('ttagartikel.kTag = ttag.kTag')
                ->setComment('JOIN2 from FilterBaseTag')
                ->setOrigin(__CLASS__)
        ];
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
        if ($this->getConfig()['navigationsfilter']['allgemein_tagfilter_benutzen'] !== 'N') {
            $joinedTables = [];
            $order        = $this->naviFilter->getOrder();
            $state        = $this->naviFilter->getCurrentStateData();

            $state->joins[] = (new FilterJoin())
                ->setComment('join1 from FilterBaseTag::getOptions()')
                ->setType('JOIN')
                ->setTable('ttagartikel')
                ->setOn('ttagartikel.kArtikel = tartikel.kArtikel')
                ->setOrigin(__CLASS__);
            $state->joins[] = (new FilterJoin())
                ->setComment('join2 from FilterBaseTag::getOptions()')
                ->setType('JOIN')
                ->setTable('ttag')
                ->setOn('ttagartikel.kTag = ttag.kTag')
                ->setOrigin(__CLASS__);
            $state->joins[] = $order->join;

            //remove duplicate joins
            foreach ($state->joins as $i => $stateJoin) {
                if (!in_array($stateJoin->getTable(), $joinedTables, true)) {
                    $joinedTables[] = $stateJoin->getTable();
                } else {
                    unset($state->joins[$i]);
                }
            }

            $state->conditions[] = 'ttag.nAktiv = 1';
            $state->conditions[] = 'ttag.kSprache = ' . $this->getLanguageID();
            $query               = $this->naviFilter->getBaseQuery([
                'ttag.kTag',
                'ttag.cName',
                'ttagartikel.nAnzahlTagging',
                'tartikel.kArtikel'
            ], $state->joins, $state->conditions, $state->having, $order->orderBy, '',
                ['ttag.kTag', 'tartikel.kArtikel']);

            $query            = "SELECT tseo.cSeo, ssMerkmal.kTag, ssMerkmal.cName, 
                COUNT(*) AS nAnzahl, SUM(ssMerkmal.nAnzahlTagging) AS nAnzahlTagging
                    FROM (" . $query . ") AS ssMerkmal
                LEFT JOIN tseo ON tseo.kKey = ssMerkmal.kTag
                    AND tseo.cKey = 'kTag'
                    AND tseo.kSprache = " . $this->getLanguageID() . "
                GROUP BY ssMerkmal.kTag
                ORDER BY nAnzahl DESC LIMIT 0, " . (int)$this->getConfig()['navigationsfilter']['tagfilter_max_anzeige'];
            $tags             = Shop::DB()->query($query, 2);
            $additionalFilter = new FilterItemTag($this->naviFilter);
            // Priorität berechnen
            $nPrioStep = 0;
            $nCount    = count($tags);
            if ($nCount > 0) {
                $nPrioStep = ($tags[0]->nAnzahlTagging - $tags[$nCount - 1]->nAnzahlTagging) / 9;
            }
            foreach ($tags as $tag) {
                $fe                 = (new FilterExtra())
                    ->setType($this->getType())
                    ->setClassName($this->getClassName())
                    ->setParam($this->getUrlParam())
                    ->setName($tag->cName)
                    ->setValue((int)$tag->kTag)
                    ->setCount($tag->nAnzahl)
                    ->setURL($this->naviFilter->getURL(
                        true,
                        $additionalFilter->init((int)$tag->kTag)
                    ));
                $fe->kTag           = (int)$tag->kTag;
                $fe->nAnzahlTagging = (int)$tag->nAnzahlTagging;
                $class                      = '';
                // generic attributes for new filter templates
                if ($fe->kTag > 0) {
                    $class = $nPrioStep < 1
                        ? rand(1, 10)
                        : round(
                            ($fe->nAnzahlTagging - $tags[$nCount - 1]->nAnzahlTagging) /
                            $nPrioStep
                        ) + 1;
                }
                $options[] = $fe->setClass($class);
            }
        }
        $this->options = $options;

        return $options;
    }
}
