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
    use FilterItemTrait;

    /**
     * @var int
     */
    public $kKey = 0;

    /**
     * FilterItemSearchSpecial constructor.
     *
     * @param ProductFilter $productFilter
     */
    public function __construct($productFilter)
    {
        parent::__construct($productFilter);
        $this->isCustom = false;
        $this->urlParam = 'qf';
        $this->setFrontendName(Shop::Lang()->get('specificProducts'))
             ->setVisibility($this->getConfig()['navigationsfilter']['allgemein_suchspecialfilter_benutzen']);
    }

    /**
     * @param int $id
     * @return $this
     */
    public function setValue($id)
    {
        $this->kKey = (int)$id;

        return $this;
    }

    /**
     * @return int
     */
    public function getValue()
    {
        return $this->kKey;
    }

    /**
     * @param array $languages
     * @return $this
     */
    public function setSeo($languages)
    {
        $oSeo_arr = Shop::DB()->selectAll(
            'tseo',
            ['cKey', 'kKey'],
            ['suchspecial', $this->getValue()],
            'cSeo, kSprache',
            'kSprache'
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
        switch ($this->getValue()) {
            case SEARCHSPECIALS_BESTSELLER:
                $this->cName = Shop::Lang()->get('bestsellers');
                break;
            case SEARCHSPECIALS_SPECIALOFFERS:
                $this->cName = Shop::Lang()->get('specialOffers');
                break;
            case SEARCHSPECIALS_NEWPRODUCTS:
                $this->cName = Shop::Lang()->get('newProducts');
                break;
            case SEARCHSPECIALS_TOPOFFERS:
                $this->cName = Shop::Lang()->get('topOffers');
                break;
            case SEARCHSPECIALS_UPCOMINGPRODUCTS:
                $this->cName = Shop::Lang()->get('upcomingProducts');
                break;
            case SEARCHSPECIALS_TOPREVIEWS:
                $this->cName = Shop::Lang()->get('topReviews');
                break;
            default:
                //invalid search special ID
                Shop::$is404        = true;
                Shop::$kSuchspecial = 0;
                break;
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
     * @todo
     */
    public function getTableName()
    {
        return '';
    }

    /**
     * @return string
     */
    public function getSQLCondition()
    {
        $conf = $this->getConfig();
        switch ($this->kKey) {
            case SEARCHSPECIALS_BESTSELLER:
                $nAnzahl = (isset($conf['global']['global_bestseller_minanzahl'])
                    && (int)$conf['global']['global_bestseller_minanzahl'] > 0)
                    ? (int)$conf['global']['global_bestseller_minanzahl']
                    : 100;

                return 'ROUND(tbestseller.fAnzahl) >= ' . $nAnzahl;

            case SEARCHSPECIALS_SPECIALOFFERS:
                $tasp = 'tartikelsonderpreis';
                $tsp  = 'tsonderpreise';
                if (!$this->productFilter->hasPriceRangeFilter()) {
                    $tasp = 'tasp';
                    $tsp  = 'tsp';
                }
                return $tasp . " .kArtikel = tartikel.kArtikel
                                    AND " . $tasp . ".cAktiv = 'Y' 
                                    AND " . $tasp . ".dStart <= now()
                                    AND (" . $tasp . ".dEnde >= curdate() 
                                        OR " . $tasp . ".dEnde = '0000-00-00')
                                    AND " . $tsp . " .kKundengruppe = " . Session::CustomerGroup()->getID();
//@todo:
//                $oFilter->tasp = $tasp;
//                $oFilter->tsp  = $tsp;

                break;

            case SEARCHSPECIALS_NEWPRODUCTS:
                $alter_tage = ($conf['boxen']['box_neuimsortiment_alter_tage'] > 0)
                    ? (int)$conf['boxen']['box_neuimsortiment_alter_tage']
                    : 30;

                return "tartikel.cNeu = 'Y' 
                            AND DATE_SUB(now(),INTERVAL $alter_tage DAY) < tartikel.dErstellt 
                            AND tartikel.cNeu = 'Y'";

            case SEARCHSPECIALS_TOPOFFERS:
                return "tartikel.cTopArtikel = 'Y'";

            case SEARCHSPECIALS_UPCOMINGPRODUCTS:
                return 'now() < tartikel.dErscheinungsdatum';

            case SEARCHSPECIALS_TOPREVIEWS:
                if (!$this->productFilter->hasPriceRangeFilter()) {
                    $nMindestSterne = ((int)$conf['boxen']['boxen_topbewertet_minsterne'] > 0)
                        ? (int)$conf['boxen']['boxen_topbewertet_minsterne']
                        : 4;

                    return 'ROUND(taex.fDurchschnittsBewertung) >= ' . $nMindestSterne;
                }
                break;

            default:
                break;
        }

        return '';
    }

    /**
     * @return string
     */
    public function getSQLJoin()
    {
        switch ($this->kKey) {
            case SEARCHSPECIALS_BESTSELLER:
                return (new FilterJoin())
                    ->setType('JOIN')
                    ->setTable('tbestseller')
                    ->setOn('tbestseller.kArtikel = tartikel.kArtikel')
                    ->setComment('JOIN from FilterItemSearchSpecial bestseller')
                    ->setOrigin(__CLASS__);

            case SEARCHSPECIALS_SPECIALOFFERS:
                if (!$this->productFilter->hasPriceRangeFilter()) {
                    return [
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

                return [];

            case SEARCHSPECIALS_NEWPRODUCTS:
            case SEARCHSPECIALS_TOPOFFERS:
            case SEARCHSPECIALS_UPCOMINGPRODUCTS:
                return [];

            case SEARCHSPECIALS_TOPREVIEWS:
                return $this->productFilter->hasRatingFilter()
                    ? []
                    : (new FilterJoin())
                        ->setType('JOIN')
                        ->setTable('tartikelext AS taex ')
                        ->setOn('taex.kArtikel = tartikel.kArtikel')
                        ->setComment('JOIN from FilterItemSearchSpecial top reviews')
                        ->setOrigin(__CLASS__);

            default:
                return [];
        }
    }

    /**
     * @param null $data
     * @return array
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
        for ($i = 1; $i < 7; ++$i) {
            $state = $this->productFilter->getCurrentStateData();
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
                    if (!$this->isInitialized()) {
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
            }
            $qry    = $this->productFilter->getBaseQuery(
                ['tartikel.kArtikel'],
                $state->joins,
                $state->conditions,
                $state->having
            );
            $qryRes  = Shop::DB()->query($qry, 2);

            $fe = (new FilterExtra())
                ->setType($this->getType())
                ->setClassName($this->getClassName())
                ->setParam($this->getUrlParam())
                ->setName($name)
                ->setValue($i)
                ->setCount(count($qryRes))
                ->setSort(0)
                ->setURL($this->productFilter->getURL(
                    $additionalFilter->init($i)
                ));
            $fe->kKey = $i;
            if ($fe->getCount() > 0) {
                $options[$i] = $fe;
            }
        }
        $this->options = $options;

        return $options;
    }
}
