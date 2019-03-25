<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTL\Catalog\Category\Kategorie;
use JTL\Catalog\Hersteller;
use JTL\Catalog\Product\Artikel;
use JTL\Catalog\Product\Preise;
use JTL\Checkout\Kupon;
use JTL\Customer\Kunde;
use JTL\DB\ReturnType;
use JTL\Helpers\Text;
use JTL\Mail\Mail\Mail;
use JTL\Mail\Mailer;
use JTL\Shop;

/**
 * @param array $kKupon_arr
 * @return bool
 */
function loescheKupons($kKupon_arr)
{
    if (!is_array($kKupon_arr) || count($kKupon_arr) === 0) {
        return false;
    }
    $kKupon_arr = array_map('\intval', $kKupon_arr);
    $nRows      = Shop::Container()->getDB()->query(
        'DELETE tkupon, tkuponsprache, tkuponkunde, tkuponbestellung
            FROM tkupon
            LEFT JOIN tkuponsprache
              ON tkuponsprache.kKupon = tkupon.kKupon
            LEFT JOIN tkuponkunde
              ON tkuponkunde.kKupon = tkupon.kKupon
            LEFT JOIN tkuponbestellung
              ON tkuponbestellung.kKupon = tkupon.kKupon
            WHERE tkupon.kKupon IN(' . implode(',', $kKupon_arr) . ')',
        ReturnType::AFFECTED_ROWS
    );

    return $nRows >= count($kKupon_arr);
}

/**
 * @param int $kKupon
 * @return array - key = lang-iso ; value = localized coupon name
 */
function getCouponNames(int $kKupon)
{
    $names = [];
    if (!$kKupon) {
        return $names;
    }
    $coupons = Shop::Container()->getDB()->selectAll('tkuponsprache', 'kKupon', $kKupon);
    foreach ($coupons as $coupon) {
        $names[$coupon->cISOSprache] = $coupon->cName;
    }

    return $names;
}

/**
 * @param string $selHerst
 * @return array
 */
function getManufacturers($selHerst = '')
{
    $selected       = Text::parseSSK($selHerst);
    $hersteller_arr = Shop::Container()->getDB()->query(
        'SELECT kHersteller, cName FROM thersteller',
        ReturnType::ARRAY_OF_OBJECTS
    );

    foreach ($hersteller_arr as $i => $hersteller) {
        $oHersteller                  = new Hersteller($hersteller->kHersteller);
        $hersteller_arr[$i]->cName    = $oHersteller->cName;
        $hersteller_arr[$i]->selected = in_array($hersteller_arr[$i]->kHersteller, $selected) ? 1 : 0;
        unset($oHersteller);
    }

    return $hersteller_arr;
}

/**
 * @param string $selKats
 * @param int    $kKategorie
 * @param int    $tiefe
 * @return array
 */
function getCategories($selKats = '', $kKategorie = 0, $tiefe = 0)
{
    $selected = Text::parseSSK($selKats);
    $arr      = [];
    $kats     = Shop::Container()->getDB()->selectAll(
        'tkategorie',
        'kOberKategorie',
        (int)$kKategorie,
        'kKategorie, cName'
    );
    $kCount   = count($kats);
    for ($o = 0; $o < $kCount; $o++) {
        for ($i = 0; $i < $tiefe; $i++) {
            $kats[$o]->cName = '--' . $kats[$o]->cName;
        }
        $kats[$o]->selected = 0;
        if (in_array($kats[$o]->kKategorie, $selected)) {
            $kats[$o]->selected = 1;
        }
        $arr[] = $kats[$o];
        $arr   = array_merge($arr, getCategories($selKats, $kats[$o]->kKategorie, $tiefe + 1));
    }

    return $arr;
}

/**
 * Parse Datumsstring und formatiere ihn im DB-kompatiblen Standardformat
 *
 * @param string $string
 * @return string
 */
function normalizeDate($string)
{
    if ($string === null || $string === '') {
        return null;
    }

    $date = date_create($string);

    if ($date === false) {
        return $string;
    }

    return $date->format('Y-m-d H:i') . ':00';
}

/**
 * @param string $cKuponTyp
 * @param string $cWhereSQL
 * @param string $cOrderSQL
 * @param string $cLimitSQL
 * @return array|int|object
 */
function getRawCoupons($cKuponTyp = Kupon::TYPE_STANDARD, $cWhereSQL = '', $cOrderSQL = '', $cLimitSQL = '')
{
    return Shop::Container()->getDB()->query(
        "SELECT k.*, max(kk.dErstellt) AS dLastUse
            FROM tkupon AS k
            LEFT JOIN tkuponkunde AS kk ON kk.kKupon = k.kKupon
            WHERE cKuponTyp = '" . Shop::Container()->getDB()->escape($cKuponTyp) . "' " .
            ($cWhereSQL !== '' ? ' AND ' . $cWhereSQL : '') .
            'GROUP BY k.kKupon' .
            ($cOrderSQL !== '' ? ' ORDER BY ' . $cOrderSQL : '') .
            ($cLimitSQL !== '' ? ' LIMIT ' . $cLimitSQL : ''),
        ReturnType::ARRAY_OF_OBJECTS
    );
}

/**
 * Get instances of existing coupons, each with some enhanced information that can be displayed
 *
 * @param string $cKuponTyp
 * @param string $cWhereSQL - an SQL WHERE clause (col1 = val1 AND vol2 LIKE ...)
 * @param string $cOrderSQL - an SQL ORDER BY clause (cName DESC)
 * @param string $cLimitSQL - an SQL LIMIT clause  (10,20)
 * @return array
 */
function getCoupons($cKuponTyp = Kupon::TYPE_STANDARD, $cWhereSQL = '', $cOrderSQL = '', $cLimitSQL = '')
{
    $oKuponDB_arr = getRawCoupons($cKuponTyp, $cWhereSQL, $cOrderSQL, $cLimitSQL);
    $oKupon_arr   = [];
    foreach ($oKuponDB_arr as $oKuponDB) {
        $oKupon_arr[] = getCoupon((int)$oKuponDB->kKupon);
    }

    return $oKupon_arr;
}

/**
 * @param string $cKuponTyp
 * @param string $cWhereSQL
 * @return array
 */
function getExportableCoupons($cKuponTyp = Kupon::TYPE_STANDARD, $cWhereSQL = '')
{
    $coupons = getRawCoupons($cKuponTyp, $cWhereSQL);

    foreach ($coupons as $rawCoupon) {
        foreach (getCouponNames($rawCoupon->kKupon) as $iso => $name) {
            $rawCoupon->{'cName_' . $iso} = $name;
        }
    }

    return $coupons;
}

/**
 * Get an instance of an existing coupon with some enhanced information that can be displayed
 *
 * @param int $kKupon
 * @return Kupon $oKupon
 */
function getCoupon($kKupon)
{
    $oKupon = new Kupon($kKupon);
    augmentCoupon($oKupon);

    return $oKupon;
}

/**
 * Enhance an existing Kupon instance with some extra information that can be displayed
 *
 * @param Kupon $oKupon
 */
function augmentCoupon($oKupon)
{
    $oKupon->cLocalizedValue = $oKupon->cWertTyp === 'festpreis'
        ? Preise::getLocalizedPriceString($oKupon->fWert)
        : '';
    $oKupon->cLocalizedMbw   = isset($oKupon->fMindestbestellwert)
        ? Preise::getLocalizedPriceString($oKupon->fMindestbestellwert)
        : '';
    $oKupon->bOpenEnd        = $oKupon->dGueltigBis === null;

    if (date_create($oKupon->dGueltigAb) === false) {
        $oKupon->cGueltigAbShort = 'ungültig';
        $oKupon->cGueltigAbLong  = 'ungültig';
    } else {
        $oKupon->cGueltigAbShort = date_create($oKupon->dGueltigAb)->format('d.m.Y');
        $oKupon->cGueltigAbLong  = date_create($oKupon->dGueltigAb)->format('d.m.Y H:i');
    }

    if ($oKupon->bOpenEnd) {
        $oKupon->cGueltigBisShort = 'open-end';
        $oKupon->cGueltigBisLong  = 'open-end';
    } elseif (date_create($oKupon->dGueltigBis) === false) {
        $oKupon->cGueltigBisShort = 'ungültig';
        $oKupon->cGueltigBisLong  = 'ungültig';
    } elseif ($oKupon->dGueltigBis === '') {
        $oKupon->cGueltigBisShort = '';
        $oKupon->cGueltigBisLong  = '';
    } else {
        $oKupon->cGueltigBisShort = date_create($oKupon->dGueltigBis)->format('d.m.Y');
        $oKupon->cGueltigBisLong  = date_create($oKupon->dGueltigBis)->format('d.m.Y H:i');
    }

    if ((int)$oKupon->kKundengruppe === -1) {
        $oKupon->cKundengruppe = '';
    } else {
        $oKundengruppe         = Shop::Container()->getDB()->query(
            'SELECT cName 
                FROM tkundengruppe 
                WHERE kKundengruppe = ' . $oKupon->kKundengruppe,
            ReturnType::SINGLE_OBJECT
        );
        $oKupon->cKundengruppe = $oKundengruppe->cName;
    }

    $cArtNr_arr      = Text::parseSSK($oKupon->cArtikel);
    $cHersteller_arr = Text::parseSSK($oKupon->cHersteller);
    $cKategorie_arr  = Text::parseSSK($oKupon->cKategorien);
    $cKunde_arr      = Text::parseSSK($oKupon->cKunden);

    $oKupon->cArtikelInfo    = ($oKupon->cArtikel === '')
        ? ''
        : (string)count($cArtNr_arr);
    $oKupon->cHerstellerInfo = (empty($oKupon->cHersteller) || $oKupon->cHersteller === '-1')
        ? ''
        : (string)count($cHersteller_arr);
    $oKupon->cKategorieInfo  = (empty($oKupon->cKategorien) || $oKupon->cKategorien === '-1')
        ? ''
        : (string)count($cKategorie_arr);
    $oKupon->cKundenInfo     = (empty($oKupon->cKunden) || $oKupon->cKunden === '-1')
        ? ''
        : (string)count($cKunde_arr);

    $oMaxErstelltDB   = Shop::Container()->getDB()->query(
        'SELECT max(dErstellt) as dLastUse
            FROM tkuponkunde
            WHERE kKupon = ' . (int)$oKupon->kKupon,
        ReturnType::SINGLE_OBJECT
    );
    $oKupon->dLastUse = date_create(
        is_string($oMaxErstelltDB->dLastUse)
        ? $oMaxErstelltDB->dLastUse
        : null
    );
}

/**
 * Create a fresh Kupon instance with default values to be edited
 *
 * @param $cKuponTyp - Kupon::TYPE_STANDRAD, Kupon::TYPE_SHIPPING, Kupon::TYPE_NEWCUSTOMER
 * @return Kupon
 */
function createNewCoupon($cKuponTyp)
{
    $oKupon                        = new Kupon();
    $oKupon->cKuponTyp             = $cKuponTyp;
    $oKupon->cName                 = '';
    $oKupon->fWert                 = 0.0;
    $oKupon->cWertTyp              = 'festpreis';
    $oKupon->cZusatzgebuehren      = 'N';
    $oKupon->nGanzenWKRabattieren  = 1;
    $oKupon->kSteuerklasse         = 1;
    $oKupon->fMindestbestellwert   = 0.0;
    $oKupon->cCode                 = '';
    $oKupon->cLieferlaender        = '';
    $oKupon->nVerwendungen         = 0;
    $oKupon->nVerwendungenProKunde = 0;
    $oKupon->cArtikel              = '';
    $oKupon->kKundengruppe         = -1;
    $oKupon->dGueltigAb            = date_create()->format('Y-m-d H:i');
    $oKupon->dGueltigBis           = '';
    $oKupon->cAktiv                = 'Y';
    $oKupon->cHersteller           = '-1';
    $oKupon->cKategorien           = '-1';
    $oKupon->cKunden               = '-1';
    $oKupon->kKupon                = 0;

    augmentCoupon($oKupon);

    return $oKupon;
}

/**
 * Read coupon settings from the edit page form and create a Kupon instance of it
 *
 * @return Kupon
 * @throws Exception
 */
function createCouponFromInput()
{
    $oKupon                        = new Kupon((int)$_POST['kKuponBearbeiten']);
    $oKupon->cKuponTyp             = $_POST['cKuponTyp'];
    $oKupon->cName                 = htmlspecialchars($_POST['cName'], ENT_COMPAT | ENT_HTML401, JTL_CHARSET);
    $oKupon->fWert                 = !empty($_POST['fWert']) ? (float)str_replace(',', '.', $_POST['fWert']) : null;
    $oKupon->cWertTyp              = !empty($_POST['cWertTyp']) ? $_POST['cWertTyp'] : null;
    $oKupon->cZusatzgebuehren      = !empty($_POST['cZusatzgebuehren']) ? $_POST['cZusatzgebuehren'] : 'N';
    $oKupon->nGanzenWKRabattieren  = !empty($_POST['nGanzenWKRabattieren']) ? (int)$_POST['nGanzenWKRabattieren'] : 0;
    $oKupon->kSteuerklasse         = !empty($_POST['kSteuerklasse']) ? (int)$_POST['kSteuerklasse'] : null;
    $oKupon->fMindestbestellwert   = (float)str_replace(',', '.', $_POST['fMindestbestellwert']);
    $oKupon->cCode                 = !empty($_POST['cCode']) ? $_POST['cCode'] : '';
    $oKupon->cLieferlaender        = !empty($_POST['cLieferlaender'])
        ? mb_convert_case($_POST['cLieferlaender'], MB_CASE_UPPER)
        : '';
    $oKupon->nVerwendungen         = !empty($_POST['nVerwendungen']) ? (int)$_POST['nVerwendungen'] : 0;
    $oKupon->nVerwendungenProKunde = !empty($_POST['nVerwendungenProKunde']) ? (int)$_POST['nVerwendungenProKunde'] : 0;
    $oKupon->cArtikel              = !empty($_POST['cArtikel']) ? ';' . trim($_POST['cArtikel'], ";\t\n\r") . ';' : '';
    $oKupon->cHersteller           = '-1';
    $oKupon->kKundengruppe         = (int)$_POST['kKundengruppe'];
    $oKupon->dGueltigAb            = normalizeDate(!empty($_POST['dGueltigAb'])
        ? $_POST['dGueltigAb']
        : date_create()->format('Y-m-d H:i') . ':00');
    $oKupon->dGueltigBis           = normalizeDate(!empty($_POST['dGueltigBis']) ? $_POST['dGueltigBis'] : '');
    $oKupon->cAktiv                = isset($_POST['cAktiv']) && $_POST['cAktiv'] === 'Y' ? 'Y' : 'N';
    $oKupon->cKategorien           = '-1';
    if ($oKupon->cKuponTyp !== Kupon::TYPE_NEWCUSTOMER) {
        $oKupon->cKunden = '-1';
    }
    if (isset($_POST['bOpenEnd']) && $_POST['bOpenEnd'] === 'Y') {
        $oKupon->dGueltigBis = null;
    } elseif (!empty($_POST['dDauerTage'])) {
        $oKupon->dGueltigBis     = '';
        $actualTimestamp         = date_create();
        $actualTimestampEndofDay = date_time_set($actualTimestamp, 23, 59, 59);
        $setDays                 = new DateInterval('P' . $_POST['dDauerTage'] . 'D');
        $oKupon->dGueltigBis     = date_add($actualTimestampEndofDay, $setDays)->format('Y-m-d H:i:s');
    }
    if (!empty($_POST['kHersteller'])
        && is_array($_POST['kHersteller'])
        && count($_POST['kHersteller']) > 0
        && !in_array('-1', $_POST['kHersteller'])
    ) {
        $oKupon->cHersteller = Text::createSSK($_POST['kHersteller']);
    }
    if (!empty($_POST['kKategorien'])
        && is_array($_POST['kKategorien']) && count($_POST['kKategorien']) > 0
        && !in_array('-1', $_POST['kKategorien'])
    ) {
        $oKupon->cKategorien = Text::createSSK($_POST['kKategorien']);
    }
    if (!empty($_POST['cKunden']) && $_POST['cKunden'] != '-1') {
        $oKupon->cKunden = trim($_POST['cKunden'], ";\t\n\r") . ';';
    }
    if (isset($_POST['couponCreation'])) {
        $massCreationCoupon                  = new stdClass();
        $massCreationCoupon->cActiv          = !empty($_POST['couponCreation'])
            ? (int)$_POST['couponCreation']
            : 0;
        $massCreationCoupon->numberOfCoupons = ($massCreationCoupon->cActiv === 1 && !empty($_POST['numberOfCoupons']))
            ? (int)$_POST['numberOfCoupons']
            : 2;
        $massCreationCoupon->lowerCase       = ($massCreationCoupon->cActiv === 1 && !empty($_POST['lowerCase']));
        $massCreationCoupon->upperCase       = ($massCreationCoupon->cActiv === 1 && !empty($_POST['upperCase']));
        $massCreationCoupon->numbersHash     = ($massCreationCoupon->cActiv === 1 && !empty($_POST['numbersHash']));
        $massCreationCoupon->hashLength      = ($massCreationCoupon->cActiv === 1 && !empty($_POST['hashLength']))
            ? $_POST['hashLength']
            : 4;
        $massCreationCoupon->prefixHash      = ($massCreationCoupon->cActiv === 1 && !empty($_POST['prefixHash']))
            ? $_POST['prefixHash']
            : '';
        $massCreationCoupon->suffixHash      = ($massCreationCoupon->cActiv === 1 && !empty($_POST['suffixHash']))
            ? $_POST['suffixHash']
            : '';
        $oKupon->massCreationCoupon          = $massCreationCoupon;
    }

    return $oKupon;
}

/**
 * Get the number of existing coupons of type $cKuponTyp
 *
 * @param string $cKuponTyp
 * @param string $cWhereSQL
 * @return int
 */
function getCouponCount($cKuponTyp = Kupon::TYPE_STANDARD, $cWhereSQL = '')
{
    $oKuponDB = Shop::Container()->getDB()->query(
        "SELECT COUNT(kKupon) AS count
            FROM tkupon
            WHERE cKuponTyp = '" . $cKuponTyp . "'" .
            ($cWhereSQL !== '' ? ' AND ' . $cWhereSQL : ''),
        ReturnType::SINGLE_OBJECT
    );

    return (int)$oKuponDB->count;
}

/**
 * Validates the fields of a given Kupon instance
 *
 * @param Kupon $oKupon
 * @return array - list of error messages
 */
function validateCoupon($oKupon)
{
    $cFehler_arr = [];

    if ($oKupon->cName === '') {
        $cFehler_arr[] = __('errorCouponNameMissing');
    }
    if (($oKupon->cKuponTyp === Kupon::TYPE_STANDARD || $oKupon->cKuponTyp === Kupon::TYPE_NEWCUSTOMER)
        && $oKupon->fWert < 0
    ) {
        $cFehler_arr[] = __('errorCouponValueNegative');
    }
    if ($oKupon->fMindestbestellwert < 0) {
        $cFehler_arr[] = __('errorCouponMinOrderValueNegative');
    }
    if ($oKupon->cKuponTyp === Kupon::TYPE_SHIPPING && $oKupon->cLieferlaender === '') {
        $cFehler_arr[] = __('errorCouponISOMissing');
    }
    if (isset($oKupon->massCreationCoupon)) {
        $cCodeLength = (int)$oKupon->massCreationCoupon->hashLength
            + (int)mb_strlen($oKupon->massCreationCoupon->prefixHash)
            + (int)mb_strlen($oKupon->massCreationCoupon->suffixHash);
        if ($cCodeLength > 32) {
            $cFehler_arr[] = __('errorCouponCodeLong');
        }
        if ($cCodeLength < 2) {
            $cFehler_arr[] = __('errorCouponCodeShort');
        }
        if (!$oKupon->massCreationCoupon->lowerCase
            && !$oKupon->massCreationCoupon->upperCase
            && !$oKupon->massCreationCoupon->numbersHash
        ) {
            $cFehler_arr[] = __('errorCouponCodeOptionSelect');
        }
    } elseif (mb_strlen($oKupon->cCode) > 32) {
        $cFehler_arr[] = __('errorCouponCodeLong');
    }
    if ($oKupon->cCode !== ''
        && !isset($oKupon->massCreationCoupon)
        && ($oKupon->cKuponTyp === Kupon::TYPE_STANDARD || $oKupon->cKuponTyp === Kupon::TYPE_SHIPPING)
    ) {
        $queryRes = Shop::Container()->getDB()->executeQueryPrepared(
            'SELECT kKupon
                FROM tkupon
                WHERE cCode = :cCode
                    AND kKupon != :kKupon',
            [ 'cCode' => $oKupon->cCode, 'kKupon' => (int)$oKupon->kKupon ],
            ReturnType::SINGLE_OBJECT
        );
        if (is_object($queryRes)) {
            $cFehler_arr[] = __('errorCouponCodeDuplicate');
        }
    }

    $cArtNr_arr  = Text::parseSSK($oKupon->cArtikel);
    $validArtNrs = [];

    foreach ($cArtNr_arr as $cArtNr) {
        $res = Shop::Container()->getDB()->select('tartikel', 'cArtNr', $cArtNr);
        if ($res === null) {
            $cFehler_arr[] = sprintf(__('errorProductNumberNotFound'), $cArtNr);
        } else {
            $validArtNrs[] = $cArtNr;
        }
    }

    $oKupon->cArtikel = Text::createSSK($validArtNrs);

    if ($oKupon->cKuponTyp === Kupon::TYPE_SHIPPING) {
        $cLandISO_arr  = Text::parseSSK($oKupon->cLieferlaender);
        $countryHelper = Shop::Container()->getCountryService();
        foreach ($cLandISO_arr as $cLandISO) {
            if ($countryHelper->getCountry($cLandISO) === null) {
                $cFehler_arr[] = sprintf(__('errorISOInvalid'), $cLandISO);
            }
        }
    }

    $dGueltigAb  = date_create($oKupon->dGueltigAb);
    $dGueltigBis = date_create($oKupon->dGueltigBis);

    if ($dGueltigAb === false) {
        $cFehler_arr[] = __('errorPeriodBeginFormat');
    }
    if ($dGueltigBis === false) {
        $cFehler_arr[] = __('errorPeriodEndFormat');
    }

    $bOpenEnd = $oKupon->dGueltigBis === null;

    if ($dGueltigAb !== false && $dGueltigBis !== false && $dGueltigAb > $dGueltigBis && $bOpenEnd === false) {
        $cFehler_arr[] = __('errorPeriodEndAfterBegin');
    }

    return $cFehler_arr;
}

/**
 * Save a new or already existing coupon in the DB
 *
 * @param Kupon $oKupon
 * @param array $oSprache_arr
 * @return int - 0 on failure ; kKupon on success
 */
function saveCoupon($oKupon, $oSprache_arr)
{
    if ((int)$oKupon->kKupon > 0) {
        // vorhandener Kupon
        $res = $oKupon->update() === -1 ? 0 : $oKupon->kKupon;
    } else {
        // neuer Kupon
        $oKupon->nVerwendungenBisher = 0;
        $oKupon->dErstellt           = 'NOW()';
        if (isset($oKupon->massCreationCoupon)) {
            $massCreationCoupon = $oKupon->massCreationCoupon;
            $oKupon->kKupon     = [];
            unset($oKupon->massCreationCoupon, $_POST['informieren']);
            for ($i = 1; $i <= $massCreationCoupon->numberOfCoupons; $i++) {
                if ($oKupon->cKuponTyp !== Kupon::TYPE_NEWCUSTOMER) {
                    $oKupon->cCode = $oKupon->generateCode(
                        $massCreationCoupon->hashLength,
                        $massCreationCoupon->lowerCase,
                        $massCreationCoupon->upperCase,
                        $massCreationCoupon->numbersHash,
                        $massCreationCoupon->prefixHash,
                        $massCreationCoupon->suffixHash
                    );
                }
                unset($oKupon->translationList);
                $oKupon->kKupon[] = (int)$oKupon->save();
            }
        } else {
            if ($oKupon->cKuponTyp !== Kupon::TYPE_NEWCUSTOMER && $oKupon->cCode === '') {
                $oKupon->cCode = $oKupon->generateCode();
            }
            unset($oKupon->translationList);
            $oKupon->kKupon = (int)$oKupon->save();
        }
        $res = $oKupon->kKupon;
    }

    if ($res > 0) {
        // Kupon-Sprachen aktualisieren
        if (is_array($oKupon->kKupon)) {
            foreach ($oKupon->kKupon as $kKupon) {
                Shop::Container()->getDB()->delete('tkuponsprache', 'kKupon', $kKupon);

                foreach ($oSprache_arr as $oSprache) {
                    $postVarName       = 'cName_' . $oSprache->cISO;
                    $cKuponSpracheName = isset($_POST[$postVarName]) && $_POST[$postVarName] !== ''
                        ? htmlspecialchars($_POST[$postVarName], ENT_COMPAT | ENT_HTML401, JTL_CHARSET)
                        : $oKupon->cName;

                    $kuponSprache              = new stdClass();
                    $kuponSprache->kKupon      = $kKupon;
                    $kuponSprache->cISOSprache = $oSprache->cISO;
                    $kuponSprache->cName       = $cKuponSpracheName;
                    Shop::Container()->getDB()->insert('tkuponsprache', $kuponSprache);
                }
            }
        } else {
            Shop::Container()->getDB()->delete('tkuponsprache', 'kKupon', $oKupon->kKupon);

            foreach ($oSprache_arr as $oSprache) {
                $postVarName       = 'cName_' . $oSprache->cISO;
                $cKuponSpracheName = isset($_POST[$postVarName]) && $_POST[$postVarName] !== ''
                    ? htmlspecialchars($_POST[$postVarName], ENT_COMPAT | ENT_HTML401, JTL_CHARSET)
                    : $oKupon->cName;

                $kuponSprache              = new stdClass();
                $kuponSprache->kKupon      = $oKupon->kKupon;
                $kuponSprache->cISOSprache = $oSprache->cISO;
                $kuponSprache->cName       = $cKuponSpracheName;
                Shop::Container()->getDB()->insert('tkuponsprache', $kuponSprache);
            }
        }
    }

    return $res;
}

/**
 * Send notification emails to all customers admitted to this Kupon
 *
 * @param Kupon $coupon
 */
function informCouponCustomers($coupon)
{
    augmentCoupon($coupon);
    $db              = Shop::Container()->getDB();
    $defaultLang     = $db->select('tsprache', 'cShopStandard', 'Y');
    $defaultCurrency = $db->select('twaehrung', 'cStandard', 'Y');
    $defaultOptions  = Artikel::getDefaultOptions();
    // lokalisierter Kuponwert und MBW
    $coupon->cLocalizedWert = $coupon->cWertTyp === 'festpreis'
        ? Preise::getLocalizedPriceString($coupon->fWert, $defaultCurrency, false)
        : $coupon->fWert . ' %';
    $coupon->cLocalizedMBW  = Preise::getLocalizedPriceString($coupon->fMindestbestellwert, $defaultCurrency, false);
    // kKunde-Array aller auserwaehlten Kunden
    $customerIDs  = Text::parseSSK($coupon->cKunden);
    $customerData = $db->query(
        'SELECT kKunde
            FROM tkunde
            WHERE TRUE
                ' . ((int)$coupon->kKundengruppe === -1
            ? 'AND TRUE'
            : 'AND kKundengruppe = ' . (int)$coupon->kKundengruppe) . '
                ' . ($coupon->cKunden === '-1'
            ? 'AND TRUE'
            : 'AND kKunde IN (' . implode(',', $customerIDs) . ')'),
        ReturnType::ARRAY_OF_OBJECTS
    );
    $productIDs   = [];
    $itemNumbers  = Text::parseSSK($coupon->cArtikel);
    if (count($itemNumbers) > 0) {
        $itemNumbers = array_map(function ($e) {
            return '"' . $e . '"';
        }, $itemNumbers);
        $productData = $db->query(
            'SELECT kArtikel
                FROM tartikel
                WHERE cArtNr IN (' . implode(',', $itemNumbers) . ')',
            ReturnType::ARRAY_OF_OBJECTS
        );
        $productIDs  = array_map(function ($e) {
            return (int)$e->kArtikel;
        }, $productData);
    }
    foreach ($customerData as $oKundeDB) {
        $customer = new Kunde($oKundeDB->kKunde);
        $language = Shop::Lang()->getIsoFromLangID($customer->kSprache);
        if (!$language) {
            $language = $defaultLang;
        }
        $localized  = $db->select(
            'tkuponsprache',
            ['kKupon', 'cISOSprache'],
            [$coupon->kKupon, $language->cISO]
        );
        $categories = [];
        if ($coupon->cKategorien !== '-1') {
            foreach (array_map('\intval', Text::parseSSK($coupon->cKategorien)) as $categoryID) {
                if ($categoryID > 0) {
                    $category       = new Kategorie($categoryID, $customer->kSprache, $customer->kKundengruppe);
                    $category->cURL = $category->cURLFull;
                    $categories[]   = $category;
                }
            }
        }
        $products = [];
        foreach ($productIDs as $productID) {
            $product = new Artikel();
            $product->fuelleArtikel(
                $productID,
                $defaultOptions,
                $customer->kKundengruppe,
                $customer->kSprache,
                true
            );
            $products[] = $product;
        }
        // put all together
        $coupon->Kategorien      = $categories;
        $coupon->Artikel         = $products;
        $coupon->AngezeigterName = $localized->cName;
        $obj                     = new stdClass();
        $obj->tkupon             = $coupon;
        $obj->tkunde             = $customer;
        $mailer                  = Shop::Container()->get(Mailer::class);
        $mail                    = new Mail();
        $mailer->send($mail->createFromTemplateID(MAILTEMPLATE_KUPON, $obj));
    }
}

/**
 * Set all Coupons with an outdated dGueltigBis to cAktiv = 'N'
 */
function deactivateOutdatedCoupons()
{
    Shop::Container()->getDB()->query(
        "UPDATE tkupon
            SET cAktiv = 'N'
            WHERE dGueltigBis > 0
            AND dGueltigBis <= NOW()",
        ReturnType::QUERYSINGLE
    );
}

/**
 * Set all Coupons that reached nVerwendungenBisher to nVerwendungen to cAktiv = 'N'
 */
function deactivateExhaustedCoupons()
{
    Shop::Container()->getDB()->query(
        "UPDATE tkupon
            SET cAktiv = 'N'
            WHERE nVerwendungen > 0
            AND nVerwendungenBisher >= nVerwendungen",
        ReturnType::QUERYSINGLE
    );
}
