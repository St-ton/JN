<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once dirname(__FILE__) . '/includes/admininclude.php';

$oAccount->permission('STATS_COUPON_VIEW', true, true);

$cHinweis      = '';
$cFehler       = '';
$step          = 'kuponstatistik_uebersicht';
$cWhere        = '';
$cFromDate_arr = array();
$cToDate_arr   = array();
$Kupons_arr    = Shop::DB()->query("SELECT kKupon, cName FROM tkupon ORDER BY cName DESC", 9);

if (isset($_POST['formFilter']) && $_POST['formFilter'] > 0 && validateToken()) {
    if (intval($_POST['kKupon']) > -1) {
        $cWhere = "(SELECT kKupon FROM tkuponbestellung WHERE tkuponbestellung.kBestellung = tbestellung.kBestellung LIMIT 0,1) = " . (int)$_POST['kKupon'] . " AND";
        foreach ($Kupons_arr as $key => $value) {
            if ($value['kKupon'] == (int)$_POST['kKupon']) {
                $Kupons_arr[$key]['aktiv'] = 1;
                break;
            }
        }
    }

    $cFromDate_arr['nTag']   = (int)$_POST['cFromDay'];
    $cFromDate_arr['nMonat'] = (int)$_POST['cFromMonth'];
    $cFromDate_arr['nJahr']  = (int)$_POST['cFromYear'];

    if (!checkdate($cFromDate_arr['nMonat'], $cFromDate_arr['nTag'], $cFromDate_arr['nJahr'])) {
        $cFromDate_arr['nJahr']  = 2011;
        $cFromDate_arr['nMonat'] = 1;
        $cFromDate_arr['nTag']   = 1;
    }

    $cToDate_arr['nTag']   = (int)$_POST['cToDay'];
    $cToDate_arr['nMonat'] = (int)$_POST['cToMonth'];
    $cToDate_arr['nJahr']  = (int)$_POST['cToYear'];

    if (!checkdate($cToDate_arr['nMonat'], $cToDate_arr['nTag'], $cToDate_arr['nJahr']) || mktime(0, 0, 0, $cToDate_arr['nMonat'], $cToDate_arr['nTag'], $cToDate_arr['nJahr']) > time()) {
        $cToDate_arr['nJahr']  = date('Y');
        $cToDate_arr['nMonat'] = date('m');
        $cToDate_arr['nTag']   = date('d');
    }

    if (mktime(0, 0, 0, $cFromDate_arr['nMonat'], $cFromDate_arr['nTag'], $cFromDate_arr['nJahr']) > mktime(0, 0, 0, $cToDate_arr['nMonat'], $cToDate_arr['nTag'], $cToDate_arr['nJahr'])) {
        $cFromDate_arr['nJahr']  = 2011;
        $cFromDate_arr['nMonat'] = 1;
        $cFromDate_arr['nTag']   = 1;

        $cToDate_arr['nJahr']  = date('Y');
        $cToDate_arr['nMonat'] = date('m');
        $cToDate_arr['nTag']   = date('d');
    }
} else {
    $cFromDate_arr['nJahr']  = date('Y');
    $cFromDate_arr['nMonat'] = date('m');
    $cFromDate_arr['nTag']   = 1;

    $cToDate_arr['nJahr']  = date('Y');
    $cToDate_arr['nMonat'] = date('m');
    $cToDate_arr['nTag']   = date('d');
}

$dStart = $cFromDate_arr['nJahr'] . '-' . $cFromDate_arr['nMonat'] . '-' . $cFromDate_arr['nTag'];
$dEnd   = $cToDate_arr['nJahr'] . '-' . $cToDate_arr['nMonat'] . '-' . $cToDate_arr['nTag'] . ' 23:59:59';

$usedKupons = Shop::DB()->query(
    "SELECT
        twarenkorbpos.cName,
        tbestellung.dErstellt,
        tbestellung.kKunde,
        tbestellung.cBestellNr,
        twarenkorbpos.kWarenkorb,
        (SELECT kKupon
            FROM tkuponbestellung
            WHERE tkuponbestellung.kBestellung = tbestellung.kBestellung
                AND kKupon IN (SELECT kKupon FROM tkupon WHERE kKupon = tkuponbestellung.kKupon)) AS kKupon,
        twarenkorbpos.fPreis+(twarenkorbpos.fPreis/100*twarenkorbpos.fMwSt) AS nWertKupon,
        (SELECT SUM((twarenkorbpos.fPreis+(twarenkorbpos.fPreis/100*twarenkorbpos.fMwSt))*twarenkorbpos.nAnzahl)
            FROM twarenkorbpos
            WHERE twarenkorbpos.kWarenkorb = tbestellung.kWarenkorb) AS nSummeWarenkorb
        FROM twarenkorbpos
            LEFT JOIN tbestellung ON twarenkorbpos.kWarenkorb = tbestellung.kWarenkorb
        WHERE " . $cWhere . " twarenkorbpos.nPosTyp = 3
            AND tbestellung.dErstellt BETWEEN '" . $dStart . "'
            AND '" . $dEnd . "'
            AND tbestellung.cStatus != " . BESTELLUNG_STATUS_STORNO . "
        ORDER BY tbestellung.dErstellt DESC", 9
);

$usedBeschraenkteProzenteKupons = Shop::DB()->query(
    "SELECT DISTINCT
        twarenkorbpos.cHinweis AS cName,
        tbestellung.dErstellt,
        tbestellung.kKunde,
        tbestellung.cBestellNr,
        twarenkorbpos.kWarenkorb,
        (SELECT kKupon
            FROM tkuponbestellung
            WHERE tkuponbestellung.kBestellung = tbestellung.kBestellung
                AND kKupon IN
                    (SELECT kKupon
                        FROM tkupon
                        WHERE kKupon = tkuponbestellung.kKupon
                          AND cWertTyp = 'prozent')) AS kKupon,
        (SELECT fWert
            FROM tkupon
            WHERE kKupon =
                (SELECT kKupon
                FROM tkuponbestellung
                WHERE tkuponbestellung.kBestellung = tbestellung.kBestellung
                    AND kKupon IN
                        (SELECT kKupon
                        FROM tkupon
                        WHERE kKupon = tkuponbestellung.kKupon
                            AND cWertTyp = 'prozent')))AS fWert,
        (SELECT SUM(((twarenkorbpos.fPreis+(twarenkorbpos.fPreis/100*twarenkorbpos.fMwSt))/(100-fWert)*fWert)*(- twarenkorbpos.nAnzahl))
            FROM twarenkorbpos
            WHERE twarenkorbpos.kWarenkorb = tbestellung.kWarenkorb
                AND IF(LOCATE(';'+twarenkorbpos.cArtNr+';',
                    (SELECT cArtikel
                        FROM tkupon
                        WHERE kKupon =
                            (SELECT kKupon
                                FROM tkuponbestellung
                                WHERE tkuponbestellung.kBestellung = tbestellung.kBestellung)))>0,1,0)=1
                AND twarenkorbpos.kArtikel != 0) AS nWertKupon,
        (SELECT SUM((twarenkorbpos.fPreis+(twarenkorbpos.fPreis/100*twarenkorbpos.fMwSt))*twarenkorbpos.nAnzahl)
            FROM twarenkorbpos
            WHERE twarenkorbpos.kWarenkorb = tbestellung.kWarenkorb) AS nSummeWarenkorb
        FROM twarenkorbpos
        LEFT JOIN tbestellung ON twarenkorbpos.kWarenkorb = tbestellung.kWarenkorb
        WHERE " . $cWhere . " IF(LOCATE('Rabatt)',twarenkorbpos.cHinweis)>0,1,0)
            AND tbestellung.dErstellt BETWEEN '" . $dStart . "'
            AND '" . $dEnd . "'
            AND tbestellung.cStatus != " . BESTELLUNG_STATUS_STORNO . "
        ORDER BY tbestellung.dErstellt DESC", 9
);

$nCountBestellungen_arr = Shop::DB()->query(
    "SELECT count(*) AS nCount
        FROM tbestellung
        WHERE dErstellt BETWEEN '" . $dStart . "'
            AND '" . $dEnd . "'
            AND tbestellung.cStatus != " . BESTELLUNG_STATUS_STORNO, 8
);

$nCountUsedKupons    = 0;
$nCountUser          = 0;
$nSummeWarenkorbAlle = 0;
$nSummeKuponAlle     = 0;
$tmpUser             = array();
$datum               = array();
if (isset($usedKupons) && is_array($usedKupons)) {
    if (isset($usedBeschraenkteProzenteKupons) && is_array($usedBeschraenkteProzenteKupons)) {
        $usedKupons = array_merge($usedKupons, $usedBeschraenkteProzenteKupons);
    }
    foreach ($usedKupons as $key => $usedKupon) {
        $oKunde                        = new Kunde($usedKupon['kKunde']);
        $usedKupons[$key]['cUserName'] = $oKunde->cVorname . ' ' . $oKunde->cNachname;
        unset($oKunde);
        $usedKupons[$key]['nWertKupon']      = gibPreisLocalizedOhneFaktor(substr($usedKupon['nWertKupon'], 1));
        $usedKupons[$key]['nSummeWarenkorb'] = gibPreisLocalizedOhneFaktor($usedKupon['nSummeWarenkorb'] + (float)substr($usedKupon['nWertKupon'], 1));
        $usedKupons[$key]['nSummeGesamt']    = gibPreisLocalizedOhneFaktor($usedKupon['nSummeWarenkorb']);
        $usedKupons[$key]['cBestellPos_arr'] = Shop::DB()->query("
            SELECT CONCAT_WS(' ',cName,cHinweis) AS cName, fPreis+(fPreis/100*fMwSt) AS nPreisNetto, nAnzahl
                FROM twarenkorbpos
                WHERE kWarenkorb = " . (int)$usedKupon['kWarenkorb'], 9
        );
        foreach ($usedKupons[$key]['cBestellPos_arr'] as $posKey => $value) {
            $usedKupons[$key]['cBestellPos_arr'][$posKey]['nPreisNetto']       = gibPreisLocalizedOhneFaktor($value['nPreisNetto']);
            $usedKupons[$key]['cBestellPos_arr'][$posKey]['nAnzahl']           = str_replace('.', ',', number_format($value['nAnzahl'], 2));
            $usedKupons[$key]['cBestellPos_arr'][$posKey]['nGesamtPreisNetto'] = gibPreisLocalizedOhneFaktor($value['nAnzahl'] * $value['nPreisNetto']);
        }

        $nCountUsedKupons++;
        $nSummeWarenkorbAlle += $usedKupon['nSummeWarenkorb'] + (float)substr($usedKupon['nWertKupon'], 1);
        $nSummeKuponAlle += (float)substr($usedKupon['nWertKupon'], 1);
        if (!in_array($usedKupon['kKunde'], $tmpUser)) {
            $nCountUser++;
            $tmpUser[] = $usedKupon['kKunde'];
        }
        $datum[$key] = $usedKupon['dErstellt'];
    }
    array_multisort($datum, SORT_DESC, $usedKupons);
}

$nProzentCountUsedKupons = (isset($nCountBestellungen_arr['nCount']) && intval($nCountBestellungen_arr['nCount']) > 0) ?
    number_format(100 / intval($nCountBestellungen_arr['nCount']) * $nCountUsedKupons, 2) :
    0;
$zusammenfassung_arr     = array(
    'nCountUsedKupons'        => $nCountUsedKupons,
    'nCountUser'              => $nCountUser,
    'nCountBestellungen'      => $nCountBestellungen_arr['nCount'],
    'nProzentCountUsedKupons' => $nProzentCountUsedKupons,
    'nSummeWarenkorbAlle'     => gibPreisLocalizedOhneFaktor($nSummeWarenkorbAlle),
    'nSummeKuponAlle'         => gibPreisLocalizedOhneFaktor($nSummeKuponAlle)
);

$smarty->assign('zusammenfassung_arr', $zusammenfassung_arr)
       ->assign('usedKupons', $usedKupons)
       ->assign('cFromDate_arr', $cFromDate_arr)
       ->assign('cToDate_arr', $cToDate_arr)
       ->assign('Kupons_arr', $Kupons_arr)
       ->assign('hinweis', $cHinweis)
       ->assign('fehler', $cFehler)
       ->assign('step', $step)
       ->display('kuponstatistik.tpl');
