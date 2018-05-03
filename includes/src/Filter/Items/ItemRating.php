<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Filter\Items;

use DB\ReturnType;
use Filter\AbstractFilter;
use Filter\FilterJoin;
use Filter\FilterOption;
use Filter\FilterInterface;
use Filter\ProductFilter;

/**
 * Class ItemRating
 * @package Filter\Items
 */
class ItemRating extends AbstractFilter
{
    use \MagicCompatibilityTrait;

    /**
     * @var array
     */
    private static $mapping = [
        'nSterne' => 'Value'
    ];

    /**
     * ItemRating constructor.
     *
     * @param ProductFilter $productFilter
     */
    public function __construct(ProductFilter $productFilter)
    {
        parent::__construct($productFilter);
        $this->setIsCustom(false)
             ->setUrlParam('bf')
             ->setUrlParamSEO(null)
             ->setVisibility($this->getConfig()['navigationsfilter']['bewertungsfilter_benutzen'])
             ->setFrontendName(\Shop::Lang()->get('Votes'));
    }

    /**
     * @inheritdoc
     */
    public function setValue($value): FilterInterface
    {
        return parent::setValue((int)$value);
    }

    /**
     * @inheritdoc
     */
    public function setSeo(array $languages): FilterInterface
    {
        $this->setName(\Shop::Lang()->get('from', 'productDetails') . ' ' .
            $this->getValue() . ' ' .
            \Shop::Lang()->get($this->getValue() > 0 ? 'starPlural' : 'starSingular')
        );

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getPrimaryKeyRow(): string
    {
        return 'nSterne';
    }

    /**
     * @inheritdoc
     */
    public function getTableName(): string
    {
        return 'ttags';
    }

    /**
     * @inheritdoc
     */
    public function getSQLCondition(): string
    {
        return 'ROUND(tartikelext.fDurchschnittsBewertung, 0) >= ' . $this->getValue();
    }

    /**
     * @inheritdoc
     */
    public function getSQLJoin()
    {
        return (new FilterJoin())
            ->setType('JOIN')
            ->setTable('tartikelext')
            ->setOn('tartikel.kArtikel = tartikelext.kArtikel')
            ->setComment('JOIN from ' . __METHOD__)
            ->setOrigin(__CLASS__);
    }

    /**
     * @inheritdoc
     */
    public function getOptions($data = null): array
    {
        if ($this->options !== null) {
            return $this->options;
        }
        if ($this->getConfig()['navigationsfilter']['bewertungsfilter_benutzen'] === 'N') {
            $this->hide();
            $this->options = [];

            return $this->options;
        }
        $options = [];
        $state   = $this->productFilter->getCurrentStateData();
        $state->addJoin($this->getSQLJoin());

        $query            = $this->productFilter->getFilterSQL()->getBaseQuery(
            [
                'ROUND(tartikelext.fDurchschnittsBewertung, 0) AS nSterne',
                'tartikel.kArtikel'
            ],
            $state->getJoins(),
            $state->getConditions(),
            $state->getHaving()
        );
        $res              = \Shop::Container()->getDB()->query(
            'SELECT ssMerkmal.nSterne, COUNT(*) AS nAnzahl
                FROM (' . $query . ' ) AS ssMerkmal
                GROUP BY ssMerkmal.nSterne
                ORDER BY ssMerkmal.nSterne DESC',
            ReturnType::ARRAY_OF_OBJECTS
        );
        $nSummeSterne     = 0;
        $additionalFilter = new self($this->getProductFilter());
        foreach ($res as $row) {
            $nSummeSterne += (int)$row->nAnzahl;

            $options[] = (new FilterOption())
                ->setParam($this->getUrlParam())
                ->setURL($this->productFilter->getFilterURL()->getURL(
                    $additionalFilter->init((int)$row->nSterne)
                ))
                ->setType($this->getType())
                ->setClassName($this->getClassName())
                ->setName(
                    \Shop::Lang()->get('from', 'productDetails') . ' ' .
                    $row->nSterne . ' ' .
                    \Shop::Lang()->get($row->nSterne > 1 ? 'starPlural' : 'starSingular')
                )
                ->setValue((int)$row->nSterne)
                ->setCount($nSummeSterne);
        }
        $this->options = $options;
        if (count($options) === 0) {
            $this->hide();
        }

        return $options;
    }
}
