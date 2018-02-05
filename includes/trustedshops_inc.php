<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * @return stdClass
 */
function gibTrustedShops()
{
    unset($_SESSION['TrustedShops'], $oTrustedShops);
    $oTrustedShops = new TrustedShops(-1, StringHandler::convertISO2ISO639(Shop::getLanguageCode()));
    $oTrustedShops->holeKaeuferschutzProdukteDB(StringHandler::convertISO2ISO639(Shop::getLanguageCode()), true);
    // Hole alle Käuferschutzprodukte, die in der DB hinterlegt sind
    $oTrustedShopsTMP = new stdClass();
    $cLandISO = $_SESSION['Lieferadresse']->cLand;
    if (!$cLandISO) {
        $cLandISO = $_SESSION['Kunde']->cLand;
    }
    // Prüfe, ob TS ID noch gültig ist
    if ($oTrustedShops->pruefeZertifikat(StringHandler::convertISO2ISO639(Shop::getLanguageCode())) === 1) {
        // Gib nur die Informationen weiter, die das Template auch braucht
        $oTrustedShopsTMP->nAktiv                       = $oTrustedShops->nAktiv;
        $oTrustedShopsTMP->eType                        = $oTrustedShops->eType;
        $oTrustedShopsTMP->cId                          = $oTrustedShops->tsId;
        $oTrustedShopsTMP->cISOSprache                  = $oTrustedShops->oZertifikat->cISOSprache;
        $oTrustedShopsTMP->oKaeuferschutzProdukteDB     = $oTrustedShops->oKaeuferschutzProdukteDB;
        $oTrustedShopsTMP->oKaeuferschutzProdukte       = $oTrustedShops->oKaeuferschutzProdukte;
        if (isset($oTrustedShopsTMP->oKaeuferschutzProdukte->item)) {
            $oTrustedShopsTMP->oKaeuferschutzProdukte->item = filterNichtGebrauchteKaeuferschutzProdukte(
                $oTrustedShops->oKaeuferschutzProdukte->item,
                Session::Cart()->gibGesamtsummeWaren(false) *
                ((100 + (float)$_SESSION['Steuersatz'][Session::Cart()->gibVersandkostenSteuerklasse($cLandISO)]) / 100)
            );
        }
        $oTrustedShopsTMP->cLogoURL                 = $oTrustedShops->cLogoURL;
        $oTrustedShopsTMP->cSpeicherungURL          = $oTrustedShops->cSpeicherungURL;
        $oTrustedShopsTMP->cBedingungURL            = $oTrustedShops->cBedingungURL;
        $oTrustedShopsTMP->cBoxText                 = $oTrustedShops->cBoxText;
        $oTrustedShopsTMP->cVorausgewaehltesProdukt = isset($oTrustedShops->oKaeuferschutzProdukte->item)
            ? gibVorausgewaehltesProdukt(
                $oTrustedShops->oKaeuferschutzProdukte->item,
                Session::Cart()->gibGesamtsummeWaren(false) *
                ((100 + (float)$_SESSION['Steuersatz'][Session::Cart()->gibVersandkostenSteuerklasse($cLandISO)]) / 100)
            )
            : '';
    }

    if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
        Jtllog::writeLog(
            "Der TrustedShops Käuferschutz im Bestellvorgang wurde mit folgendem Ergebnis geladen: " .
                print_r($oTrustedShopsTMP, true),
            JTLLOG_LEVEL_DEBUG
        );
    }

    return $oTrustedShopsTMP;
}

/**
 * Filter alle Käuferschutzprodukte aus den Produkten in der DB, die für die Warensumme keinen Sinn machen
 *
 * @param array $oKaeuferschutzProdukte_arr
 * @param float $fGesamtSumme
 * @return array
 */
function filterNichtGebrauchteKaeuferschutzProdukte($oKaeuferschutzProdukte_arr, $fGesamtSumme)
{
    $oKaeuferschutzProdukteFilter_arr = [];
    if (is_array($oKaeuferschutzProdukte_arr) && count($oKaeuferschutzProdukte_arr) > 0) {
        foreach ($oKaeuferschutzProdukte_arr as $oKaeuferschutzProdukte) {
            $oKaeuferschutzProdukteFilter_arr[] = $oKaeuferschutzProdukte;
            if ((float)$fGesamtSumme < (float)$oKaeuferschutzProdukte->protectedAmountDecimal) {
                break;
            }
        }
    }

    return $oKaeuferschutzProdukteFilter_arr;
}

/**
 * Liefer ein Assoc Array mit tsProductID als Keys + Preisen als Werte
 *
 * @param array $oKaeuferschutzProdukte_arr
 * @return array
 */
function gibKaeuferschutzProdukteAssocID($oKaeuferschutzProdukte_arr)
{
    $oKaeuferschutzProdukteAssocID_arr = [];
    if (is_array($oKaeuferschutzProdukte_arr) && count($oKaeuferschutzProdukte_arr) > 0) {
        foreach ($oKaeuferschutzProdukte_arr as $oKaeuferschutzProdukte) {
            $oKaeuferschutzProdukteAssocID_arr[$oKaeuferschutzProdukte->tsProductID] = $oKaeuferschutzProdukte->netFee;
        }
    }

    return $oKaeuferschutzProdukteAssocID_arr;
}

/**
 * Liefer das Käuferschutzprodukt (tsProductID), welches vorausgewählt werden soll anhand der Warenkorb Summe
 *
 * @param array $oKaeuferschutzProdukte_arr
 * @param float $fGesamtSumme
 * @return string
 */
function gibVorausgewaehltesProdukt($oKaeuferschutzProdukte_arr, $fGesamtSumme)
{
    $tsProductID  = '';
    $fLetzterWert = 0.0;
    if (is_array($oKaeuferschutzProdukte_arr) && count($oKaeuferschutzProdukte_arr) > 0) {
        foreach ($oKaeuferschutzProdukte_arr as $oKaeuferschutzProdukte) {
            if ((float)$fGesamtSumme <= (float)$oKaeuferschutzProdukte->protectedAmountDecimal
                && ((float)$oKaeuferschutzProdukte->protectedAmountDecimal < $fLetzterWert || $fLetzterWert === 0.0)
            ) {
                $tsProductID  = $oKaeuferschutzProdukte->tsProductID;
                $fLetzterWert = (float)$oKaeuferschutzProdukte->protectedAmountDecimal;
            }
        }
    }

    return $tsProductID;
}
