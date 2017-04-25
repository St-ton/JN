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
     * @param int|null   $languageID
     * @param int|null   $customerGroupID
     * @param array|null $config
     * @param array|null $languages
     */
    public function __construct($languageID = null, $customerGroupID = null, $config = null, $languages = null)
    {
        parent::__construct($languageID, $customerGroupID, $config, $languages);
        $this->isCustom    = false;
        $this->urlParam    = 'bf';
        $this->urlParamSEO = null;
        $this->setVisibility($config['navigationsfilter']['bewertungsfilter_benutzen'])
             ->setFrontendName(Shop::Lang()->get('Votes', 'global'));
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
                ? Shop::Lang()->get('starPlural', 'global')
                : Shop::Lang()->get('starSingular', 'global')
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
        return (new FilterJoin())->setType('JOIN')
             ->setTable('tartikelext')
             ->setOn('tartikel.kArtikel = tartikelext.kArtikel')
             ->setComment('JOIN from FilterItemRating');
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
        $ratings = [];
        if ($this->getConfig()['navigationsfilter']['bewertungsfilter_benutzen'] !== 'N') {
            $naviFilter = Shop::getNaviFilter();
            $order      = $naviFilter->getOrder();
            $state      = $naviFilter->getCurrentStateData();

            $state->joins[] = $order->join;
            $state->joins[] = $this->getSQLJoin();

            $query = $naviFilter->getBaseQuery(
                [
                    'ROUND(tartikelext.fDurchschnittsBewertung, 0) AS nSterne',
                    'tartikel.kArtikel'
                ],
                $state->joins,
                $state->conditions,
                $state->having,
                $order->orderBy
            );
            $query = "SELECT ssMerkmal.nSterne, COUNT(*) AS nAnzahl
                        FROM (" . $query . " ) AS ssMerkmal
                        GROUP BY ssMerkmal.nSterne
                        ORDER BY ssMerkmal.nSterne DESC";
            $res   = Shop::DB()->query($query, 2);
            if (is_array($res)) {
                $nSummeSterne     = 0;
                $additionalFilter = new FilterItemRating(
                    $this->getLanguageID(),
                    $this->getCustomerGroupID(),
                    $this->getConfig(),
                    $this->getAvailableLanguages()
                );
                foreach ($res as $row) {
                    $nSummeSterne += (int)$row->nAnzahl;

                    $rating         = (new FilterExtra())
                        ->setType($this->getType())
                        ->setClassName($this->getClassName())
                        ->setParam($this->getUrlParam())
                        ->setName(
                            Shop::Lang()->get('from', 'productDetails') . ' ' .
                            $row->nSterne . ' ' .
                            ($row->nSterne > 1
                                ? Shop::Lang()->get('starPlural', 'global')
                                : Shop::Lang()->get('starSingular', 'global'))
                        )
                        ->setValue((int)$row->nSterne)
                        ->setCount($nSummeSterne)
                        ->setURL($naviFilter->getURL(
                            true,
                            $additionalFilter->init((int)$row->nSterne)
                        ));
                    $rating->nStern = (int)$row->nSterne;

//                    <em>({lang key='from' section='productDetails'} {$oBewertung->nStern}
//                                {if $oBewertung->nStern > 1}
//                                    {lang key='starPlural'}
//                                {else}
//                                    {lang key='starSingular'}
//                                {/if})
//                            </em>
                    // attributes for old filter templates
//                    $rating          = new stdClass();
//                    $rating->nStern  = (int)$row->nSterne;
//                    $rating->nAnzahl = $nSummeSterne;
//                    $rating->cURL    = $naviFilter->getURL(
//                        true,
//                        $additionalFilter->init($rating->nStern)
//                    );
//                    // generic attributes for new filter templates
//                    $rating->count = $nSummeSterne;
//                    $rating->id    = (int)$row->nSterne;

                    $ratings[] = $rating;
                }
            }
        }

        return $ratings;
    }
}
