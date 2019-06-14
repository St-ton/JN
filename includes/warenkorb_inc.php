<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTL\Helpers\Cart;

/**
 * @param array $nPos_arr
 * @deprecated since 5.0.0
 */
function loescheWarenkorbPositionen($nPos_arr)
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    Cart::deleteCartItems($nPos_arr);
}

/**
 * @param int $nPos
 * @deprecated since 5.0.0
 */
function loescheWarenkorbPosition($nPos)
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    Cart::deleteCartItems([$nPos]);
}

/**
 * @deprecated since 5.0.0
 */
function uebernehmeWarenkorbAenderungen()
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    Cart::applyCartChanges();
}

/**
 * @return string
 * @deprecated since 5.0.0
 */
function checkeSchnellkauf()
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    return Cart::checkQuickBuy();
}

/**
 * @deprecated since 5.0.0
 */
function loescheAlleSpezialPos()
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    Cart::deleteAllSpecialItems();
}

/**
 * @return stdClass
 * @deprecated since 5.0.0
 */
function gibXSelling()
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    return Cart::getXSelling();
}

/**
 * @param array $conf
 * @return array
 * @deprecated since 5.0.0
 */
function gibGratisGeschenke(array $conf)
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    return Cart::getFreeGifts($conf);
}

/**
 * Schaut nach ob eine Bestellmenge > Lagersbestand ist und falls dies erlaubt ist, gibt es einen Hinweis
 *
 * @param array $conf
 * @return string
 * @deprecated since 5.0.0
 */
function pruefeBestellMengeUndLagerbestand($conf = [])
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    return Cart::checkOrderAmountAndStock($conf);
}

/**
 * Nachschauen ob beim Konfigartikel alle Pflichtkomponenten vorhanden sind, andernfalls löschen
 * @deprecated since 5.0.0
 */
function validiereWarenkorbKonfig()
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    Cart::validateCartConfig();
}
