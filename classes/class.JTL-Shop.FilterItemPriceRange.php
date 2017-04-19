<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class FilterItemPriceRange
 */
class FilterItemPriceRange extends AbstractFilter
{
    use FilterItemTrait;

    /**
     * @var float
     */
    public $fVon;

    /**
     * @var float
     */
    public $fBis;

    /**
     * @var string
     */
    public $cWert;

    /**
     * @var string
     */
    public $cVonLocalized;

    /**
     * @var string
     */
    public $cBisLocalized;

    /**
     * @var object
     */
    private $oFilter;

    /**
     * FilterItemPriceRange constructor.
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
        $this->urlParam    = 'pf';
        $this->urlParamSEO = null;
    }

    /**
     * @param int $id
     * @return $this
     */
    public function setValue($id)
    {
        $this->cWert = (int)$id;

        return $this;
    }

    /**
     * @return int
     */
    public function getValue()
    {
        return $this->cWert;
    }

    /**
     * @param array $languages
     * @return $this
     */
    public function setSeo($languages)
    {
        return $this;
    }

    /**
     * @param int|string|null $id
     * @return $this
     */
    public function init($id)
    {
        if ($id === null) {
            $id = '0_0';
        }
        list($fVon, $fBis) = explode('_', $id);
        $this->fVon  = (float)$fVon;
        $this->fBis  = (float)$fBis;
        $this->cWert = ($id === '0_0') ? 0 : ($this->fVon . '_' . $this->fBis);
        //localize prices
        $this->cVonLocalized = gibPreisLocalizedOhneFaktor($this->fVon);
        $this->cBisLocalized = gibPreisLocalizedOhneFaktor($this->fBis);
        $this->isInitialized = true;

        $oFilter         = new stdClass();
        $oFilter->cJoin  = "
            JOIN tpreise 
                ON tartikel.kArtikel = tpreise.kArtikel 
                AND tpreise.kKundengruppe = " . (int)$_SESSION['Kundengruppe']->kKundengruppe . "
            LEFT JOIN tartikelkategorierabatt 
                ON tartikelkategorierabatt.kKundengruppe = " . (int)$_SESSION['Kundengruppe']->kKundengruppe . "
                AND tartikelkategorierabatt.kArtikel = tartikel.kArtikel
            LEFT JOIN tartikelsonderpreis 
                ON tartikelsonderpreis.kArtikel = tartikel.kArtikel
                AND tartikelsonderpreis.cAktiv = 'Y'
                AND tartikelsonderpreis.dStart <= now()
                AND (tartikelsonderpreis.dEnde >= curDATE() 
                    OR tartikelsonderpreis.dEnde = '0000-00-00')
            LEFT JOIN tsonderpreise 
                ON tartikelsonderpreis.kArtikelSonderpreis = tsonderpreise.kArtikelSonderpreis
                AND tsonderpreise.kKundengruppe = " . (int)$_SESSION['Kundengruppe']->kKundengruppe;
        $oFilter->cWhere = '';

        $fKundenrabatt = 0.0;
        if (isset($_SESSION['Kunde']->fRabatt) && $_SESSION['Kunde']->fRabatt > 0) {
            $fKundenrabatt = $_SESSION['Kunde']->fRabatt;
        }

        $nSteuersatzKeys_arr = array_keys($_SESSION['Steuersatz']);
        // bis
        if (isset($_SESSION['Kundengruppe']->nNettoPreise) && (int)$_SESSION['Kundengruppe']->nNettoPreise > 0) {
            $oFilter->cWhere .= " ROUND(LEAST((tpreise.fVKNetto * " .
                $_SESSION['Waehrung']->fFaktor . ") * ((100 - GREATEST(IFNULL(tartikelkategorierabatt.fRabatt, 0), " .
                $_SESSION['Kundengruppe']->fRabatt . ", " . $fKundenrabatt .
                ", 0)) / 100), IFNULL(tsonderpreise.fNettoPreis, (tpreise.fVKNetto * " .
                $_SESSION['Waehrung']->fFaktor . "))), 2)";
        } else {
            foreach ($nSteuersatzKeys_arr as $nSteuersatzKeys) {
                $fSteuersatz = (float)$_SESSION['Steuersatz'][$nSteuersatzKeys];
                $oFilter->cWhere .= " IF(tartikel.kSteuerklasse = " . $nSteuersatzKeys . ",
                            ROUND(LEAST(tpreise.fVKNetto * ((100 - GREATEST(IFNULL(tartikelkategorierabatt.fRabatt, 0), " .
                    $_SESSION['Kundengruppe']->fRabatt . ", " . $fKundenrabatt .
                    ", 0)) / 100), IFNULL(tsonderpreise.fNettoPreis, (tpreise.fVKNetto * " .
                    $_SESSION['Waehrung']->fFaktor . "))) * ((100 + " . $fSteuersatz . ") / 100
                        ), 2),";
            }
        }

        if ((int)$_SESSION['Kundengruppe']->nNettoPreise === 0) {
            $oFilter->cWhere .= "0";

            $count = count($nSteuersatzKeys_arr);
            for ($x = 0; $x < $count; ++$x) {
                $oFilter->cWhere .= ")";
            }
        }
        $oFilter->cWhere .= " < " . $this->fBis . " AND ";
        // von
        if ((int)$_SESSION['Kundengruppe']->nNettoPreise > 0) {
            $oFilter->cWhere .= " ROUND(LEAST(tpreise.fVKNetto * ((100 - GREATEST(IFNULL(tartikelkategorierabatt.fRabatt, 0), " .
                $_SESSION['Kundengruppe']->fRabatt . ", " . $fKundenrabatt .
                ", 0)) / 100), IFNULL(tsonderpreise.fNettoPreis, (tpreise.fVKNetto * " .
                $_SESSION['Waehrung']->fFaktor . "))), 2)";
        } else {
            foreach ($nSteuersatzKeys_arr as $nSteuersatzKeys) {
                $fSteuersatz = (float)$_SESSION['Steuersatz'][$nSteuersatzKeys];
                $oFilter->cWhere .= " IF(tartikel.kSteuerklasse = " . $nSteuersatzKeys . ",
                            ROUND(LEAST(tpreise.fVKNetto * ((100 - GREATEST(IFNULL(tartikelkategorierabatt.fRabatt, 0), " .
                    $_SESSION['Kundengruppe']->fRabatt . ", " . $fKundenrabatt .
                    ", 0)) / 100), IFNULL(tsonderpreise.fNettoPreis, (tpreise.fVKNetto * " .
                    $_SESSION['Waehrung']->fFaktor . "))) * ((100 + " . $fSteuersatz . ") / 100
                        ), 2),";
            }
        }
        if ((int)$_SESSION['Kundengruppe']->nNettoPreise === 0) {
            $oFilter->cWhere .= "0";
            $count = count($nSteuersatzKeys_arr);
            for ($x = 0; $x < $count; $x++) {
                $oFilter->cWhere .= ")";
            }
        }
        $oFilter->cWhere .= " >= " . $this->fVon;

        $this->oFilter = $oFilter;

        return $this;
    }

    /**
     * @return string
     */
    public function getPrimaryKeyRow()
    {
        return '';
    }

    /**
     * @return string
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
        return $this->oFilter->cWhere;
    }

    /**
     * @return FilterJoin[]
     */
    public function getSQLJoin()
    {
        $res = [];

        $join = new FilterJoin();
        $join->setComment('join1 from FilterItemPriceRange')
             ->setType('JOIN')
             ->setTable('tpreise')
             ->setOn('tartikel.kArtikel = tpreise.kArtikel 
                          AND tpreise.kKundengruppe = ' . (int)$_SESSION['Kundengruppe']->kKundengruppe);
        $res[] = $join;

        $join = new FilterJoin();
        $join->setComment('join2 from FilterItemPriceRange')
             ->setType('LEFT JOIN')
             ->setTable('tartikelkategorierabatt')
             ->setOn('tartikelkategorierabatt.kKundengruppe = ' . (int)$_SESSION['Kundengruppe']->kKundengruppe .
                       ' AND tartikelkategorierabatt.kArtikel = tartikel.kArtikel');
        $res[] = $join;

        $join = new FilterJoin();
        $join->setComment('join3 from FilterItemPriceRange')
             ->setType('LEFT JOIN')
             ->setTable('tartikelsonderpreis')
             ->setOn("tartikelsonderpreis.kArtikel = tartikel.kArtikel
                 AND tartikelsonderpreis.cAktiv = 'Y'
                 AND tartikelsonderpreis.dStart <= now()
                 AND (tartikelsonderpreis.dEnde >= curDATE() OR tartikelsonderpreis.dEnde = '0000-00-00')");
        $res[] = $join;

        $join = new FilterJoin();
        $join->setComment('join4 from FilterItemPriceRange')
             ->setType('LEFT JOIN')
             ->setTable('tsonderpreise')
             ->setOn('tartikelsonderpreis.kArtikelSonderpreis = tsonderpreise.kArtikelSonderpreis 
                          AND tsonderpreise.kKundengruppe = ' . (int)$_SESSION['Kundengruppe']->kKundengruppe);
        $res[] = $join;

        return $res;
    }

    /**
     * @param object     $oPreis
     * @param object     $currency
     * @param array|null $oPreisspannenfilter_arr
     * @return string
     */
    public function getPriceRangeSQL($oPreis, $currency, $oPreisspannenfilter_arr = null)
    {
        $cSQL          = '';
        $fKundenrabatt = 0.0;
        if (isset($_SESSION['Kunde']->fRabatt) && $_SESSION['Kunde']->fRabatt > 0) {
            $fKundenrabatt = $_SESSION['Kunde']->fRabatt;
        }
        // Wenn Option vorhanden, dann nur Spannen anzeigen, in denen Artikel vorhanden sind
        if ($this->getConfig()['navigationsfilter']['preisspannenfilter_anzeige_berechnung'] === 'A') {
//            $nPreisMax = $oPreis->fMaxPreis;
            $nPreisMin               = $oPreis->fMinPreis;
            $nStep                   = $oPreis->fStep;
            $oPreisspannenfilter_arr = [];
            for ($i = 0; $i < $oPreis->nAnzahlSpannen; ++$i) {
                $fakePriceRange              = new stdClass();
                $fakePriceRange->nBis        = ($nPreisMin + ($i + 1) * $nStep);
                $oPreisspannenfilter_arr[$i] = $fakePriceRange;
            }
        }

        if (is_array($oPreisspannenfilter_arr)) {
            foreach ($oPreisspannenfilter_arr as $i => $oPreisspannenfilter) {
                $cSQL .= "COUNT(DISTINCT 
                    IF(";

                $nBis = $oPreisspannenfilter->nBis;
                // Finde den höchsten und kleinsten Steuersatz
                if (is_array($_SESSION['Steuersatz']) && (int)$_SESSION['Kundengruppe']->nNettoPreise === 0) {
                    $nSteuersatzKeys_arr = array_keys($_SESSION['Steuersatz']);
                    foreach ($nSteuersatzKeys_arr as $nSteuersatzKeys) {
                        $fSteuersatz = (float)$_SESSION['Steuersatz'][$nSteuersatzKeys];
                        $cSQL .= "IF(tartikel.kSteuerklasse = " . $nSteuersatzKeys . ",
                            ROUND(LEAST((tpreise.fVKNetto * " . $currency->fFaktor .
                            ") * ((100 - GREATEST(IFNULL(tartikelkategorierabatt.fRabatt, 0), " .
                            $_SESSION['Kundengruppe']->fRabatt . ", " . $fKundenrabatt .
                            ", 0)) / 100), IFNULL(tsonderpreise.fNettoPreis, (tpreise.fVKNetto * " .
                            $currency->fFaktor . "))) * ((100 + " . $fSteuersatz . ") / 100)
                        , 2),";
                    }
                    $cSQL .= "0";
                    $count = count($nSteuersatzKeys_arr);
                    for ($x = 0; $x < $count; $x++) {
                        $cSQL .= ")";
                    }
                } elseif ($_SESSION['Kundengruppe']->nNettoPreise > 0) {
                    $cSQL .= "ROUND(LEAST((tpreise.fVKNetto * " . $currency->fFaktor .
                        ") * ((100 - GREATEST(IFNULL(tartikelkategorierabatt.fRabatt, 0), " .
                        $_SESSION['Kundengruppe']->fRabatt . ", " . $fKundenrabatt .
                        ", 0)) / 100), IFNULL(tsonderpreise.fNettoPreis, (tpreise.fVKNetto * " .
                        $currency->fFaktor . "))), 2)";
                }

                $cSQL .= " < " . $nBis . ", tartikel.kArtikel, NULL)
                    ) AS anz" . $i . ", ";
            }
            $cSQL = substr($cSQL, 0, strlen($cSQL) - 2);
        }

        return $cSQL;
    }

    /**
     * @param int $mixed - product count
     * @return array
     */
    public function getOptions($mixed = null)
    {
        $naviFilter       = Shop::getNaviFilter();
        $productCount     = $mixed;
        $oPreisspanne_arr = [];
        // Prüfe ob es nur einen Artikel in der Artikelübersicht gibt, falls ja und es ist noch kein Preisspannenfilter gesetzt
        // dürfen keine Preisspannenfilter angezeigt werden
        if (($productCount === 1 && !$this->isInitialized()) ||
            $this->getConfig()['navigationsfilter']['preisspannenfilter_benutzen'] === 'N'
        ) {
            return $oPreisspanne_arr;
        }
        $currency = isset($_SESSION['Waehrung'])
            ? $_SESSION['Waehrung']
            : null;
        if (!isset($currency->kWaehrung)) {
            $currency = Shop::DB()->select('twaehrung', 'cStandard', 'Y');
        }

        $order = $naviFilter->getOrder();
        $state = $naviFilter->getCurrentStateData();

        $join = new FilterJoin();
        $join->setType('LEFT JOIN')
             ->setTable('tartikelkategorierabatt')
             ->setOn("tartikelkategorierabatt.kKundengruppe = " . $this->getCustomerGroupID() .
                 " AND tartikelkategorierabatt.kArtikel = tartikel.kArtikel");
        $state->joins[] = $join;

        $join = new FilterJoin();
        $join->setType('LEFT JOIN')
             ->setTable('tartikelsonderpreis')
             ->setOn("tartikelsonderpreis.kArtikel = tartikel.kArtikel
                        AND tartikelsonderpreis.cAktiv = 'Y'
                        AND tartikelsonderpreis.dStart <= now()
                        AND (tartikelsonderpreis.dEnde >= CURDATe() OR tartikelsonderpreis.dEnde = '0000-00-00')");
        $state->joins[] = $join;

        $join = new FilterJoin();
        $join->setType('LEFT JOIN')
             ->setTable('tsonderpreise')
             ->setOn("tartikelsonderpreis.kArtikelSonderpreis = tsonderpreise.kArtikelSonderpreis 
                        AND tsonderpreise.kKundengruppe = " . $this->getCustomerGroupID());
        $state->joins[] = $join;

        $state->joins[] = $order->join;

        // Automatisch
        if ($this->getConfig()['navigationsfilter']['preisspannenfilter_anzeige_berechnung'] === 'A') {
            $join = new FilterJoin();
            $join->setComment('join1 from FilterItemPriceRange::getOptions()')
                 ->setTable('tpreise')
                 ->setType('JOIN')
                 ->setOn('tpreise.kArtikel = tartikel.kArtikel 
                            AND tpreise.kKundengruppe = ' . $this->getCustomerGroupID());
            $state->joins[] = $join;

            $join = new FilterJoin();
            $join->setComment('join2 from FilterItemPriceRange::getOptions()')
                 ->setTable('tartikelsichtbarkeit')
                 ->setType('LEFT JOIN')
                 ->setOn('tartikel.kArtikel = tartikelsichtbarkeit.kArtikel 
                            AND tartikelsichtbarkeit.kKundengruppe = ' . $this->getCustomerGroupID());
            $state->joins[] = $join;

            //remove duplicate joins
            $joinedTables = [];
            foreach ($state->joins as $i => $stateJoin) {
                if (is_string($stateJoin)) {
                    throw new \InvalidArgumentException('getBaseQuery() got join as string: ' . $stateJoin);
                }
                if (!in_array($stateJoin->getTable(), $joinedTables)) {
                    $joinedTables[] = $stateJoin->getTable();
                } else {
                    unset($state->joins[$i]);
                }
            }
            // Finde den höchsten und kleinsten Steuersatz
            if (is_array($_SESSION['Steuersatz']) && $_SESSION['Kundengruppe']->nNettoPreise === '0') {
                $fSteuersatz_arr = [];
                foreach ($_SESSION['Steuersatz'] as $fSteuersatz) {
                    $fSteuersatz_arr[] = $fSteuersatz;
                }
                $fSteuersatzMax = count($fSteuersatz_arr) ? max($fSteuersatz_arr) : 0;
                $fSteuersatzMin = count($fSteuersatz_arr) ? min($fSteuersatz_arr) : 0;
            } elseif ($_SESSION['Kundengruppe']->nNettoPreise > 0) {
                $fSteuersatzMax = 0.0;
                $fSteuersatzMin = 0.0;
            }
            $fKundenrabatt     = (isset($_SESSION['Kunde']->fRabatt) && $_SESSION['Kunde']->fRabatt > 0)
                ? $_SESSION['Kunde']->fRabatt
                : 0.0;
            $state->conditions = implode(' AND ', array_map(function ($a) {
                return (is_string($a))
                    ? ($a)
                    : ('(' . implode(' OR ', $a) . ')');
            }, $state->conditions));
            if (!empty($state->conditions)) {
                $state->conditions = ' AND ' . $state->conditions;
            }
            $state->having             = implode(' AND ', $state->having);
            $state->joins              = implode("\n", $state->joins);
            $qry                       = "SELECT max(ssMerkmal.fMax) AS fMax, min(ssMerkmal.fMin) AS fMin
                FROM (
                    SELECT ROUND(
                        LEAST(
                            (tpreise.fVKNetto * " . $currency->fFaktor . ") *
                            ((100 - GREATEST(IFNULL(tartikelkategorierabatt.fRabatt, 0), " .
                $_SESSION['Kundengruppe']->fRabatt . ", " . $fKundenrabatt . ", 0)) / 100),
                            IFNULL(tsonderpreise.fNettoPreis, (tpreise.fVKNetto * " .
                $currency->fFaktor . "))) * ((100 + " . $fSteuersatzMax . ") / 100), 2) AS fMax,
                 ROUND(LEAST((tpreise.fVKNetto * " . $currency->fFaktor . ") *
                 ((100 - greatest(IFNULL(tartikelkategorierabatt.fRabatt, 0), " .
                $_SESSION['Kundengruppe']->fRabatt . ", " . $fKundenrabatt . ", 0)) / 100),
                 IFNULL(tsonderpreise.fNettoPreis, (tpreise.fVKNetto * " .
                $currency->fFaktor . "))) * ((100 + " . $fSteuersatzMin . ") / 100), 2) AS fMin
                FROM tartikel
                " . $state->joins . "
                WHERE tartikelsichtbarkeit.kArtikel IS NULL
                    AND tartikel.kVaterArtikel = 0
                    " . $naviFilter->getStorageFilter() . "
                    " . $state->conditions . "
                GROUP BY tartikel.kArtikel
                " . $state->having . "
            ) AS ssMerkmal";
            $oPreisspannenFilterMaxMin = Shop::DB()->query($qry, 1);
            if (isset($oPreisspannenFilterMaxMin->fMax) && $oPreisspannenFilterMaxMin->fMax > 0) {
                // Berechnet Max, Min, Step, Anzahl, Diff und liefert diese Werte in einem Objekt
                $oPreis = $this->calculateSteps($oPreisspannenFilterMaxMin->fMax * $currency->fFaktor,
                    $oPreisspannenFilterMaxMin->fMin * $currency->fFaktor);
                // Begrenzung der Preisspannen bei zu großen Preisdifferenzen
                $oPreis->nAnzahlSpannen = min(20, (int)$oPreis->nAnzahlSpannen);
                $cSelectSQL             = '';
                for ($i = 0; $i < $oPreis->nAnzahlSpannen; ++$i) {
                    if ($i > 0) {
                        $cSelectSQL .= ', ';
                    }
                    $cSelectSQL .= " SUM(ssMerkmal.anz" . $i . ") AS anz" . $i;
                }
                $qry                   = "SELECT " . $cSelectSQL . "
                    FROM
                    (
                        SELECT " . $this->getPriceRangeSQL($oPreis, $currency) . "
                        FROM tartikel " .
                    $state->joins . "
                        WHERE tartikelsichtbarkeit.kArtikel IS NULL
                            AND tartikel.kVaterArtikel = 0
                            " . $naviFilter->getStorageFilter() . "
                            " . $state->conditions . "
                        GROUP BY tartikel.kArtikel
                        " . $state->having . "
                    ) AS ssMerkmal
                    ";
                $oPreisspannenFilterDB     = Shop::DB()->query($qry, 1);
                $nPreisspannenAnzahl_arr   = (is_object($oPreisspannenFilterDB))
                    ? get_object_vars($oPreisspannenFilterDB)
                    : null;
                $oPreisspannenFilterDB_arr = [];
                for ($i = 0; $i < $oPreis->nAnzahlSpannen; ++$i) {
                    $sub                         = ($i === 0)
                        ? 0
                        : $nPreisspannenAnzahl_arr['anz' . ($i - 1)];
                    $oPreisspannenFilterDB_arr[] = ($nPreisspannenAnzahl_arr['anz' . $i] - $sub);
                }
                $nPreisMax        = $oPreis->fMaxPreis;
                $nPreisMin        = $oPreis->fMinPreis;
                $nStep            = $oPreis->fStep;
                $nAnzahlSpannen   = (int)$oPreis->nAnzahlSpannen;
                $additionalFilter = new FilterItemPriceRange(
                    $this->getLanguageID(),
                    $this->getCustomerGroupID(),
                    $this->getConfig(),
                    $this->getAvailableLanguages()
                );
                for ($i = 0; $i < $nAnzahlSpannen; ++$i) {
                    $oPreisspannenFilter       = new stdClass();
                    $oPreisspannenFilter->nVon = ($nPreisMin + $i * $nStep);
                    $oPreisspannenFilter->nBis = ($nPreisMin + ($i + 1) * $nStep);
                    if ($oPreisspannenFilter->nBis > $nPreisMax) {
                        if ($oPreisspannenFilter->nVon >= $nPreisMax) {
                            $oPreisspannenFilter->nVon = ($nPreisMin + ($i - 1) * $nStep);
                        }

                        if ($oPreisspannenFilter->nBis > $nPreisMax) {
                            $oPreisspannenFilter->nBis = $nPreisMax;
                        }
                    }
                    // Localize Preise
                    $oPreisspannenFilter->cVonLocalized  = gibPreisLocalizedOhneFaktor(
                        $oPreisspannenFilter->nVon,
                        $currency
                    );
                    $oPreisspannenFilter->cBisLocalized  = gibPreisLocalizedOhneFaktor(
                        $oPreisspannenFilter->nBis,
                        $currency
                    );
                    $oPreisspannenFilter->nAnzahlArtikel = $oPreisspannenFilterDB_arr[$i];
                    $oPreisspannenFilter->cURL           = $naviFilter->getURL(
                        true,
                        $additionalFilter->init($oPreisspannenFilter->nVon . '_' . $oPreisspannenFilter->nBis)
                    );
                    $oPreisspanne_arr[]                  = $oPreisspannenFilter;
                }
            }
        } else {
            $oPreisspannenfilter_arr = Shop::DB()->query("SELECT * FROM tpreisspannenfilter", 2);
            if (is_array($oPreisspannenfilter_arr) && count($oPreisspannenfilter_arr) > 0) {
                // Berechnet Max, Min, Step, Anzahl, Diff
                $oPreis = $this->calculateSteps(
                    $oPreisspannenfilter_arr[count($oPreisspannenfilter_arr) - 1]->nBis * $currency->fFaktor,
                    $oPreisspannenfilter_arr[0]->nVon * $currency->fFaktor
                );
                if (!$oPreis->nAnzahlSpannen || !$oPreis->fMaxPreis) {
                    $res = [];

//                    Shop::Cache()->set($cacheID, $res, [CACHING_GROUP_CATEGORY]);

                    return $res;
                }
                $cSelectSQL = '';
                $count      = count($oPreisspannenfilter_arr);
                for ($i = 0; $i < $count; ++$i) {
                    if ($i > 0) {
                        $cSelectSQL .= ', ';
                    }
                    $cSelectSQL .= "SUM(ssMerkmal.anz" . $i . ") AS anz" . $i;
                }

                $oPreisspannenFilterDB     = Shop::DB()->query(
                    "SELECT " . $cSelectSQL . "
                        FROM
                        (
                            SELECT " . $this->getPriceRangeSQL($oPreis, $currency, $oPreisspannenfilter_arr) . "
                                FROM tartikel " .
                    $state->joins . "
                                WHERE tartikelsichtbarkeit.kArtikel IS NULL
                                    AND tartikel.kVaterArtikel = 0
                                    " . $naviFilter->getStorageFilter() . "
                                    " . $state->where . "
                                GROUP BY tartikel.kArtikel
                                " . $state->having . "
                        ) AS ssMerkmal
                    ", 1
                );
                $nPreisspannenAnzahl_arr   = get_object_vars($oPreisspannenFilterDB);
                $oPreisspannenFilterDB_arr = [];
                if (is_array($nPreisspannenAnzahl_arr)) {
                    $count = count($nPreisspannenAnzahl_arr);
                    for ($i = 0; $i < $count; ++$i) {
                        $sub                         = ($i === 0)
                            ? 0
                            : $nPreisspannenAnzahl_arr['anz' . ($i - 1)];
                        $oPreisspannenFilterDB_arr[] = ($nPreisspannenAnzahl_arr['anz' . $i] - $sub);
                    }
                }
                $additionalFilter = new FilterItemPriceRange(
                    $this->getLanguageID(),
                    $this->getCustomerGroupID(),
                    $this->getConfig(),
                    $this->getAvailableLanguages()
                );
                foreach ($oPreisspannenfilter_arr as $i => $oPreisspannenfilter) {
                    $oPreisspannenfilterTMP                 = new stdClass();
                    $oPreisspannenfilterTMP->nVon           = $oPreisspannenfilter->nVon;
                    $oPreisspannenfilterTMP->nBis           = $oPreisspannenfilter->nBis;
                    $oPreisspannenfilterTMP->nAnzahlArtikel = (int)$oPreisspannenFilterDB_arr[$i];
                    // Localize Preise
                    $oPreisspannenfilterTMP->cVonLocalized = gibPreisLocalizedOhneFaktor(
                        $oPreisspannenfilterTMP->nVon,
                        $currency
                    );
                    $oPreisspannenfilterTMP->cBisLocalized = gibPreisLocalizedOhneFaktor(
                        $oPreisspannenfilterTMP->nBis,
                        $currency
                    );
                    $oPreisspannenfilterTMP->cURL            = $naviFilter->getURL(
                        true,
                        $additionalFilter->init($oPreisspannenfilterTMP->nVon . '_' . $oPreisspannenfilterTMP->nBis)
                    );
                    $oPreisspanne_arr[]                      = $oPreisspannenfilterTMP;
                }
            }
        }
        // Preisspannen ohne Artikel ausblenden (falls im Backend eingestellt)
        if (count($oPreisspanne_arr) > 0 &&
            $this->getConfig()['navigationsfilter']['preisspannenfilter_spannen_ausblenden'] === 'Y'
        ) {
            $oPreisspanneTMP_arr = [];
            foreach ($oPreisspanne_arr as $oPreisspanne) {
                if ($oPreisspanne->nAnzahlArtikel > 0) {
                    $oPreisspanneTMP_arr[] = $oPreisspanne;
                }
            }
            $oPreisspanne_arr = $oPreisspanneTMP_arr;
        }

        return $oPreisspanne_arr;
    }

    /**
     * has to be public for compatibility with filter_inc.php
     *
     * @param float $fMax
     * @param float $fMin
     * @return stdClass
     * @former berechneMaxMinStep
     */
    public function calculateSteps($fMax, $fMin)
    {
        $fStepWert_arr = [
            0.001, 0.005, 0.01, 0.05, 0.10, 0.25, 0.5, 1.0, 2.5, 5.0, 7.5,
            10.0, 12.5, 15.0, 20.0, 25.0, 50.0, 100.0, 250.0, 300.0, 350.0,
            400.0, 500.0, 750.0, 1000.0, 1500.0, 2500.0, 5000.0, 10000.0,
            25000.0, 30000.0, 40000.0, 50000.0, 60000.0, 75000.0, 100000.0,
            150000.0, 250000.0, 350000.0, 400000.0, 500000.0, 550000.0,
            600000.0, 750000.0, 1000000.0, 1500000.0, 5000000.0, 7500000.0,
            10000000.0, 12500000.0, 15000000.0, 25000000.0, 50000000.0,
            100000000.0
        ];
        $nStep      = 10;
        $fDiffPreis = (float)($fMax - $fMin) * 1000;
        $nMaxSteps  = ($this->getConfig()['navigationsfilter']['preisspannenfilter_anzeige_berechnung'] === 'M')
            ? 10
            : 5;
        foreach ($fStepWert_arr as $i => $fStepWert) {
            if (($fDiffPreis / (float)($fStepWert * 1000)) < $nMaxSteps) {
                $nStep = $i;
                break;
            }
        }
        $fStepWert = $fStepWert_arr[$nStep] * 1000;
        $fMax *= 1000;
        $fMin *= 1000;
        $fMaxPreis      = round(((($fMax * 100) - (($fMax * 100) % ($fStepWert * 100))) + ($fStepWert * 100)) / 100, 0);
        $fMinPreis      = round((($fMin * 100) - (($fMin * 100) % ($fStepWert * 100))) / 100, 0);
        $fDiffPreis     = $fMaxPreis - $fMinPreis;
        $nAnzahlSpannen = round($fDiffPreis / $fStepWert, 0);

        $oObject                 = new stdClass();
        $oObject->fMaxPreis      = $fMaxPreis / 1000;
        $oObject->fMinPreis      = $fMinPreis / 1000;
        $oObject->fStep          = $fStepWert_arr[$nStep];
        $oObject->fDiffPreis     = $fDiffPreis / 1000;
        $oObject->nAnzahlSpannen = $nAnzahlSpannen;

        return $oObject;
    }
}
