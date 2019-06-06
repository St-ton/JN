<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * @return stdClass
 * @deprecated since 5.0.0
 */
function gibRedirect()
{
    return new stdClass();
}

/**
 * Schaut nach dem Login, ob Kategorien nicht sichtbar sein dürfen und löscht eventuell diese aus der Session
 *
 * @return bool
 * @deprecated since 5.0.0
 */
function pruefeKategorieSichtbarkeit()
{
    return true;
}

/**
 * @return bool
 * @deprecated since 5.0.0
 */
function setzeWarenkorbPersInWarenkorb(): bool
{
    return false;
}

/**
 * Prüfe ob Artikel im Warenkorb vorhanden sind, welche für den aktuellen Kunden nicht mehr sichtbar sein dürfen
 *
 * @deprecated since 5.0.0
 */
function pruefeWarenkorbArtikelSichtbarkeit(): void
{
}

/**
 * @deprecated since 5.0.0
 */
function fuehreLoginAus(): void
{
}
