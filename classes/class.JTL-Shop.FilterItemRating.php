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
    use MagicCompatibilityTrait;

    private static $mapping = [
        'nSterne' => 'Value'
    ];

    /**
     * FilterItemRating constructor.
     *
     * @param ProductFilter $productFilter
     */
    public function __construct(ProductFilter $productFilter)
    {
        parent::__construct($productFilter);
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
        return parent::setValue((int)$id);
    }

    /**
     * @param array $languages
     * @return $this
     */
    public function setSeo($languages)
    {
        $this->setName(Shop::Lang()->get('from', 'productDetails') . ' ' .
            $this->getValue() . ' ' .
            Shop::Lang()->get($this->getValue() > 0 ? 'starPlural' : 'starSingular')
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
     * @param null $data
     * @return FilterOption[]
     */
    public function getOptions($data = null)
    {
        if ($this->options !== null) {
            return $this->options;
        }
        if ($this->getConfig()['navigationsfilter']['bewertungsfilter_benutzen'] === 'N') {
            $this->hide();

            return $this->options;
        }
        $options = [];
        $state   = $this->productFilter->getCurrentStateData();

        $state->joins[] = $this->getSQLJoin();

        $query = $this->productFilter->getFilterSQL()->getBaseQuery(
            [
                'ROUND(tartikelext.fDurchschnittsBewertung, 0) AS nSterne',
                'tartikel.kArtikel'
            ],
            $state->joins,
            $state->conditions,
            $state->having
        );
        $res              = Shop::DB()->query(
            'SELECT ssMerkmal.nSterne, COUNT(*) AS nAnzahl
                FROM (' . $query . ' ) AS ssMerkmal
                GROUP BY ssMerkmal.nSterne
                ORDER BY ssMerkmal.nSterne DESC', 2
        );
        $nSummeSterne     = 0;
        $additionalFilter = new self($this->getProductFilter());
        foreach ($res as $row) {
            $nSummeSterne += (int)$row->nAnzahl;

            $options[] = (new FilterOption())
                ->setType($this->getType())
                ->setClassName($this->getClassName())
                ->setParam($this->getUrlParam())
                ->setName(
                    Shop::Lang()->get('from', 'productDetails') . ' ' .
                    $row->nSterne . ' ' .
                    Shop::Lang()->get($row->nSterne > 1 ? 'starPlural' : 'starSingular')
                )
                ->setValue((int)$row->nSterne)
                ->setCount($nSummeSterne)
                ->setURL($this->productFilter->getFilterURL()->getURL(
                    $additionalFilter->init((int)$row->nSterne)
                ));
        }
        $this->options = $options;
        if (count($options) === 0) {
            $this->hide();
        }

        return $options;
    }
}
