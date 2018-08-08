<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * @param array $nPos_arr
 * @deprecated since 5.0.0
 */
function loescheWarenkorbPositionen($nPos_arr)
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    WarenkorbHelper::deleteCartPositions($nPos_arr);
}

/**
 * @param int $nPos
 * @deprecated since 5.0.0
 */
function loescheWarenkorbPosition($nPos)
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    WarenkorbHelper::deleteCartPositions([$nPos]);
}

/**
 * @deprecated since 5.0.0
 */
function uebernehmeWarenkorbAenderungen()
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    WarenkorbHelper::applyCartChanges();
}

/**
 * @return string
 * @deprecated since 5.0.0
 */
function checkeSchnellkauf()
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    return WarenkorbHelper::checkQuickBuy();
}

/**
 * @deprecated since 5.0.0
 */
function loescheAlleSpezialPos()
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    WarenkorbHelper::deleteAllSpecialPositions();
}

/**
 * @return stdClass
 * @deprecated since 5.0.0
 */
function gibXSelling()
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    return WarenkorbHelper::getXSelling();
}

/**
 * @param array $Einstellungen
 * @return array
 * @deprecated since 5.0.0
 */
function gibGratisGeschenke(array $Einstellungen)
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    return WarenkorbHelper::getFreeGifts($Einstellungen);
}

/**
 * Schaut nach ob eine Bestellmenge > Lagersbestand ist und falls dies erlaubt ist, gibt es einen Hinweis
 *
 * @param array $Einstellungen
 * @return string
 * @deprecated since 5.0.0
 */
function pruefeBestellMengeUndLagerbestand($Einstellungen = [])
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    return WarenkorbHelper::checkOrderAmountAndStock($Einstellungen);
}

/**
 * Nachschauen ob beim Konfigartikel alle Pflichtkomponenten vorhanden sind, andernfalls löschen
 * @deprecated since 5.0.0
 */
function validiereWarenkorbKonfig()
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    WarenkorbHelper::validateCartConfig();
}
