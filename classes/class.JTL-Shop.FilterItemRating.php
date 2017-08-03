<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class FilterItemRating
 */
class FilterItemRating extends AbstractFilter
{
    use FilterItemTrait;

    /**
     * @var int
     */
    public $nSterne = 0;

    /**
     * FilterItemRating constructor.
     *
     * @param Navigationsfilter $naviFilter
     */
    public function __construct($naviFilter)
    {
        parent::__construct($naviFilter);
        $this->isCustom    = false;
        $this->urlParam    = 'bf';
        $this->urlParamSEO = null;
        $this->setVisibility($this->getConfig()['navigationsfilter']['bewertungsfilter_benutzen'])
             ->setFrontendName(Shop::Lang()->get('Votes'));
    }

    /**
     * @param int $id
     * @return $this
     */
    public function setValue($id)
    {
        $this->nSterne = (int)$id;

        return $this;
    }

    /**
     * @return int
     */
    public function getValue()
    {
        return $this->nSterne;
    }

    /**
     * @param array $languages
     * @return $this
     */
    public function setSeo($languages)
    {
        $this->cName = Shop::Lang()->get('from', 'productDetails') . ' ' .
            $this->getValue() . ' ' .
            ($this->getValue() > 0
                ? Shop::Lang()->get('starPlural')
                : Shop::Lang()->get('starSingular')
            );

        return $this;
    }

    /**
     * @return string
     */
    public function getPrimaryKeyRow()
    {
        return 'nSterne';
    }

    /**
     * @return string
     */
    public function getTableName()
    {
        return 'ttags';
    }

    /**
     * @return string
     */
    public function getSQLCondition()
    {
        return 'ROUND(tartikelext.fDurchschnittsBewertung, 0) >= ' . $this->getValue();
    }

    /**
     * @return FilterJoin
     */
    public function getSQLJoin()
    {
        return (new FilterJoin())
            ->setType('JOIN')
            ->setTable('tartikelext')
            ->setOn('tartikel.kArtikel = tartikelext.kArtikel')
            ->setComment('JOIN from FilterItemRating')
            ->setOrigin(__CLASS__);
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
        if ($this->getConfig()['navigationsfilter']['bewertungsfilter_benutzen'] !== 'N') {
            $order      = $this->naviFilter->getOrder();
            $state      = $this->naviFilter->getCurrentStateData();

            $state->joins[] = $order->join;
            $state->joins[] = $this->getSQLJoin();

            $query = $this->naviFilter->getBaseQuery(
                [
                    'ROUND(tartikelext.fDurchschnittsBewertung, 0) AS nSterne',
                    'tartikel.kArtikel'
                ],
                $state->joins,
                $state->conditions,
                $state->having,
                $order->orderBy
            );
            $query = 'SELECT ssMerkmal.nSterne, COUNT(*) AS nAnzahl
                        FROM (' . $query . ' ) AS ssMerkmal
                        GROUP BY ssMerkmal.nSterne
                        ORDER BY ssMerkmal.nSterne DESC';
            $res   = Shop::DB()->query($query, 2);
            if (is_array($res)) {
                $nSummeSterne     = 0;
                $additionalFilter = new self($this->getNaviFilter());
                foreach ($res as $row) {
                    $nSummeSterne += (int)$row->nAnzahl;

                    $fe         = (new FilterExtra())
                        ->setType($this->getType())
                        ->setClassName($this->getClassName())
                        ->setParam($this->getUrlParam())
                        ->setName(
                            Shop::Lang()->get('from', 'productDetails') . ' ' .
                            $row->nSterne . ' ' .
                            ($row->nSterne > 1
                                ? Shop::Lang()->get('starPlural')
                                : Shop::Lang()->get('starSingular'))
                        )
                        ->setValue((int)$row->nSterne)
                        ->setCount($nSummeSterne)
                        ->setURL($this->naviFilter->getURL(
                            true,
                            $additionalFilter->init((int)$row->nSterne)
                        ));
                    $fe->nStern = (int)$row->nSterne;
                    $options[] = $fe;
                }
            }
        }
        $this->options = $options;

        return $options;
    }
}
