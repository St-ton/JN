<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * @return array|bool
 */
function holeExportformatCron()
{
    $oExportformatCron_arr = Shop::DB()->query(
        "SELECT texportformat.*, tcron.kCron, tcron.nAlleXStd, tcron.dStart, DATE_FORMAT(tcron.dStart, '%d.%m.%Y %H:%i') AS dStart_de,
            tcron.dLetzterStart, DATE_FORMAT(tcron.dLetzterStart, '%d.%m.%Y %H:%i') AS dLetzterStart_de,
            DATE_FORMAT(DATE_ADD(tcron.dLetzterStart, INTERVAL tcron.nAlleXStd HOUR), '%d.%m.%Y %H:%i') AS dNaechsterStart_de
            FROM texportformat
            JOIN tcron ON tcron.cJobArt = 'exportformat'
                AND tcron.kKey = texportformat.kExportformat
            ORDER BY tcron.dStart DESC", 2
    );

    if (is_array($oExportformatCron_arr) && count($oExportformatCron_arr) > 0) {
        foreach ($oExportformatCron_arr as $i => $oExportformatCron) {
            $oExportformatCron_arr[$i]->cAlleXStdToDays = rechneUmAlleXStunden($oExportformatCron->nAlleXStd);

            $oExportformatCron_arr[$i]->Sprache = Shop::DB()->query(
                "SELECT * 
                    FROM tsprache 
                    WHERE kSprache = " . (int)$oExportformatCron->kSprache, 1
            );
            $oExportformatCron_arr[$i]->Waehrung = Shop::DB()->query(
                "SELECT * 
                    FROM twaehrung 
                    WHERE kWaehrung = " . (int)$oExportformatCron->kWaehrung, 1
            );
            $oExportformatCron_arr[$i]->Kundengruppe = Shop::DB()->query(
                "SELECT * 
                    FROM tkundengruppe 
                    WHERE kKundengruppe = " . (int)$oExportformatCron->kKundengruppe, 1
            );
            $oExportformatCron_arr[$i]->oJobQueue = Shop::DB()->query(
                "SELECT *, DATE_FORMAT(dZuletztGelaufen, '%d.%m.%Y %H:%i') AS dZuletztGelaufen_de 
                    FROM tjobqueue 
                    WHERE kCron = " . (int)$oExportformatCron->kCron, 1
            );
            $oExportformatCron_arr[$i]->nAnzahlArtikel       = holeMaxExportArtikelAnzahl($oExportformatCron);
            $oExportformatCron_arr[$i]->nAnzahlArtikelYatego = Shop::DB()->query(
                "SELECT count(*) AS nAnzahl 
                    FROM tartikel 
                    JOIN tartikelattribut 
                        ON tartikelattribut.kArtikel = tartikel.kArtikel 
                    WHERE tartikelattribut.cName = 'yategokat'", 1
            );
        }

        return $oExportformatCron_arr;
    }

    return false;
}

/**
 * @param int $kCron
 * @return int
 */
function holeCron($kCron)
{
    $kCron = (int)$kCron;
    if ($kCron > 0) {
        $oCron = Shop::DB()->query(
            "SELECT *, DATE_FORMAT(tcron.dStart, '%d.%m.%Y %H:%i') AS dStart_de
                FROM tcron
                WHERE kCron = " . $kCron, 1
        );

        if (!empty($oCron->kCron) && $oCron->kCron > 0) {
            return $oCron;
        }
    }

    return 0;
}

/**
 * @param int $nAlleXStd
 * @return bool|string
 */
function rechneUmAlleXStunden($nAlleXStd)
{
    if ($nAlleXStd > 0) {
        // nAlleXStd umrechnen
        if ($nAlleXStd > 24) {
            $nAlleXStd = round($nAlleXStd / 24);
            if ($nAlleXStd >= 365) {
                $nAlleXStd /= 365;
                if ($nAlleXStd == 1) {
                    $nAlleXStd .= ' Jahr';
                } else {
                    $nAlleXStd .= ' Jahre';
                }
            } else {
                if ($nAlleXStd == 1) {
                    $nAlleXStd .= ' Tag';
                } else {
                    $nAlleXStd .= ' Tage';
                }
            }
        } else {
            if ($nAlleXStd > 1) {
                $nAlleXStd .= ' Stunden';
            } else {
                $nAlleXStd .= ' Stunde';
            }
        }

        return $nAlleXStd;
    }

    return false;
}

/**
 * @return bool
 */
function holeAlleExportformate()
{
    $oExportformat_arr = Shop::DB()->query(
        "SELECT *
            FROM texportformat
            ORDER BY cName, kSprache, kKundengruppe, kWaehrung", 2
    );

    if (is_array($oExportformat_arr) && count($oExportformat_arr) > 0) {
        foreach ($oExportformat_arr as $i => $oExportformat) {
            $oExportformat_arr[$i]->Sprache      = Shop::DB()->query("SELECT * FROM tsprache WHERE kSprache = " . (int)$oExportformat->kSprache, 1);
            $oExportformat_arr[$i]->Waehrung     = Shop::DB()->query("SELECT * FROM twaehrung WHERE kWaehrung = " . (int)$oExportformat->kWaehrung, 1);
            $oExportformat_arr[$i]->Kundengruppe = Shop::DB()->query("SELECT * FROM tkundengruppe WHERE kKundengruppe = " . (int)$oExportformat->kKundengruppe, 1);
        }

        return $oExportformat_arr;
    }

    return false;
}

/**
 * @param int    $kExportformat
 * @param string $dStart
 * @param int    $nAlleXStunden
 * @param int    $kCron
 * @return int
 */
function erstelleExportformatCron($kExportformat, $dStart, $nAlleXStunden, $kCron = 0)
{
    $kExportformat = (int)$kExportformat;
    $nAlleXStunden = (int)$nAlleXStunden;
    $kCron         = (int)$kCron;
    if ($kExportformat > 0 && $nAlleXStunden > 5 && dStartPruefen($dStart)) {
        if ($kCron > 0) {
            // Editieren
            Shop::DB()->query(
                "DELETE tcron, tjobqueue
                    FROM tcron
                    LEFT JOIN tjobqueue ON tjobqueue.kCron = tcron.kCron
                    WHERE tcron.kCron = " . $kCron, 4
            );
            $oCron = new Cron(
                $kCron,
                $kExportformat,
                $nAlleXStunden,
                $dStart . '_' . $kExportformat,
                'exportformat',
                'texportformat',
                'kExportformat',
                baueENGDate($dStart),
                baueENGDate($dStart, 1)
            );
            $oCron->speicherInDB();

            return 1;
        }
        // Pruefe ob Exportformat nicht bereits vorhanden
        $oCron = Shop::DB()->query(
            "SELECT kCron
                FROM tcron
                WHERE cKey = 'kExportformat'
                    AND kKey = " . $kExportformat, 1
        );

        if (isset($oCron->kCron) && $oCron->kCron > 0) {
            return -1;
        }
        $oCron = new Cron(
            0,
            $kExportformat,
            $nAlleXStunden,
            $dStart . '_' . $kExportformat,
            'exportformat',
            'texportformat',
            'kExportformat',
            baueENGDate($dStart),
            baueENGDate($dStart, 1)
        );
        $oCron->speicherInDB();

        return 1;
    }

    return 0;
}

/**
 * @param string $dStart
 * @return bool
 */
function dStartPruefen($dStart)
{
    if (preg_match('/^([0-3]{1}[0-9]{1}[.]{1}[0-1]{1}[0-9]{1}[.]{1}[0-9]{4}[ ]{1}[0-2]{1}[0-9]{1}[:]{1}[0-6]{1}[0-9]{1})/', $dStart)) {
        return true;
    }

    return false;
}

/**
 * @param string $dStart
 * @param int    $bZeit
 * @return string
 */
function baueENGDate($dStart, $bZeit = 0)
{
    list($dDatum, $dZeit)        = explode(' ', $dStart);
    list($nTag, $nMonat, $nJahr) = explode('.', $dDatum);

    if ($bZeit) {
        return $dZeit;
    }

    return $nJahr . '-' . $nMonat . '-' . $nTag . ' ' . $dZeit;
}

/**
 * @param array $kCron_arr
 * @return bool
 */
function loescheExportformatCron($kCron_arr)
{
    if (is_array($kCron_arr) && count($kCron_arr) > 0) {
        foreach ($kCron_arr as $kCron) {
            $kCron = (int)$kCron;
            Shop::DB()->delete('tjobqueue', 'kCron', $kCron);
            Shop::DB()->delete('tcron', 'kCron', $kCron);
        }

        return true;
    }

    return false;
}

/**
 * @param int $nStunden
 * @return array|bool
 */
function holeExportformatQueueBearbeitet($nStunden)
{
    if (!$nStunden) {
        $nStunden = 24;
    } else {
        $nStunden = (int)$nStunden;
    }
    $kSprache = (isset($_SESSION['kSprache'])) ? (int)$_SESSION['kSprache'] : null;
    if (!$kSprache) {
        $oSpracheTMP = Shop::DB()->query(
            "SELECT kSprache
                FROM tsprache
                WHERE cShopStandard = 'Y'", 1
        );

        if (isset($oSpracheTMP->kSprache) && $oSpracheTMP->kSprache > 0) {
            $kSprache = (int)$oSpracheTMP->kSprache;
        } else {
            return false;
        }
    }

    $oExportformatQueueBearbeitet = Shop::DB()->query(
        "SELECT texportformat.cName, texportformat.cDateiname, texportformatqueuebearbeitet.*, DATE_FORMAT(texportformatqueuebearbeitet.dZuletztGelaufen,
            '%d.%m.%Y %H:%i') AS dZuletztGelaufen_DE, tsprache.cNameDeutsch AS cNameSprache, tkundengruppe.cName AS cNameKundengruppe, twaehrung.cName AS cNameWaehrung
            FROM texportformatqueuebearbeitet
            JOIN texportformat ON texportformat.kExportformat = texportformatqueuebearbeitet.kExportformat
                AND texportformat.kSprache = " . $kSprache . "
            JOIN tsprache ON tsprache.kSprache = texportformat.kSprache
            JOIN tkundengruppe ON tkundengruppe.kKundengruppe = texportformat.kKundengruppe
            JOIN twaehrung ON twaehrung.kWaehrung = texportformat.kWaehrung
            WHERE DATE_SUB(now(), INTERVAL " . $nStunden . " HOUR) < texportformatqueuebearbeitet.dZuletztGelaufen
            ORDER BY texportformatqueuebearbeitet.dZuletztGelaufen DESC", 2
    );

    return $oExportformatQueueBearbeitet;
}
