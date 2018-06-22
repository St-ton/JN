<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Holt für einen Kunden die aktive Wunschliste (falls vorhanden) aus der DB und fügt diese in die Session
 * @deprecated since 5.0.0
 */
function setzeWunschlisteInSession()
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    Wunschliste::persistInSession();
}

/**
 * @param int $kWunschliste
 * @return string
 * @deprecated since 5.0.0
 */
function wunschlisteLoeschen(int $kWunschliste)
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    return Wunschliste::delete($kWunschliste);
}

/**
 * @param int $kWunschliste
 * @return string
 * @deprecated since 5.0.0
 */
function wunschlisteAktualisieren(int $kWunschliste)
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    return Wunschliste::update($kWunschliste);
}

/**
 * @param int $kWunschliste
 * @return string
 * @deprecated since 5.0.0
 */
function wunschlisteStandard(int $kWunschliste)
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    return Wunschliste::setDefault($kWunschliste);
}

/**
 * @param string $name
 * @return string
 * @deprecated since 5.0.0
 */
function wunschlisteSpeichern($name)
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    return Wunschliste::save($name);
}

/**
 * @param array $cEmail_arr
 * @param int   $kWunschliste
 * @return string
 * @deprecated since 5.0.0
 */
function wunschlisteSenden(array $cEmail_arr, int $kWunschliste)
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    return Wunschliste::send($cEmail_arr, $kWunschliste);
}

/**
 * @param int $kWunschliste
 * @param int $kWunschlistePos
 * @return array|bool
 * @deprecated since 5.0.0
 */
function gibEigenschaftenZuWunschliste(int $kWunschliste, int $kWunschlistePos)
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    return Wunschliste::getAttributesByID($kWunschliste, $kWunschlistePos);
}

/**
 * @param int $kWunschlistePos
 * @return object|bool
 * @deprecated since 5.0.0
 */
function giboWunschlistePos(int $kWunschlistePos)
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    return Wunschliste::getWishListPositionDataByID($kWunschlistePos);
}

/**
 * @param int    $kWunschliste
 * @param string $cURLID
 * @return bool|stdClass
 * @deprecated since 5.0.0
 */
function giboWunschliste(int $kWunschliste = 0, string $cURLID = '')
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    return Wunschliste::getWishListDataByID($kWunschliste, $cURLID);
}

/**
 * @param object $oWunschliste
 * @return mixed
 * @deprecated since 5.0.0
 */
function bauecPreis($oWunschliste)
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    return Wunschliste::buildPrice($oWunschliste);
}

/**
 * @param int $nMSGCode
 * @return string
 * @deprecated since 5.0.0
 */
function mappeWunschlisteMSG(int $nMSGCode)
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    return Wunschliste::mapMessage($nMSGCode);
}
