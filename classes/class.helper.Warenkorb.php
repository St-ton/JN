<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class WarenkorbHelper
 */
class WarenkorbHelper
{
    const NET   = 0;
    const GROSS = 1;

    /**
     * @param int $decimals
     * @return object
     */
    public function getTotal($decimals = 0)
    {
        $info            = new stdClass();
        $info->type      = $_SESSION['Kundengruppe']->nNettoPreise == 1 ? self::NET : self::GROSS;
        $info->currency  = null;
        $info->article   = array(0, 0);
        $info->shipping  = array(0, 0);
        $info->discount  = array(0, 0);
        $info->surcharge = array(0, 0);
        $info->total     = array(0, 0);
        $info->items     = array();

        $info->currency = $this->getCurrency();

        foreach ($_SESSION['Warenkorb']->PositionenArr as $oPosition) {
            $amountItem = $oPosition->fPreisEinzelNetto;
            if (isset($oPosition->WarenkorbPosEigenschaftArr) && is_array($oPosition->WarenkorbPosEigenschaftArr) &&
                (!isset($oPosition->Artikel->kVaterArtikel) || (int)$oPosition->Artikel->kVaterArtikel === 0)
            ) {
                foreach ($oPosition->WarenkorbPosEigenschaftArr as $oWarenkorbPosEigenschaft) {
                    if ($oWarenkorbPosEigenschaft->fAufpreis != 0) {
                        $amountItem += $oWarenkorbPosEigenschaft->fAufpreis;
                    }
                }
            }
            $amount      = $amountItem * $info->currency->fFaktor;
            $amountGross = $amount * ((100 + gibUst($oPosition->kSteuerklasse)) / 100);

            switch ($oPosition->nPosTyp) {
                case C_WARENKORBPOS_TYP_ARTIKEL: {
                    $item = (object) [
                        'name'     => '',
                        'quantity' => 1,
                        'amount'   => []
                    ];

                    if (is_array($oPosition->cName)) {
                        $langIso    = $_SESSION['cISOSprache'];
                        $item->name = $oPosition->cName[$langIso];
                    } else {
                        $item->name = $oPosition->cName;
                    }

                    $item->name = html_entity_decode($item->name);

                    $item->amount = [
                        self::NET   => $amount,
                        self::GROSS => $amountGross
                    ];

                    if ((int) $oPosition->nAnzahl != $oPosition->nAnzahl) {
                        $item->amount[self::NET] *= $oPosition->nAnzahl;
                        $item->amount[self::GROSS] *= $oPosition->nAnzahl;

                        $item->name = sprintf('%g %s %s',
                            (float) $oPosition->nAnzahl,
                            $oPosition->Artikel->cEinheit
                                ? $oPosition->Artikel->cEinheit
                                : 'x', $item->name);
                    } else {
                        $item->quantity = (int) $oPosition->nAnzahl;
                    }

                    $info->article[self::NET] += $item->amount[self::NET] * $item->quantity;
                    $info->article[self::GROSS] += $item->amount[self::GROSS] * $item->quantity;

                    $info->items[] = $item;
                    break;
                }

                case C_WARENKORBPOS_TYP_VERSANDPOS:
                case C_WARENKORBPOS_TYP_VERSANDZUSCHLAG:
                case C_WARENKORBPOS_TYP_VERPACKUNG:
                case C_WARENKORBPOS_TYP_VERSAND_ARTIKELABHAENGIG: {
                    $info->shipping[self::NET] += $amount * $oPosition->nAnzahl;
                    $info->shipping[self::GROSS] += $amountGross * $oPosition->nAnzahl;
                    break;
                }

                case C_WARENKORBPOS_TYP_KUPON:
                case C_WARENKORBPOS_TYP_GUTSCHEIN:
                case C_WARENKORBPOS_TYP_NEUKUNDENKUPON: {
                    $info->discount[self::NET] += $amount * $oPosition->nAnzahl;
                    $info->discount[self::GROSS] += $amountGross * $oPosition->nAnzahl;
                    break;
                }

                case C_WARENKORBPOS_TYP_ZAHLUNGSART:
                    if ($amount >= 0) {
                        $info->surcharge[self::NET] += $amount * $oPosition->nAnzahl;
                        $info->surcharge[self::GROSS] += $amountGross * $oPosition->nAnzahl;
                    } else {
                        $amount = $amount * -1;
                        $info->discount[self::NET] += $amount * $oPosition->nAnzahl;
                        $info->discount[self::GROSS] += $amountGross * $oPosition->nAnzahl;
                    }
                    break;

                case C_WARENKORBPOS_TYP_NACHNAHMEGEBUEHR: {
                    $info->surcharge[self::NET] += $amount * $oPosition->nAnzahl;
                    $info->surcharge[self::GROSS] += $amountGross * $oPosition->nAnzahl;
                    break;
                }
            }
        }

        if (isset($_SESSION['Bestellung']) && $_SESSION['Bestellung']->GuthabenNutzen === 1) {
            $amountGross = $_SESSION['Bestellung']->fGuthabenGenutzt * -1;
            $amount      = $amountGross;

            $info->discount[self::NET] += $amount;
            $info->discount[self::GROSS] += $amountGross;
        }

        // positive discount
        $info->discount[self::NET] *= -1;
        $info->discount[self::GROSS] *= -1;

        // total
        $info->total[self::NET]   = $info->article[self::NET] + $info->shipping[self::NET] - $info->discount[self::NET] + $info->surcharge[self::NET];
        $info->total[self::GROSS] = $info->article[self::GROSS] + $info->shipping[self::GROSS] - $info->discount[self::GROSS] + $info->surcharge[self::GROSS];

        $formatter = function ($prop) use ($decimals) {
            return [
                self::NET   => number_format($prop[self::NET], $decimals, '.', ''),
                self::GROSS => number_format($prop[self::GROSS], $decimals, '.', ''),
            ];
        };

        if ($decimals > 0) {
            $info->article   = $formatter($info->article);
            $info->shipping  = $formatter($info->shipping);
            $info->discount  = $formatter($info->discount);
            $info->surcharge = $formatter($info->surcharge);
            $info->total     = $formatter($info->total);

            foreach ($info->items as &$item) {
                $item->amount = $formatter($item->amount);
            }
        }

        return $info;
    }

    /**
     * @return Warenkorb
     */
    public function getObject()
    {
        return $_SESSION['Warenkorb'];
    }

    /**
     * @return Lieferadresse
     */
    public function getShippingAddress()
    {
        return $_SESSION['Lieferadresse'];
    }

    /**
     * @return Rechnungsadresse
     */
    public function getBillingAddress()
    {
        return $_SESSION['Rechnungsadresse'];
    }

    /**
     * @return Kunde
     */
    public function getCustomer()
    {
        return $_SESSION['Kunde'];
    }

    /**
     * @return currency
     */
    public function getCurrency()
    {
        return (is_object($_SESSION['Waehrung']) && $_SESSION['Waehrung']->kWaehrung) ?
            $_SESSION['Waehrung'] : gibStandardWaehrung();
    }

    /**
     * @return currency iso
     */
    public function getCurrencyISO()
    {
        return $this->getCurrency()->cISO;
    }

    /**
     * @return language iso
     */
    public function getLanguageISO()
    {
        return $_SESSION['cISOSprache'];
    }

    /**
     * @return state iso
     */
    public function getStateISO()
    {
        return PayPalHelper::isStateRequired($this->getLanguageISO())
            ? PayPalHelper::getStateISO(@$this->getShippingAddress()->cBundesland)
            : @$this->getShippingAddress()->cBundesland;
    }

    /**
     * @return return country iso
     */
    public function getCountryISO()
    {
        return PayPalHelper::getCountryISO($this->getShippingAddress()->cLand);
    }

    /**
     * @return string
     */
    public function getInvoiceID()
    {
        return;
    }

    /**
     * @return int
     */
    public function getIdentifier()
    {
        return 0;
    }
}
