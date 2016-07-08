<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * @param array $kKupon_arr
 * @return bool
 */
function loescheKupons($kKupon_arr)
{
    if (is_array($kKupon_arr) && count($kKupon_arr) > 0) {
        foreach ($kKupon_arr as $i => $kKupon) {
            $kKupon_arr[$i] = (int)$kKupon;
        }
        $nRows = Shop::DB()->query(
            "DELETE
                FROM tkupon
                WHERE kKupon IN(" . implode(',', $kKupon_arr) . ")", 3
        );
        Shop::DB()->query(
            "DELETE
                FROM tkuponsprache
                WHERE kKupon IN(" . implode(',', $kKupon_arr) . ")", 3
        );

        return ($nRows >= count($kKupon_arr));
    }

    return false;
}

/**
 * @param int $kKupon
 * @return array
 */
function getCouponNames($kKupon)
{
    $namen = array();
    if (!$kKupon) {
        return $namen;
    }
    $kuponnamen = Shop::DB()->query("SELECT * FROM tkuponsprache WHERE kKupon = " . (int)$kKupon, 2);
    $kCount     = count($kuponnamen);
    for ($i = 0; $i < $kCount; $i++) {
        $namen[$kuponnamen[$i]->cISOSprache] = $kuponnamen[$i]->cName;
    }

    return $namen;
}

/**
 * @param string $selKats
 * @param int    $kKategorie
 * @param int    $tiefe
 * @return array
 */
function getCategories($selKats = '', $kKategorie = 0, $tiefe = 0)
{
    $selected = explode(';', $selKats);
    $arr      = array();
    $kats     = Shop::DB()->query("SELECT kKategorie, cName FROM tkategorie WHERE kOberKategorie = " . (int)$kKategorie, 2);
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
 * @param string $selKats
 * @param int    $kKategorie
 * @param int    $tiefe
 * @return array
 */
function getCustomers($selCustomers = '')
{
    $selected    = explode(';', $selCustomers);
    $customers   = Shop::DB()->query("SELECT kKunde FROM tkunde", 2);

    foreach ($customers as $i => $customer) {
        $oKunde = new Kunde($customer->kKunde);
        $customers[$i]->cVorname    = $oKunde->cVorname;
        $customers[$i]->cNachname   = $oKunde->cNachname;
        $customers[$i]->selected    = in_array($customers[$i]->kKunde, $selected) ? 1 : 0;
        unset($oKunde);
    }

    return $customers;
}

/**
 * @param string $string
 * @return string
 */
function convertDate($string)
{
    if ($string === null || $string === '' || $string === '0000-00-00 00:00:00') {
        return '0000-00-00 00:00:00';
    }
    $date = new DateTime($string);

    return $date->format('Y-m-d H:i') . ':00';
}

/**
 * @return array
 */
function getCoupons($cKuponTyp = 'standard')
{
    $oKuponDB_arr = Shop::DB()->query("SELECT kKupon FROM tkupon WHERE cKuponTyp = '" . $cKuponTyp . "'", 2);
    $oKupon_arr   = array();

    foreach ($oKuponDB_arr as $oKuponDB) {
        $oKupon = new Kupon($oKuponDB->kKupon);
        $oKupon->dGueltigAbFormatted = date_create($oKupon->dGueltigAb)->format('d.m.Y');
        $oKupon->dGueltigBisFormatted = date_create($oKupon->dGueltigBis)->format('d.m.Y');

        if ((int)$oKupon->kKundengruppe == -1) {
            $oKupon->cKundengruppe = 'Alle';
        } else {
            $oKundengruppe = Shop::DB()->query("SELECT cName FROM tkundengruppe WHERE kKundengruppe = " . $oKupon->kKundengruppe, 1);
            $oKupon->cKundengruppe = $oKundengruppe->cName;
        }

        if ($oKupon->cArtikel === '') {
            $oKupon->ArtikelInfo = 'Alle';
        } else {
            $oKupon->ArtikelInfo = 'eingeschr&auml;nkt';
        }

        $oKupon_arr[] = $oKupon;
    }

    return $oKupon_arr;
}

/**
 * @param bool   $bNew
 * @param string $cLimitSQL
 * @return array
 */
/*
function getCoupons($bNew = true, $cLimitSQL = '')
{
    $cWhere = " WHERE cAktiv='Y'
				  	AND (dGueltigBis > now() OR dGueltigBis = '0000-00-00 00:00:00')
				  	AND (nVerwendungen = 0 OR nVerwendungenBisher <= nVerwendungen)
					AND dGueltigAb <= now()";

    if (!$bNew) {
        $cWhere = " WHERE cAktiv='N'
						OR (dGueltigBis < now() AND dGueltigBis != '0000-00-00 00:00:00')
						OR (nVerwendungen > 0 AND nVerwendungenBisher > nVerwendungen)
						OR dGueltigAb > now()";
    }

    $oCoupon_arr = Shop::DB()->query(
        "SELECT *, DATE_FORMAT(dGueltigAb, '%d.%m.%Y') AS GueltigAb, DATE_FORMAT(dGueltigBis, '%d.%m.%Y') AS GueltigBis
            FROM tkupon
            {$cWhere}
            ORDER BY dGueltigBis DESC
            {$cLimitSQL}", 2
    );

    if (is_array($oCoupon_arr) && count($oCoupon_arr) > 0) {
        foreach ($oCoupon_arr as $i => $oCoupon) {
            $oCoupon_arr[$i]->Gueltigkeit = "{$oCoupon_arr[$i]->GueltigAb} - {$oCoupon_arr[$i]->GueltigBis}";

            $oKundengruppe = Shop::DB()->query(
                "SELECT cName
                    FROM tkundengruppe
                    WHERE kKundengruppe = {$oCoupon_arr[$i]->kKundengruppe}", 1
            );
            if (isset($oKundengruppe->cName)) {
                $oCoupon_arr[$i]->Kundengruppe = $oKundengruppe->cName;
            } else {
                $oCoupon_arr[$i]->Kundengruppe = 'Alle';
            }
            if ($oCoupon_arr[$i]->cArtikel) {
                $oCoupon_arr[$i]->Artikel = 'eingeschr&auml;nkt';
            } else {
                $oCoupon_arr[$i]->Artikel = 'Alle';
            }
            $oVerwendungen = Shop::DB()->query(
                "SELECT nVerwendungen
                    FROM tkupon
                    WHERE kKupon = " . (int)$oCoupon_arr[$i]->kKupon, 1
            );
            $oVerwendungenBisher = Shop::DB()->query(
                "SELECT nVerwendungenBisher
                    FROM tkupon
                    WHERE kKupon = " . (int)$oCoupon_arr[$i]->kKupon, 1
            );
            $oCoupon_arr[$i]->Verwendungen       = (isset($oVerwendungen->nVerwendungen)) ? $oVerwendungen->nVerwendungen : 0;
            $oCoupon_arr[$i]->VerwendungenBisher = (isset($oVerwendungenBisher->nVerwendungenBisher)) ? $oVerwendungenBisher->nVerwendungenBisher : 0;
        }
    } else {
        $oCoupon_arr = array();
    }

    return $oCoupon_arr;
}
*/

/**
 * @param bool $bNew
 * @return int
 */
function getCouponCount($bNew = true)
{
    if ($bNew) {
        $cWhere = "cAktiv='Y' 
                   AND (dGueltigBis > now() OR dGueltigBis = '0000-00-00 00:00:00')
                   AND (nVerwendungen = 0 OR nVerwendungenBisher <= nVerwendungen)
                   AND dGueltigAb <= now()";
    } else {
        $cWhere = "cAktiv='N'
                   OR (dGueltigBis < now() AND dGueltigBis != '0000-00-00 00:00:00')
                   OR (nVerwendungen > 0 AND nVerwendungenBisher > nVerwendungen)
                   OR dGueltigAb > now()";
    }
    $oCoupon = Shop::DB()->query("SELECT count(*) AS count FROM tkupon WHERE {$cWhere}", 1);

    return (isset($oCoupon->count)) ? (int)$oCoupon->count : 0;
}
