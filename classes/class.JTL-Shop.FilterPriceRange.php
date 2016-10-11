<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class FilterPriceRange
 */
class FilterPriceRange extends AbstractFilter implements IFilter
{
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
     * @param int $id
     * @return $this
     */
    public function setID($id)
    {
        $this->cWert = (int)$id;

        return $this;
    }

    /**
     * @return int
     */
    public function getID()
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
     * @param int   $id
     * @param array $languages
     * @return $this
     */
    public function init($id, $languages)
    {
        list($fVon, $fBis) = explode('_', $id);
        $this->fVon  = doubleval($fVon);
        $this->fBis  = doubleval($fBis);
        $this->cWert = $this->fVon . '_' . $this->fBis;
        //localize prices
        $this->cVonLocalized = gibPreisLocalizedOhneFaktor($this->fVon);
        $this->cBisLocalized = gibPreisLocalizedOhneFaktor($this->fBis);
        $this->isInitialized = true;

        $oFilter = new stdClass();
        $oFilter->cJoin = "JOIN tpreise ON tartikel.kArtikel = tpreise.kArtikel AND tpreise.kKundengruppe = " . (int)$_SESSION['Kundengruppe']->kKundengruppe . "
                            LEFT JOIN tartikelkategorierabatt ON tartikelkategorierabatt.kKundengruppe = " . (int)$_SESSION['Kundengruppe']->kKundengruppe . "
                                AND tartikelkategorierabatt.kArtikel = tartikel.kArtikel
                            LEFT JOIN tartikelsonderpreis ON tartikelsonderpreis.kArtikel = tartikel.kArtikel
                                AND tartikelsonderpreis.cAktiv = 'Y'
                                AND tartikelsonderpreis.dStart <= now()
                                AND (tartikelsonderpreis.dEnde >= CURDATE() OR tartikelsonderpreis.dEnde = '0000-00-00')
                            LEFT JOIN tsonderpreise ON tartikelsonderpreis.kArtikelSonderpreis = tsonderpreise.kArtikelSonderpreis
                                AND tsonderpreise.kKundengruppe = " . (int)$_SESSION['Kundengruppe']->kKundengruppe;
        $oFilter->cWhere = '';

        $fKundenrabatt = 0.0;
        if (isset($_SESSION['Kunde']->fRabatt) && $_SESSION['Kunde']->fRabatt > 0) {
            $fKundenrabatt = $_SESSION['Kunde']->fRabatt;
        }

        $nSteuersatzKeys_arr = array_keys($_SESSION['Steuersatz']);
        // bis
        if (isset($_SESSION['Kundengruppe']->nNettoPreise) && intval($_SESSION['Kundengruppe']->nNettoPreise) > 0) {
            $oFilter->cWhere .= " ROUND(LEAST((tpreise.fVKNetto * " . $_SESSION['Waehrung']->fFaktor . ") * ((100 - GREATEST(IFNULL(tartikelkategorierabatt.fRabatt, 0), " .
                $_SESSION['Kundengruppe']->fRabatt . ", " . $fKundenrabatt . ", 0)) / 100), IFNULL(tsonderpreise.fNettoPreis, (tpreise.fVKNetto * " . $_SESSION['Waehrung']->fFaktor . "))), 2)";
        } else {
            foreach ($nSteuersatzKeys_arr as $nSteuersatzKeys) {
                $fSteuersatz = floatval($_SESSION['Steuersatz'][$nSteuersatzKeys]);
                $oFilter->cWhere .= " IF(tartikel.kSteuerklasse = " . $nSteuersatzKeys . ",
                            ROUND(LEAST(tpreise.fVKNetto * ((100 - GREATEST(IFNULL(tartikelkategorierabatt.fRabatt, 0), " .
                    $_SESSION['Kundengruppe']->fRabatt . ", " . $fKundenrabatt . ", 0)) / 100), IFNULL(tsonderpreise.fNettoPreis, (tpreise.fVKNetto * " .
                    $_SESSION['Waehrung']->fFaktor . "))) * ((100 + " . $fSteuersatz . ") / 100
                        ), 2),";
            }
        }

        if (intval($_SESSION['Kundengruppe']->nNettoPreise) === 0) {
            $oFilter->cWhere .= "0";

            $count = count($nSteuersatzKeys_arr);
            for ($x = 0; $x < $count; $x++) {
                $oFilter->cWhere .= ")";
            }
        }
        $oFilter->cWhere .= " < " . $this->fBis . " AND ";
        // von
        if (intval($_SESSION['Kundengruppe']->nNettoPreise) > 0) {
            $oFilter->cWhere .= " ROUND(LEAST(tpreise.fVKNetto * ((100 - GREATEST(IFNULL(tartikelkategorierabatt.fRabatt, 0), " .
                $_SESSION['Kundengruppe']->fRabatt . ", " . $fKundenrabatt . ", 0)) / 100), IFNULL(tsonderpreise.fNettoPreis, (tpreise.fVKNetto * " .
                $_SESSION['Waehrung']->fFaktor . "))), 2)";
        } else {
            foreach ($nSteuersatzKeys_arr as $nSteuersatzKeys) {
                $fSteuersatz = floatval($_SESSION['Steuersatz'][$nSteuersatzKeys]);
                $oFilter->cWhere .= " IF(tartikel.kSteuerklasse = " . $nSteuersatzKeys . ",
                            ROUND(LEAST(tpreise.fVKNetto * ((100 - GREATEST(IFNULL(tartikelkategorierabatt.fRabatt, 0), " .
                    $_SESSION['Kundengruppe']->fRabatt . ", " . $fKundenrabatt . ", 0)) / 100), IFNULL(tsonderpreise.fNettoPreis, (tpreise.fVKNetto * " .
                    $_SESSION['Waehrung']->fFaktor . "))) * ((100 + " . $fSteuersatz . ") / 100
                        ), 2),";
            }
        }
        if (intval($_SESSION['Kundengruppe']->nNettoPreise) === 0) {
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
     * @return string
     */
    public function getSQLJoin()
    {
        return $this->oFilter->cJoin;
    }
}
