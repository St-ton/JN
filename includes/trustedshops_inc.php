<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * @return stdClass
 * @deprecated since 5.0.0
 */
function gibTrustedShops()
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return TrustedShops::getTrustedShops();
}

/**
 * Filter alle Käuferschutzprodukte aus den Produkten in der DB, die für die Warensumme keinen Sinn machen
 *
 * @param array $oKaeuferschutzProdukte_arr
 * @param float $fGesamtSumme
 * @return array
 * @deprecated since 5.0.0
 */
function filterNichtGebrauchteKaeuferschutzProdukte($oKaeuferschutzProdukte_arr, $fGesamtSumme)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return TrustedShops::filterNichtGebrauchteKaeuferschutzProdukte($oKaeuferschutzProdukte_arr, $fGesamtSumme);
}

/**
 * Liefer ein Assoc Array mit tsProductID als Keys + Preisen als Werte
 *
 * @param array $oKaeuferschutzProdukte_arr
 * @return array
 * @deprecated since 5.0.0
 */
function gibKaeuferschutzProdukteAssocID($oKaeuferschutzProdukte_arr)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return TrustedShops::gibKaeuferschutzProdukteAssocID($oKaeuferschutzProdukte_arr);
}

/**
 * Liefer das Käuferschutzprodukt (tsProductID), welches vorausgewählt werden soll anhand der Warenkorb Summe
 *
 * @param array $oKaeuferschutzProdukte_arr
 * @param float $fGesamtSumme
 * @return string
 * @deprecated since 5.0.0
 */
function gibVorausgewaehltesProdukt($oKaeuferschutzProdukte_arr, $fGesamtSumme)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return TrustedShops::getPreSelectedProduct($oKaeuferschutzProdukte_arr, $fGesamtSumme);
}
