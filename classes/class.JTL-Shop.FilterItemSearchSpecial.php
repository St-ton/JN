<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class FilterItemSearchSpecial
 */
class FilterItemSearchSpecial extends AbstractFilter
{
    use MagicCompatibilityTrait;

    /**
     * @var array
     */
    private static $mapping = [
        'cName' => 'Name',
        'kKey'  => 'Value'
    ];

    /**
     * FilterItemSearchSpecial constructor.
     *
     * @param ProductFilter $productFilter
     */
    public function __construct(ProductFilter $productFilter)
    {
        parent::__construct($productFilter);
        $this->setIsCustom(false)
             ->setUrlParam('qf')
             ->setFrontendName(Shop::Lang()->get('specificProducts'))
             ->setVisibility($this->getConfig()['navigationsfilter']['allgemein_suchspecialfilter_benutzen'])
             ->setType($this->getConfig()['navigationsfilter']['search_special_filter_type'] === 'O'
                 ? AbstractFilter::FILTER_TYPE_OR
                 : AbstractFilter::FILTER_TYPE_AND);
    }

    /**
     * @param array|int|string $value
     * @return $this
     */
    public function setValue($value)
    {
        $this->value = is_array($value) ? $value : (int)$value;

        return $this;
    }

    /**
     * @param array $languages
     * @return $this
     */
    public function setSeo($languages)
    {
        $val = $this->getValue();
        if ((is_numeric($val) && $val > 0) || (is_array($val) && count($val) > 0)) {
            if (!is_array($val)) {
                $val = [$val];
            }
            $oSeo_arr = Shop::DB()->query(
                "SELECT tseo.cSeo, tseo.kSprache
                    FROM tseo
                    WHERE cKey = 'suchspecial' 
                        AND kKey IN (" . implode(', ', $val) . ")
                    ORDER BY kSprache",
                2
            );
            foreach ($languages as $language) {
                $this->cSeo[$language->kSprache] = '';
                foreach ($oSeo_arr as $oSeo) {
                    $oSeo->kSprache = (int)$oSeo->kSprache;
                    if ($language->kSprache === $oSeo->kSprache) {
                        $this->cSeo[$language->kSprache] = $oSeo->cSeo;
                    }
                }
            }
            switch ($val[0]) {
                case SEARCHSPECIALS_BESTSELLER:
                    $this->setName(Shop::Lang()->get('bestsellers'));
                    break;
                case SEARCHSPECIALS_SPECIALOFFERS:
                    $this->setName(Shop::Lang()->get('specialOffers'));
                    break;
                case SEARCHSPECIALS_NEWPRODUCTS:
                    $this->setName(Shop::Lang()->get('newProducts'));
                    break;
                case SEARCHSPECIALS_TOPOFFERS:
                    $this->setName(Shop::Lang()->get('topOffers'));
                    break;
                case SEARCHSPECIALS_UPCOMINGPRODUCTS:
                    $this->setName(Shop::Lang()->get('upcomingProducts'));
                    break;
                case SEARCHSPECIALS_TOPREVIEWS:
                    $this->setName(Shop::Lang()->get('topReviews'));
                    break;
                default:
                    // invalid search special ID
                    Shop::$is404        = true;
                    Shop::$kSuchspecial = 0;
                    break;
            }
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getPrimaryKeyRow()
    {
        return 'kKey';
    }

    /**
     * @return string
     */
    public function getSQLCondition()
    {
        $or         = $this->getType() === AbstractFilter::FILTER_TYPE_OR;
        $conf       = $this->getConfig();
        $conditions = [];
        foreach ($this->getValue() as $value) {
            switch ($value) {
                case SEARCHSPECIALS_BESTSELLER:
                    $nAnzahl = ($min = (int)$conf['global']['global_bestseller_minanzahl']) > 0
                        ? $min
                        : 100;
    
                    $conditions[] = 'ROUND(tbestseller.fAnzahl) >= ' . $nAnzahl;
                    break;
    
                case SEARCHSPECIALS_SPECIALOFFERS:
                    $tasp = 'tartikelsonderpreis';
                    $tsp  = 'tsonderpreise';
                    if (!$this->productFilter->hasPriceRangeFilter()) {
                        $tasp = 'tasp';
                        $tsp  = 'tsp';
                    }
                    $conditions[] = $tasp . " .kArtikel = tartikel.kArtikel
                                        AND " . $tasp . ".cAktiv = 'Y' 
                                        AND " . $tasp . ".dStart <= now()
                                        AND (" . $tasp . ".dEnde >= curdate() 
                                            OR " . $tasp . ".dEnde = '0000-00-00')
                                        AND " . $tsp . " .kKundengruppe = " . Session::CustomerGroup()->getID();
                    break;
    
                case SEARCHSPECIALS_NEWPRODUCTS:
                    $days = ($d = $conf['boxen']['box_neuimsortiment_alter_tage']) > 0
                        ? (int)$d
                        : 30;
    
                    $conditions[] = "tartikel.cNeu = 'Y' 
                                AND DATE_SUB(now(),INTERVAL $days DAY) < tartikel.dErstellt 
                                AND tartikel.cNeu = 'Y'";
                    break;
    
                case SEARCHSPECIALS_TOPOFFERS:
                    $conditions[] = "tartikel.cTopArtikel = 'Y'";
                    break;
    
                case SEARCHSPECIALS_UPCOMINGPRODUCTS:
                    $conditions[] = 'NOW() < tartikel.dErscheinungsdatum';
                    break;
    
                case SEARCHSPECIALS_TOPREVIEWS:
                    if (!$this->productFilter->hasPriceRangeFilter()) {
                        $minStars = ($m = $conf['boxen']['boxen_topbewertet_minsterne']) > 0
                            ? (int)$m
                            : 4;
    
                        $conditions[] = 'ROUND(taex.fDurchschnittsBewertung) >= ' . $minStars;
                    }
                    break;
    
                default:
                    break;
            }
        }
        $conditions = array_map(function ($e) {
            return '(' . $e . ')';
        }, $conditions);
        $condition = '(' . implode($or === true ? ' OR ' : ' AND ', $conditions) . ')';

        return $condition;
    }

    /**
     * @return FilterJoin[]
     */
    public function getSQLJoin()
    {
        $joins = [];
        $values = $this->getValue();
        foreach ($values as $value) {
            switch ($value) {
                case SEARCHSPECIALS_BESTSELLER:
                    $joins = (new FilterJoin())
                        ->setType('JOIN')
                        ->setTable('tbestseller')
                        ->setOn('tbestseller.kArtikel = tartikel.kArtikel')
                        ->setComment('JOIN from FilterItemSearchSpecial bestseller')
                        ->setOrigin(__CLASS__);
                    break;

                case SEARCHSPECIALS_SPECIALOFFERS:
                    if (!$this->productFilter->hasPriceRangeFilter()) {
                        $joins = [
                            (new FilterJoin())
                                ->setType('JOIN')
                                ->setTable('tartikelsonderpreis AS tasp')
                                ->setOn('tasp.kArtikel = tartikel.kArtikel')
                                ->setComment('JOIN from FilterItemSearchSpecial special offers')
                                ->setOrigin(__CLASS__),
                            (new FilterJoin())
                                ->setType('JOIN')
                                ->setTable('tsonderpreise AS tsp')
                                ->setOn('tsp.kArtikelSonderpreis = tasp.kArtikelSonderpreis')
                                ->setComment('JOIN2 from FilterItemSearchSpecial special offers')
                                ->setOrigin(__CLASS__)
                        ];
                    }
                    break;

                case SEARCHSPECIALS_TOPREVIEWS:
                    $joins = $this->productFilter->hasRatingFilter()
                        ? []
                        : (new FilterJoin())
                            ->setType('JOIN')
                            ->setTable('tartikelext AS taex ')
                            ->setOn('taex.kArtikel = tartikel.kArtikel')
                            ->setComment('JOIN from FilterItemSearchSpecial top reviews')
                            ->setOrigin(__CLASS__);
                    break;

                case SEARCHSPECIALS_NEWPRODUCTS:
                case SEARCHSPECIALS_TOPOFFERS:
                case SEARCHSPECIALS_UPCOMINGPRODUCTS:
                default:
                    break;
            }
        }

        return $joins;
    }

    /**
     * @param null $data
     * @return FilterOption[]
     */
    public function getOptions($data = null)
    {
        if ($this->getConfig()['navigationsfilter']['allgemein_suchspecialfilter_benutzen'] !== 'Y') {
            $this->options = [];
        }
        if ($this->options !== null) {
            return $this->options;
        }
        $name             = '';
        $options          = [];
        $additionalFilter = new self($this->productFilter);
        $ignore           = $this->getType() === AbstractFilter::FILTER_TYPE_OR
            ? $this->getClassName()
            : null;
        for ($i = 1; $i < 7; ++$i) {
            $state = $this->productFilter->getCurrentStateData($ignore);
            switch ($i) {
                case SEARCHSPECIALS_BESTSELLER:
                    $name    = Shop::Lang()->get('bestsellers');
                    $nAnzahl = (($min = $this->getConfig()['global']['global_bestseller_minanzahl']) > 0)
                        ? (int)$min
                        : 100;

                    $state->joins[] = (new FilterJoin())
                        ->setComment('join from FilterItemSearchSpecial::getOptions() bestseller')
                        ->setType('JOIN')
                        ->setTable('tbestseller')
                        ->setOn('tbestseller.kArtikel = tartikel.kArtikel')
                        ->setOrigin(__CLASS__);

                    $state->conditions[] = 'ROUND(tbestseller.fAnzahl) >= ' . $nAnzahl;
                    break;
                case SEARCHSPECIALS_SPECIALOFFERS:
                    $name = Shop::Lang()->get('specialOffer');
                    if (true||!$this->isInitialized()) {
                        $state->joins[] = (new FilterJoin())
                            ->setComment('join1 from FilterItemSearchSpecial::getOptions() special offer')
                            ->setType('JOIN')
                            ->setTable('tartikelsonderpreis')
                            ->setOn('tartikelsonderpreis.kArtikel = tartikel.kArtikel')
                            ->setOrigin(__CLASS__);

                        $state->joins[] = (new FilterJoin())
                            ->setComment('join2 from FilterItemSearchSpecial::getOptions() special offer')
                            ->setType('JOIN')
                            ->setTable('tsonderpreise')
                            ->setOn('tsonderpreise.kArtikelSonderpreis = tartikelsonderpreis.kArtikelSonderpreis')
                            ->setOrigin(__CLASS__);
                        $tsonderpreise  = 'tsonderpreise';
                    } else {
                        $tsonderpreise = 'tsonderpreise';
                    }
                    $state->conditions[] = "tartikelsonderpreis.cAktiv = 'Y' 
                        AND tartikelsonderpreis.dStart <= now()";
                    $state->conditions[] = "(tartikelsonderpreis.dEnde >= CURDATE() 
                        OR tartikelsonderpreis.dEnde = '0000-00-00')";
                    $state->conditions[] = $tsonderpreise . '.kKundengruppe = ' . $this->getCustomerGroupID();
                    break;
                case SEARCHSPECIALS_NEWPRODUCTS:
                    $name                = Shop::Lang()->get('newProducts');
                    $alter_tage          = (($age = $this->getConfig()['boxen']['box_neuimsortiment_alter_tage']) > 0)
                        ? (int)$age
                        : 30;
                    $state->conditions[] = "tartikel.cNeu = 'Y' 
                        AND DATE_SUB(now(), INTERVAL $alter_tage DAY) < tartikel.dErstellt";
                    break;
                case SEARCHSPECIALS_TOPOFFERS:
                    $name = Shop::Lang()->get('topOffer');
                    $state->conditions[] = "tartikel.cTopArtikel = 'Y'";
                    break;
                case SEARCHSPECIALS_UPCOMINGPRODUCTS:
                    $name = Shop::Lang()->get('upcomingProducts');
                    $state->conditions[] = 'now() < tartikel.dErscheinungsdatum';
                    break;
                case SEARCHSPECIALS_TOPREVIEWS:
                    $name = Shop::Lang()->get('topReviews');
                    if (!$this->productFilter->hasRatingFilter()) {
                        $state->joins[] = (new FilterJoin())
                            ->setComment('join from FilterItemSearchSpecial::getOptions() top reviews')
                            ->setType('JOIN')
                            ->setTable('tartikelext')
                            ->setOn('tartikelext.kArtikel = tartikel.kArtikel')
                            ->setOrigin(__CLASS__);
                    }
                    $state->conditions[] = 'ROUND(tartikelext.fDurchschnittsBewertung) >= ' .
                        (int)$this->getConfig()['boxen']['boxen_topbewertet_minsterne'];
                    break;
                default:
                    break;
            }
            $qry     = $this->productFilter->getFilterSQL()->getBaseQuery(
                ['tartikel.kArtikel'],
                $state->joins,
                $state->conditions,
                $state->having
            );
            $qryRes  = Shop::DB()->query($qry, 2);

            if (($count = count($qryRes)) > 0) {
                $options[$i] = (new FilterOption())
                    ->setType($this->getType())
                    ->setClassName($this->getClassName())
                    ->setParam($this->getUrlParam())
                    ->setName($name)
                    ->setValue($i)
                    ->setCount($count)
                    ->setSort(0)
                    ->setIsActive($this->productFilter->filterOptionIsActive($this->getClassName(), $i))
                    ->setURL($this->productFilter->getFilterURL()->getURL($additionalFilter->init($i)
//                    , false
//                    , true
                    )
                );
            }
        }
        $this->options = $options;

        return $options;
    }
}
