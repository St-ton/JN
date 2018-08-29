<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Filter\Items;


use DB\ReturnType;
use Filter\AbstractFilter;
use Filter\FilterInterface;
use Filter\Join;
use Filter\Option;
use Filter\ProductFilter;
use Filter\StateSQL;

/**
 * Class PriceRange
 * @package Filter\Items
 */
class PriceRange extends AbstractFilter
{
    use \MagicCompatibilityTrait;

    /**
     * @var float
     */
    private $offsetStart;

    /**
     * @var float
     */
    private $offsetEnd;

    /**
     * @var string
     */
    private $offsetStartLocalized;

    /**
     * @var string
     */
    private $offsetEndLocalized;

    /**
     * @var \stdClass
     */
    private $oFilter;

    /**
     * @var array
     */
    public static $mapping = [
        'cName'          => 'Name',
        'nAnzahlArtikel' => 'Count',
        'cWert'          => 'Value',
        'fVon'           => 'OffsetStart',
        'fBis'           => 'OffsetEnd',
        'cVonLocalized'  => 'OffsetStartLocalized',
        'cBisLocalized'  => 'OffsetEndLocalized'
    ];

    /**
     * PriceRange constructor.
     *
     * @param ProductFilter $productFilter
     */
    public function __construct(ProductFilter $productFilter)
    {
        parent::__construct($productFilter);
        $this->setIsCustom(false)
             ->setUrlParam('pf')
             ->setVisibility($this->getConfig('navigationsfilter')['preisspannenfilter_benutzen'])
             ->setFrontendName(\Shop::Lang()->get('rangeOfPrices'));
    }

    /**
     * @return float
     */
    public function getOffsetStart(): float
    {
        return $this->offsetStart;
    }

    /**
     * @param float $offsetStart
     * @return PriceRange
     */
    public function setOffsetStart(float $offsetStart): PriceRange
    {
        $this->offsetStart = $offsetStart;

        return $this;
    }

    /**
     * @return float
     */
    public function getOffsetEnd(): float
    {
        return $this->offsetEnd;
    }

    /**
     * @param float $offsetEnd
     * @return PriceRange
     */
    public function setOffsetEnd(float $offsetEnd): PriceRange
    {
        $this->offsetEnd = $offsetEnd;

        return $this;
    }

    /**
     * @return string
     */
    public function getOffsetStartLocalized(): string
    {
        return $this->offsetStartLocalized;
    }

    /**
     * @param string $offsetStartLocalized
     * @return PriceRange
     */
    public function setOffsetStartLocalized(string $offsetStartLocalized): PriceRange
    {
        $this->offsetStartLocalized = $offsetStartLocalized;

        return $this;
    }

    /**
     * @return string
     */
    public function getOffsetEndLocalized(): string
    {
        return $this->offsetEndLocalized;
    }

    /**
     * @param string $offsetEndLocalized
     * @return PriceRange
     */
    public function setOffsetEndLocalized(string $offsetEndLocalized): PriceRange
    {
        $this->offsetEndLocalized = $offsetEndLocalized;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setSeo(array $languages): FilterInterface
    {
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function init($id): FilterInterface
    {
        if (empty($id)) {
            $id = '0_0';
        }
        list($start, $end) = \explode('_', $id);
        $this->offsetStart = (float)$start;
        $this->offsetEnd   = (float)$end;
        $this->setValue($id === '0_0' ? 0 : ($this->offsetStart . '_' . $this->offsetEnd));
        // localize prices
        $this->offsetStartLocalized = \Preise::getLocalizedPriceWithoutFactor($this->offsetStart);
        $this->offsetEndLocalized   = \Preise::getLocalizedPriceWithoutFactor($this->offsetEnd);
        $this->setName(\html_entity_decode($this->offsetStartLocalized . ' - ' . $this->offsetEndLocalized));
        $this->isInitialized = true;
        $conversionFactor    = \Session::Currency()->getConversionFactor();
        $customerGroupID     = \Session::CustomerGroup()->getID();

        $oFilter             = new \stdClass();
        $oFilter->cJoin      = 'JOIN tpreise 
                ON tartikel.kArtikel = tpreise.kArtikel 
                AND tpreise.kKundengruppe = ' . $customerGroupID . '
            LEFT JOIN tartikelkategorierabatt 
                ON tartikelkategorierabatt.kKundengruppe = ' . $customerGroupID . "
                AND tartikelkategorierabatt.kArtikel = tartikel.kArtikel
            LEFT JOIN tartikelsonderpreis 
                ON tartikelsonderpreis.kArtikel = tartikel.kArtikel
                AND tartikelsonderpreis.cAktiv = 'Y'
                AND tartikelsonderpreis.dStart <= NOW()
                AND (tartikelsonderpreis.dEnde IS NULL OR tartikelsonderpreis.dEnde >= CURDATE())
            LEFT JOIN tsonderpreise 
                ON tartikelsonderpreis.kArtikelSonderpreis = tsonderpreise.kArtikelSonderpreis
                AND tsonderpreise.kKundengruppe = " . $customerGroupID;
        $oFilter->cWhere     = '';
        $fKundenrabatt       = (isset($_SESSION['Kunde']->fRabatt) && $_SESSION['Kunde']->fRabatt > 0)
            ? (float)$_SESSION['Kunde']->fRabatt
            : 0.0;
        $nSteuersatzKeys_arr = \array_keys($_SESSION['Steuersatz']);
        // bis
        if (\Session::CustomerGroup()->isMerchant()) {
            $oFilter->cWhere .= ' ROUND(LEAST((tpreise.fVKNetto * ' .
                $conversionFactor . ') * ((100 - GREATEST(IFNULL(tartikelkategorierabatt.fRabatt, 0), ' .
                \Session::CustomerGroup()->getDiscount() . ', ' . $fKundenrabatt . ', 0)) / 100), ' .
                'IFNULL(tsonderpreise.fNettoPreis, (tpreise.fVKNetto * ' .
                $conversionFactor . '))), 2)';
        } else {
            foreach ($nSteuersatzKeys_arr as $nSteuersatzKeys) {
                $fSteuersatz     = (float)$_SESSION['Steuersatz'][$nSteuersatzKeys];
                $oFilter->cWhere .= ' IF(tartikel.kSteuerklasse = ' . $nSteuersatzKeys . ', ROUND(
                    LEAST(tpreise.fVKNetto * 
                    ((100 - GREATEST(IFNULL(tartikelkategorierabatt.fRabatt, 0), ' .
                    \Session::CustomerGroup()->getDiscount() . ', ' . $fKundenrabatt . ', 0)) / 100), ' .
                    'IFNULL(tsonderpreise.fNettoPreis, (tpreise.fVKNetto * ' .
                    $conversionFactor . '))) * ((100 + ' . $fSteuersatz . ') / 100), 2),';
            }
            $oFilter->cWhere .= '0';

            $count = \count($nSteuersatzKeys_arr);
            for ($x = 0; $x < $count; ++$x) {
                $oFilter->cWhere .= ')';
            }
        }
        $oFilter->cWhere .= ' < ' . $this->offsetEnd . ' AND ';
        // von
        if (\Session::CustomerGroup()->isMerchant()) {
            $oFilter->cWhere .= ' ROUND(LEAST(tpreise.fVKNetto * 
                ((100 - GREATEST(IFNULL(tartikelkategorierabatt.fRabatt, 0), ' .
                \Session::CustomerGroup()->getDiscount() . ', ' . $fKundenrabatt . ', 0)) / 100), ' .
                'IFNULL(tsonderpreise.fNettoPreis, (tpreise.fVKNetto * ' . $conversionFactor . '))), 2)';
        } else {
            foreach ($nSteuersatzKeys_arr as $nSteuersatzKeys) {
                $fSteuersatz     = (float)$_SESSION['Steuersatz'][$nSteuersatzKeys];
                $oFilter->cWhere .= ' IF(tartikel.kSteuerklasse = ' . $nSteuersatzKeys . ',
                    ROUND(LEAST(tpreise.fVKNetto * ((100 - GREATEST(IFNULL(tartikelkategorierabatt.fRabatt, 0), ' .
                    \Session::CustomerGroup()->getDiscount() . ', ' . $fKundenrabatt . ', 0)) / 100), 
                    IFNULL(tsonderpreise.fNettoPreis, (tpreise.fVKNetto * ' .
                    $conversionFactor . '))) * ((100 + ' . $fSteuersatz . ') / 100), 2),';
            }
            $oFilter->cWhere .= '0';
            $count           = \count($nSteuersatzKeys_arr);
            for ($x = 0; $x < $count; ++$x) {
                $oFilter->cWhere .= ')';
            }
        }
        $oFilter->cWhere .= ' >= ' . $this->offsetStart;

        $this->oFilter       = $oFilter;
        $this->isInitialized = true;

        return $this;
    }

    /**
     * @return string
     */
    public function getSQLCondition(): string
    {
        return $this->oFilter->cWhere;
    }

    /**
     * @inheritdoc
     */
    public function getSQLJoin()
    {
        return [
            (new Join())
                ->setComment('join1 from ' . __METHOD__)
                ->setType('JOIN')
                ->setTable('tpreise')
                ->setOn('tartikel.kArtikel = tpreise.kArtikel 
                        AND tpreise.kKundengruppe = ' . $this->getCustomerGroupID())
                ->setOrigin(__CLASS__),
            (new Join())
                ->setComment('join2 from ' . __METHOD__)
                ->setType('LEFT JOIN')
                ->setTable('tartikelkategorierabatt')
                ->setOn('tartikelkategorierabatt.kKundengruppe = ' . $this->getCustomerGroupID() .
                    ' AND tartikelkategorierabatt.kArtikel = tartikel.kArtikel')
                ->setOrigin(__CLASS__),
            (new Join())
                ->setComment('join3 from ' . __METHOD__)
                ->setType('LEFT JOIN')
                ->setTable('tartikelsonderpreis')
                ->setOn("tartikelsonderpreis.kArtikel = tartikel.kArtikel
                         AND tartikelsonderpreis.cAktiv = 'Y'
                         AND tartikelsonderpreis.dStart <= NOW()
                         AND (tartikelsonderpreis.dEnde IS NULL OR tartikelsonderpreis.dEnde >= CURDATE())")
                ->setOrigin(__CLASS__),
            (new Join())
                ->setComment('join4 from ' . __METHOD__)
                ->setType('LEFT JOIN')
                ->setTable('tsonderpreise')
                ->setOn('tartikelsonderpreis.kArtikelSonderpreis = tsonderpreise.kArtikelSonderpreis 
                         AND tsonderpreise.kKundengruppe = ' . $this->getCustomerGroupID())
                ->setOrigin(__CLASS__)
        ];
    }

    /**
     * @param \stdClass $oPreis
     * @param \Currency $currency
     * @param array     $ranges
     * @return string
     */
    public function getPriceRangeSQL($oPreis, $currency, array $ranges = []): string
    {
        $cSQL          = '';
        $fKundenrabatt = (isset($_SESSION['Kunde']->fRabatt) && $_SESSION['Kunde']->fRabatt > 0)
            ? $_SESSION['Kunde']->fRabatt
            : 0.0;
        // Wenn Option vorhanden, dann nur Spannen anzeigen, in denen Artikel vorhanden sind
        if ($this->getConfig('navigationsfilter')['preisspannenfilter_anzeige_berechnung'] === 'A') {
            $nPreisMin = $oPreis->fMinPreis;
            $nStep     = $oPreis->fStep;
            $ranges    = [];
            for ($i = 0; $i < $oPreis->nAnzahlSpannen; ++$i) {
                $fakePriceRange       = new \stdClass();
                $fakePriceRange->nBis = $nPreisMin + ($i + 1) * $nStep;
                $ranges[$i]           = $fakePriceRange;
            }
        }
        $max = \count($ranges) - 1;
        foreach ($ranges as $i => $oPreisspannenfilter) {
            $cSQL .= 'COUNT(DISTINCT IF(';
            $nBis = $oPreisspannenfilter->nBis;
            // Finde den höchsten und kleinsten Steuersatz
            if (\is_array($_SESSION['Steuersatz']) && !\Session::CustomerGroup()->isMerchant()) {
                $nSteuersatzKeys_arr = \array_keys($_SESSION['Steuersatz']);
                foreach ($nSteuersatzKeys_arr as $nSteuersatzKeys) {
                    $fSteuersatz = (float)$_SESSION['Steuersatz'][$nSteuersatzKeys];
                    $cSQL        .= 'IF(tartikel.kSteuerklasse = ' . $nSteuersatzKeys . ',
                        ROUND(LEAST((tpreise.fVKNetto * ' . $currency->getConversionFactor() .
                        ') * ((100 - GREATEST(IFNULL(tartikelkategorierabatt.fRabatt, 0), ' .
                        \Session::CustomerGroup()->getDiscount() . ', ' . $fKundenrabatt .
                        ', 0)) / 100), IFNULL(tsonderpreise.fNettoPreis, (tpreise.fVKNetto * ' .
                        $currency->getConversionFactor() . '))) * ((100 + ' . $fSteuersatz . ') / 100), 2),';
                }
                $cSQL  .= '0';
                $count = \count($nSteuersatzKeys_arr);
                for ($x = 0; $x < $count; $x++) {
                    $cSQL .= ')';
                }
            } elseif (\Session::CustomerGroup()->isMerchant()) {
                $cSQL .= 'ROUND(LEAST((tpreise.fVKNetto * ' . $currency->getConversionFactor() .
                    ') * ((100 - GREATEST(IFNULL(tartikelkategorierabatt.fRabatt, 0), ' .
                    \Session::CustomerGroup()->getDiscount() . ', ' . $fKundenrabatt .
                    ', 0)) / 100), IFNULL(tsonderpreise.fNettoPreis, (tpreise.fVKNetto * ' .
                    $currency->getConversionFactor() . '))), 2)';
            }

            $cSQL .= ' < ' . $nBis . ', tartikel.kArtikel, NULL)
                ) AS anz' . $i;
            if ($i < $max) {
                $cSQL .= ', ';
            }
        }

        return $cSQL;
    }

    /**
     * @inheritdoc
     */
    public function getOptions($data = null): array
    {
        if ($this->options !== null) {
            return $this->options;
        }
        $productCount = $data;
        $options      = [];
        // Prüfe, ob es nur einen Artikel in der Artikelübersicht gibt
        // falls ja und es ist noch kein Preisspannenfilter gesetzt, dürfen keine Preisspannenfilter angezeigt werden
        if (($productCount === 1 && !$this->isInitialized())
            || $this->getConfig('navigationsfilter')['preisspannenfilter_benutzen'] === 'N'
        ) {
            return $options;
        }
        $cacheID  = null;
        $currency = \Session::Currency();
        $sql      = (new StateSQL())->from($this->productFilter->getCurrentStateData());

        $sql->addJoin((new Join())
            ->setType('LEFT JOIN')
            ->setTable('tartikelkategorierabatt')
            ->setOn('tartikelkategorierabatt.kKundengruppe = ' . $this->getCustomerGroupID() .
                ' AND tartikelkategorierabatt.kArtikel = tartikel.kArtikel')
            ->setOrigin(__CLASS__));
        $sql->addJoin((new Join())
            ->setType('LEFT JOIN')
            ->setTable('tartikelsonderpreis')
            ->setOn("tartikelsonderpreis.kArtikel = tartikel.kArtikel
                        AND tartikelsonderpreis.cAktiv = 'Y'
                        AND tartikelsonderpreis.dStart <= now()
                        AND (tartikelsonderpreis.dEnde IS NULL OR tartikelsonderpreis.dEnde >= CURDATE())")
            ->setOrigin(__CLASS__));
        $sql->addJoin((new Join())
            ->setType('LEFT JOIN')
            ->setTable('tsonderpreise')
            ->setOn('tartikelsonderpreis.kArtikelSonderpreis = tsonderpreise.kArtikelSonderpreis 
                        AND tsonderpreise.kKundengruppe = ' . $this->getCustomerGroupID())
            ->setOrigin(__CLASS__));
        $sql->addJoin((new Join())
            ->setComment('join1 from ' . __METHOD__)
            ->setTable('tpreise')
            ->setType('JOIN')
            ->setOn('tpreise.kArtikel = tartikel.kArtikel 
                        AND tpreise.kKundengruppe = ' . $this->getCustomerGroupID())
            ->setOrigin(__CLASS__));
        $sql->addJoin((new Join())
            ->setComment('join2 from ' . __METHOD__)
            ->setTable('tartikelsichtbarkeit')
            ->setType('LEFT JOIN')
            ->setOn('tartikel.kArtikel = tartikelsichtbarkeit.kArtikel 
                        AND tartikelsichtbarkeit.kKundengruppe = ' . $this->getCustomerGroupID())
            ->setOrigin(__CLASS__));
        if ($this->getConfig('navigationsfilter')['preisspannenfilter_anzeige_berechnung'] === 'A') {
            $fSteuersatzMax = 0.0;
            $fSteuersatzMin = 0.0;
            if (\is_array($_SESSION['Steuersatz']) && !\Session::CustomerGroup()->isMerchant()) {
                $fSteuersatz_arr = [];
                foreach ($_SESSION['Steuersatz'] as $fSteuersatz) {
                    $fSteuersatz_arr[] = $fSteuersatz;
                }
                $fSteuersatzMax = \count($fSteuersatz_arr) ? \max($fSteuersatz_arr) : 0;
                $fSteuersatzMin = \count($fSteuersatz_arr) ? \min($fSteuersatz_arr) : 0;
            } elseif (\Session::CustomerGroup()->isMerchant()) {
                $fSteuersatzMax = 0.0;
                $fSteuersatzMin = 0.0;
            }
            $fKundenrabatt = ($discount = \Session::CustomerGroup()->getDiscount()) > 0
                ? $discount
                : 0.0;
            $state         = (new StateSQL())->from($this->productFilter->getCurrentStateData());
            foreach ($this->getSQLJoin() as $join) {
                $state->addJoin($join);
            }
            $state->setSelect([
                'ROUND(
                LEAST(
                    (tpreise.fVKNetto * ' . $currency->getConversionFactor() . ') *
                    ((100 - GREATEST(IFNULL(tartikelkategorierabatt.fRabatt, 0), ' .
                \Session::CustomerGroup()->getDiscount() . ', ' . $fKundenrabatt . ', 0)) / 100),
                    IFNULL(tsonderpreise.fNettoPreis, (tpreise.fVKNetto * ' .
                $currency->getConversionFactor() . '))) * ((100 + ' . $fSteuersatzMax . ') / 100), 2) AS fMax,
                    ROUND(LEAST((tpreise.fVKNetto * ' . $currency->getConversionFactor() . ') *
                    ((100 - greatest(IFNULL(tartikelkategorierabatt.fRabatt, 0), ' .
                \Session::CustomerGroup()->getDiscount() . ', ' . $fKundenrabatt . ', 0)) / 100),
                    IFNULL(tsonderpreise.fNettoPreis, (tpreise.fVKNetto * ' .
                $currency->getConversionFactor() . '))) * ((100 + ' . $fSteuersatzMin . ') / 100), 2) AS fMin'
            ]);
            $state->setOrderBy(null);
            $state->setLimit('');
            $state->setGroupBy(['tartikel.kArtikel']);
            $baseQuery = $this->productFilter->getFilterSQL()->getBaseQuery($state);
            $cacheID   = 'fltr_' . \str_replace('\\', '', __CLASS__) . \md5($baseQuery);
            if (($cached = $this->productFilter->getCache()->get($cacheID)) !== false) {
                $this->options = $cached;

                return $this->options;
            }
            $minMax = $this->productFilter->getDB()->query(
                'SELECT MAX(ssMerkmal.fMax) AS fMax, MIN(ssMerkmal.fMin) AS fMin 
                    FROM (' . $baseQuery . ' ) AS ssMerkmal',
                ReturnType::SINGLE_OBJECT
            );
            if (isset($minMax->fMax) && $minMax->fMax > 0) {
                $oPreis                 = $this->calculateSteps(
                    $minMax->fMax * $currency->getConversionFactor(),
                    $minMax->fMin * $currency->getConversionFactor()
                );
                $oPreis->nAnzahlSpannen = \min(20, (int)$oPreis->nAnzahlSpannen);
                $cSelectSQL             = '';
                for ($i = 0; $i < $oPreis->nAnzahlSpannen; ++$i) {
                    if ($i > 0) {
                        $cSelectSQL .= ', ';
                    }
                    $cSelectSQL .= ' SUM(ssMerkmal.anz' . $i . ') AS anz' . $i;
                }

                $sql->setSelect([$this->getPriceRangeSQL($oPreis, $currency)]);
                $sql->setOrderBy(null);
                $sql->setLimit('');
                $sql->setGroupBy(['tartikel.kArtikel']);

                $baseQuery        = $this->productFilter->getFilterSQL()->getBaseQuery($sql);
                $dbRes            = $this->productFilter->getDB()->query(
                    'SELECT ' . $cSelectSQL . ' FROM (' .
                    $baseQuery . ' ) AS ssMerkmal',
                    ReturnType::SINGLE_OBJECT
                );
                $priceRanges      = [];
                $priceRangeCounts = \is_object($dbRes)
                    ? \get_object_vars($dbRes)
                    : [];
                for ($i = 0; $i < $oPreis->nAnzahlSpannen; ++$i) {
                    $sub           = $i === 0
                        ? 0
                        : $priceRangeCounts['anz' . ($i - 1)];
                    $priceRanges[] = $priceRangeCounts['anz' . $i] - $sub;
                }
                $nPreisMax        = $oPreis->fMaxPreis;
                $nPreisMin        = $oPreis->fMinPreis;
                $nStep            = $oPreis->fStep;
                $additionalFilter = new self($this->productFilter);
                foreach ($priceRanges as $i => $count) {
                    $fo   = new Option();
                    $nVon = $nPreisMin + $i * $nStep;
                    $nBis = $nPreisMin + ($i + 1) * $nStep;
                    if ($nBis > $nPreisMax) {
                        if ($nVon >= $nPreisMax) {
                            $nVon = $nPreisMin + ($i - 1) * $nStep;
                        }
                        $nBis = $nPreisMax;
                    }
                    $cVonLocalized     = \Preise::getLocalizedPriceWithoutFactor($nVon, $currency);
                    $cBisLocalized     = \Preise::getLocalizedPriceWithoutFactor($nBis, $currency);
                    $fo->nVon          = $nVon;
                    $fo->nBis          = $nBis;
                    $fo->cVonLocalized = $cVonLocalized;
                    $fo->cBisLocalized = $cBisLocalized;

                    $options[] = $fo->setParam($this->getUrlParam())
                                    ->setURL($this->productFilter->getFilterURL()->getURL(
                                        $additionalFilter->init($nVon . '_' . $nBis))
                                    )
                                    ->setType($this->getType())
                                    ->setClassName($this->getClassName())
                                    ->setName($cVonLocalized . ' - ' . $cBisLocalized)
                                    ->setValue($i)
                                    ->setCount($count)
                                    ->setSort(0);
                }
            }
        } else {
            $ranges = $this->productFilter->getDB()->query(
                'SELECT * FROM tpreisspannenfilter',
                ReturnType::ARRAY_OF_OBJECTS
            );
            if (\count($ranges) > 0) {
                $oPreis = $this->calculateSteps(
                    $ranges[\count($ranges) - 1]->nBis * $currency->getConversionFactor(),
                    $ranges[0]->nVon * $currency->getConversionFactor()
                );
                if (!$oPreis->nAnzahlSpannen || !$oPreis->fMaxPreis) {
                    return [];
                }
                $cSelectSQL = '';
                $count      = \count($ranges);
                for ($i = 0; $i < $count; ++$i) {
                    if ($i > 0) {
                        $cSelectSQL .= ', ';
                    }
                    $cSelectSQL .= 'SUM(ssMerkmal.anz' . $i . ') AS anz' . $i;
                }
                $state = (new StateSQL())->from($sql);
                $state->setSelect([$this->getPriceRangeSQL($oPreis, $currency, $ranges)]);
                $state->setOrderBy(null);
                $state->setLimit('');
                $state->setGroupBy(['tartikel.kArtikel']);
                foreach ($this->getSQLJoin() as $join) {
                    $state->addJoin($join);
                }
                $baseQuery = $this->productFilter->getFilterSQL()->getBaseQuery($state);
                $cacheID   = 'fltr_' . \str_replace('\\', '', __CLASS__) . \md5($baseQuery);
                if (($cached = $this->productFilter->getCache()->get($cacheID)) !== false) {
                    $this->options = $cached;

                    return $this->options;
                }
                $dbRes = $this->productFilter->getDB()->query(
                    'SELECT ' . $cSelectSQL . ' FROM (' . $baseQuery . ' ) AS ssMerkmal',
                    ReturnType::SINGLE_OBJECT
                );

                $additionalFilter = new self($this->productFilter);
                $priceRangeCounts = $dbRes !== false ? \get_object_vars($dbRes) : [];
                $priceRanges      = [];
                $count            = \count($priceRangeCounts);
                for ($i = 0; $i < $count; ++$i) {
                    $sub           = $i === 0
                        ? 0
                        : $priceRangeCounts['anz' . ($i - 1)];
                    $priceRanges[] = $priceRangeCounts['anz' . $i] - $sub;
                }
                foreach ($ranges as $i => $range) {
                    $fo                = new Option();
                    $fo->nVon          = $range->nVon;
                    $fo->nBis          = $range->nBis;
                    $fo->cVonLocalized = \Preise::getLocalizedPriceWithoutFactor($fo->nVon, $currency);
                    $fo->cBisLocalized = \Preise::getLocalizedPriceWithoutFactor($fo->nBis, $currency);
                    $options[]         = $fo->setParam($this->getUrlParam())
                                            ->setURL($this->productFilter->getFilterURL()->getURL(
                                                $additionalFilter->init($fo->nVon . '_' . $fo->nBis)
                                            ))
                                            ->setType($this->getType())
                                            ->setClassName($this->getClassName())
                                            ->setName($fo->cVonLocalized . ' - ' . $fo->cBisLocalized)
                                            ->setValue($i)
                                            ->setCount(isset($priceRanges[$i]) ? (int)$priceRanges[$i] : 0)
                                            ->setSort(0);
                }
            }
        }
        // Preisspannen ohne Artikel ausblenden (falls im Backend eingestellt)
        if (\count($options) > 0
            && $this->getConfig('navigationsfilter')['preisspannenfilter_spannen_ausblenden'] === 'Y'
        ) {
            $options = \array_filter(
                $options,
                function ($e) {
                    /** @var Option $e */
                    return $e->getCount() > 0;
                }
            );
        }
        $this->options = $options;
        if ($cacheID !== null) {
            $this->productFilter->getCache()->set($cacheID, $options, [CACHING_GROUP_FILTER]);
        }

        return $options;
    }

    /**
     * has to be public for compatibility with filter_inc.php
     *
     * @param float $fMax
     * @param float $fMin
     * @return \stdClass
     * @former berechneMaxMinStep
     */
    public function calculateSteps($fMax, $fMin): \stdClass
    {
        static $fStepWert_arr = [
            0.001,
            0.005,
            0.01,
            0.05,
            0.10,
            0.25,
            0.5,
            1.0,
            2.5,
            5.0,
            7.5,
            10.0,
            12.5,
            15.0,
            20.0,
            25.0,
            50.0,
            100.0,
            250.0,
            300.0,
            350.0,
            400.0,
            500.0,
            750.0,
            1000.0,
            1500.0,
            2500.0,
            5000.0,
            10000.0,
            25000.0,
            30000.0,
            40000.0,
            50000.0,
            60000.0,
            75000.0,
            100000.0,
            150000.0,
            250000.0,
            350000.0,
            400000.0,
            500000.0,
            550000.0,
            600000.0,
            750000.0,
            1000000.0,
            1500000.0,
            5000000.0,
            7500000.0,
            10000000.0,
            12500000.0,
            15000000.0,
            25000000.0,
            50000000.0,
            100000000.0
        ];
        $nStep      = 10;
        $fDiffPreis = (float)($fMax - $fMin) * 1000;
        $nMaxSteps  = $this->getConfig('navigationsfilter')['preisspannenfilter_anzeige_berechnung'] === 'M'
            ? 10
            : 5;
        foreach ($fStepWert_arr as $i => $fStepWert) {
            if (($fDiffPreis / (float)($fStepWert * 1000)) < $nMaxSteps) {
                $nStep = $i;
                break;
            }
        }
        $fMax           *= 1000.0;
        $fMin           *= 1000.0;
        $fStepWert      = $fStepWert_arr[$nStep] * 1000;
        $fMaxPreis      = \round(((($fMax * 100) - (($fMax * 100) % ($fStepWert * 100))) + ($fStepWert * 100)) / 100);
        $fMinPreis      = \round((($fMin * 100) - (($fMin * 100) % ($fStepWert * 100))) / 100);
        $fDiffPreis     = $fMaxPreis - $fMinPreis;
        $nAnzahlSpannen = \round($fDiffPreis / $fStepWert);

        $oObject                 = new \stdClass();
        $oObject->fMaxPreis      = $fMaxPreis / 1000;
        $oObject->fMinPreis      = $fMinPreis / 1000;
        $oObject->fStep          = $fStepWert_arr[$nStep];
        $oObject->fDiffPreis     = $fDiffPreis / 1000;
        $oObject->nAnzahlSpannen = $nAnzahlSpannen;

        return $oObject;
    }
}
