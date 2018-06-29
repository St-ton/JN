<?php declare(strict_types=1);
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
use Filter\FilterStateSQL;
use Filter\Type;
use Filter\ProductFilter;

/**
 * Class SearchSpecial
 * @package Filter\Items
 */
class SearchSpecial extends AbstractFilter
{
    use \MagicCompatibilityTrait;

    /**
     * @var array
     */
    public static $mapping = [
        'cName' => 'Name',
        'kKey'  => 'ValueCompat'
    ];

    /**
     * SearchSpecial constructor.
     *
     * @param ProductFilter $productFilter
     */
    public function __construct(ProductFilter $productFilter)
    {
        parent::__construct($productFilter);
        $this->setIsCustom(false)
             ->setUrlParam('qf')
             ->setFrontendName(\Shop::Lang()->get('specificProducts'))
             ->setVisibility($this->getConfig('navigationsfilter')['allgemein_suchspecialfilter_benutzen'])
             ->setType($this->getConfig('navigationsfilter')['search_special_filter_type'] === 'O'
                 ? Type::OR
                 : Type::AND);
    }

    /**
     * @inheritdoc
     */
    public function setValue($value): FilterInterface
    {
        $this->value = is_array($value) ? $value : [(int)$value];

        return $this;
    }

    /**
     * @param array|int|string $value
     * @return $this
     */
    public function setValueCompat(int $value)
    {
        $this->value = [$value];

        return $this;
    }

    /**
     * @return int
     */
    public function getValueCompat()
    {
        return is_array($this->value) ? $this->value[0] : $this->value;
    }


    /**
     * @inheritdoc
     */
    public function setSeo(array $languages): FilterInterface
    {
        $val = $this->getValue();
        if ((is_numeric($val) && $val > 0) || (is_array($val) && count($val) > 0)) {
            if (!is_array($val)) {
                $val = [$val];
            }
            $oSeo_arr = \Shop::Container()->getDB()->query(
                "SELECT tseo.cSeo, tseo.kSprache
                    FROM tseo
                    WHERE cKey = 'suchspecial' 
                        AND kKey IN (" . implode(', ', $val) . ")
                    ORDER BY kSprache",
                ReturnType::ARRAY_OF_OBJECTS
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
                    $this->setName(\Shop::Lang()->get('bestsellers'));
                    break;
                case SEARCHSPECIALS_SPECIALOFFERS:
                    $this->setName(\Shop::Lang()->get('specialOffers'));
                    break;
                case SEARCHSPECIALS_NEWPRODUCTS:
                    $this->setName(\Shop::Lang()->get('newProducts'));
                    break;
                case SEARCHSPECIALS_TOPOFFERS:
                    $this->setName(\Shop::Lang()->get('topOffers'));
                    break;
                case SEARCHSPECIALS_UPCOMINGPRODUCTS:
                    $this->setName(\Shop::Lang()->get('upcomingProducts'));
                    break;
                case SEARCHSPECIALS_TOPREVIEWS:
                    $this->setName(\Shop::Lang()->get('topReviews'));
                    break;
                default:
                    // invalid search special ID
                    \Shop::$is404        = true;
                    \Shop::$kSuchspecial = 0;
                    break;
            }
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getPrimaryKeyRow(): string
    {
        return 'kKey';
    }

    /**
     * @inheritdoc
     */
    public function getSQLCondition(): string
    {
        $or         = $this->getType() === Type::OR;
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
                                        AND " . $tsp . " .kKundengruppe = " . \Session::CustomerGroup()->getID();
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
                    if (!$this->productFilter->hasRatingFilter()) {
                        $minStars     = ($m = $conf['boxen']['boxen_topbewertet_minsterne']) > 0
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

        return '(' . implode($or === true ? ' OR ' : ' AND ', $conditions) . ')';
    }

    /**
     * @inheritdoc
     */
    public function getSQLJoin()
    {
        $joins    = [];
        $values   = $this->getValue();
        $joinType = $this->getType() === Type:: AND
            ? 'JOIN'
            : 'LEFT JOIN';
        foreach ($values as $value) {
            switch ($value) {
                case SEARCHSPECIALS_BESTSELLER:
                    $joins[] = (new FilterJoin())
                        ->setType($joinType)
                        ->setTable('tbestseller')
                        ->setOn('tbestseller.kArtikel = tartikel.kArtikel')
                        ->setComment('bestseller JOIN from ' . __METHOD__)
                        ->setOrigin(__CLASS__);
                    break;

                case SEARCHSPECIALS_SPECIALOFFERS:
                    if (!$this->productFilter->hasPriceRangeFilter()) {
                        $joins[] = (new FilterJoin())
                            ->setType($joinType)
                            ->setTable('tartikelsonderpreis AS tasp')
                            ->setOn('tasp.kArtikel = tartikel.kArtikel')
                            ->setComment('special offers JOIN from ' . __METHOD__)
                            ->setOrigin(__CLASS__);
                        $joins[] = (new FilterJoin())
                            ->setType($joinType)
                            ->setTable('tsonderpreise AS tsp')
                            ->setOn('tsp.kArtikelSonderpreis = tasp.kArtikelSonderpreis')
                            ->setComment('special offers JOIN2 from ' . __METHOD__)
                            ->setOrigin(__CLASS__);
                    }
                    break;

                case SEARCHSPECIALS_TOPREVIEWS:
                    if (!$this->productFilter->hasRatingFilter()) {
                        $joins[] = (new FilterJoin())
                            ->setType($joinType)
                            ->setTable('tartikelext AS taex ')
                            ->setOn('taex.kArtikel = tartikel.kArtikel')
                            ->setComment('top reviews JOIN from ' . __METHOD__)
                            ->setOrigin(__CLASS__);
                    }
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
     * @inheritdoc
     */
    public function getOptions($data = null): array
    {
        if ($this->getConfig('navigationsfilter')['allgemein_suchspecialfilter_benutzen'] === 'N') {
            $this->options = [];
        }
        if ($this->options !== null) {
            return $this->options;
        }
        $name             = '';
        $options          = [];
        $additionalFilter = new self($this->productFilter);
        $ignore           = $this->getType() === Type::OR
            ? $this->getClassName()
            : null;
        for ($i = 1; $i < 7; ++$i) {
            $sql = (new FilterStateSQL())->from($this->productFilter->getCurrentStateData($ignore));
            $sql->setSelect(['tartikel.kArtikel']);
            $sql->setOrderBy(null);
            $sql->setLimit('');
            $sql->setGroupBy(['tartikel.kArtikel']);
            switch ($i) {
                case SEARCHSPECIALS_BESTSELLER:
                    $name    = \Shop::Lang()->get('bestsellers');
                    $nAnzahl = (($min = $this->getConfig('global')['global_bestseller_minanzahl']) > 0)
                        ? (int)$min
                        : 100;

                    $sql->addJoin((new FilterJoin())
                        ->setComment('bestseller JOIN from ' . __METHOD__)
                        ->setType('JOIN')
                        ->setTable('tbestseller')
                        ->setOn('tbestseller.kArtikel = tartikel.kArtikel')
                        ->setOrigin(__CLASS__));
                    $sql->addCondition('ROUND(tbestseller.fAnzahl) >= ' . $nAnzahl);
                    break;
                case SEARCHSPECIALS_SPECIALOFFERS:
                    $name = \Shop::Lang()->get('specialOffer');
                    if (true || !$this->isInitialized()) {
                        $sql->addJoin((new FilterJoin())
                            ->setComment('special offer JOIN1 from ' . __METHOD__)
                            ->setType('JOIN')
                            ->setTable('tartikelsonderpreis')
                            ->setOn('tartikelsonderpreis.kArtikel = tartikel.kArtikel')
                            ->setOrigin(__CLASS__));
                        $sql->addJoin((new FilterJoin())
                            ->setComment('special offer JOIN2 from ' . __METHOD__)
                            ->setType('JOIN')
                            ->setTable('tsonderpreise')
                            ->setOn('tsonderpreise.kArtikelSonderpreis = tartikelsonderpreis.kArtikelSonderpreis')
                            ->setOrigin(__CLASS__));
                        $tsonderpreise = 'tsonderpreise';
                    } else {
                        $tsonderpreise = 'tsonderpreise';
                    }
                    $sql->addCondition("tartikelsonderpreis.cAktiv = 'Y' 
                        AND tartikelsonderpreis.dStart <= now()");
                    $sql->addCondition("(tartikelsonderpreis.dEnde >= CURDATE() 
                        OR tartikelsonderpreis.dEnde = '0000-00-00')");
                    $sql->addCondition($tsonderpreise . '.kKundengruppe = ' . $this->getCustomerGroupID());
                    break;
                case SEARCHSPECIALS_NEWPRODUCTS:
                    $name       = \Shop::Lang()->get('newProducts');
                    $alter_tage = (($age = $this->getConfig('boxen')['box_neuimsortiment_alter_tage']) > 0)
                        ? (int)$age
                        : 30;
                    $sql->addCondition("tartikel.cNeu = 'Y' 
                        AND DATE_SUB(now(), INTERVAL $alter_tage DAY) < tartikel.dErstellt");
                    break;
                case SEARCHSPECIALS_TOPOFFERS:
                    $name = \Shop::Lang()->get('topOffer');
                    $sql->addCondition("tartikel.cTopArtikel = 'Y'");
                    break;
                case SEARCHSPECIALS_UPCOMINGPRODUCTS:
                    $name = \Shop::Lang()->get('upcomingProducts');
                    $sql->addCondition('now() < tartikel.dErscheinungsdatum');
                    break;
                case SEARCHSPECIALS_TOPREVIEWS:
                    $name = \Shop::Lang()->get('topReviews');
                    if (!$this->productFilter->hasRatingFilter()) {
                        $sql->addJoin((new FilterJoin())
                            ->setComment('top reviews JOIN from ' . __METHOD__)
                            ->setType('JOIN')
                            ->setTable('tartikelext')
                            ->setOn('tartikelext.kArtikel = tartikel.kArtikel')
                            ->setOrigin(__CLASS__));
                    }
                    $sql->addCondition('ROUND(tartikelext.fDurchschnittsBewertung) >= ' .
                        (int)$this->getConfig('boxen')['boxen_topbewertet_minsterne']);
                    break;
                default:
                    break;
            }
            $qry    = $this->productFilter->getFilterSQL()->getBaseQuery($sql);
            $qryRes = \Shop::Container()->getDB()->query($qry, ReturnType::ARRAY_OF_OBJECTS);
            if (($count = count($qryRes)) > 0) {
                $options[$i] = (new FilterOption())
                    ->setIsActive($this->productFilter->filterOptionIsActive($this->getClassName(), $i))
                    ->setURL($this->productFilter->getFilterURL()->getURL($additionalFilter->init($i)))
                    ->setType($this->getType())
                    ->setClassName($this->getClassName())
                    ->setParam($this->getUrlParam())
                    ->setName($name)
                    ->setValue($i)
                    ->setCount($count)
                    ->setSort(0);
            }
        }
        $this->options = $options;

        return $options;
    }
}
