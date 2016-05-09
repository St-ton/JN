<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once dirname(__FILE__) . '/includes/admininclude.php';

$oAccount->permission('MODULE_VOTESYSTEM_VIEW', true, true);

require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'blaetternavi.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'bewertung_inc.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'bewertung_inc.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'toolsajax_inc.php';

$Einstellungen = Shop::getSettings(array(CONF_BEWERTUNG));
$cHinweis      = '';
$cFehler       = '';
$step          = 'bewertung_uebersicht';

setzeSprache();

if (strlen(verifyGPDataString('tab')) > 0) {
    $smarty->assign('cTab', verifyGPDataString('tab'));
}
// Bewertung editieren
if (verifyGPCDataInteger('bewertung_editieren') === 1) {
    if (editiereBewertung($_POST)) {
        $cHinweis .= 'Ihre Bewertung wurde erfolgreich editiert. ';

        if (verifyGPCDataInteger('nFZ') === 1) {
            header('Location: freischalten.php');
            exit();
        }
    } else {
        $step = 'bewertung_editieren';
        $cFehler .= 'Fehler: Bitte &uuml;berpr&uuml;fen Sie Ihre Eingaben. ';
    }
} elseif (isset($_POST['einstellungen']) && intval($_POST['einstellungen']) === 1) {
    $cHinweis .= saveAdminSectionSettings(CONF_BEWERTUNG, $_POST);
} elseif (isset($_POST['bewertung_nicht_aktiv']) && intval($_POST['bewertung_nicht_aktiv']) === 1) {
    // Bewertungen aktivieren
    if (isset($_POST['aktivieren'])) {
        if (is_array($_POST['kBewertung']) && count($_POST['kBewertung']) > 0) {
            $cacheTags    = array();
            $kArtikel_arr = $_POST['kArtikel'];
            foreach ($_POST['kBewertung'] as $i => $kBewertung) {
                Shop::DB()->query(
                    "UPDATE tbewertung
                        SET nAktiv = 1
                        WHERE kBewertung = " . (int)$kBewertung, 3
                );
                // Durchschnitt neu berechnen
                aktualisiereDurchschnitt(intval($kArtikel_arr[$i]), $Einstellungen['bewertung']['bewertung_freischalten']);
                // Berechnet BewertungGuthabenBonus
                checkeBewertungGuthabenBonus($kBewertung, $Einstellungen);
                $cacheTags[] = CACHING_GROUP_ARTICLE . '_' . (int)$kArtikel_arr[$i];
            }
            // Clear Cache
            Shop::Cache()->flushTags($cacheTags);
            $cHinweis .= count($_POST['kBewertung']) . " Bewertung(en) wurde(n) erfolgreich aktiviert.";
        }
    } elseif (isset($_POST['loeschen'])) { // Bewertungen loeschen
        if (is_array($_POST['kBewertung']) && count($_POST['kBewertung']) > 0) {
            foreach ($_POST['kBewertung'] as $kBewertung) {
                Shop::DB()->delete('tbewertung', 'kBewertung', (int)$kBewertung);
            }

            $cHinweis .= count($_POST['kBewertung']) . " Bewertung(en) wurde(n) erfolgreich gel&ouml;scht.";
        }
    }
} elseif (isset($_POST['bewertung_aktiv']) && intval($_POST['bewertung_aktiv']) === 1) {
    if (isset($_POST['cArtNr'])) {
        // Bewertungen holen
        $oBewertungAktiv_arr = Shop::DB()->query(
            "SELECT tbewertung.*, DATE_FORMAT(tbewertung.dDatum, '%d.%m.%Y') AS Datum, tartikel.cName AS ArtikelName
                FROM tbewertung
                LEFT JOIN tartikel ON tbewertung.kArtikel = tartikel.kArtikel
                WHERE tbewertung.kSprache = " . (int)$_SESSION['kSprache'] . "
                    AND (tartikel.cArtNr LIKE '%" . Shop::DB()->escape($_POST['cArtNr']) . "%'
                        OR tartikel.cName LIKE '%" . Shop::DB()->escape($_POST['cArtNr']) . "%')
                ORDER BY tbewertung.kArtikel, tbewertung.dDatum DESC", 2
        );

        $smarty->assign('cArtNr', $_POST['cArtNr']);
    }
    // Bewertungen loeschen
    if (isset($_POST['loeschen'])) {
        if (is_array($_POST['kBewertung']) && count($_POST['kBewertung']) > 0) {
            $kArtikel_arr = $_POST['kArtikel'];
            foreach ($_POST['kBewertung'] as $i => $kBewertung) {
                // Loesche Guthaben aus tbewertungguthabenbonus und aktualisiere tkunde
                BewertungsGuthabenBonusLoeschen(intval($kBewertung));

                Shop::DB()->delete('tbewertung', 'kBewertung', (int)$kBewertung);
                // Durchschnitt neu berechnen
                aktualisiereDurchschnitt(intval($kArtikel_arr[$i]), $Einstellungen['bewertung']['bewertung_freischalten']);
            }

            $cHinweis .= count($_POST['kBewertung']) . ' Bewertung(en) wurde(n) erfolgreich gel&ouml;scht.';
        }
    }
}

if ((isset($_GET['a']) && $_GET['a'] === 'editieren') || $step === 'bewertung_editieren') {
    $step = 'bewertung_editieren';
    $smarty->assign('oBewertung', holeBewertung(verifyGPCDataInteger('kBewertung')));
    if (verifyGPCDataInteger('nFZ') === 1) {
        $smarty->assign('nFZ', 1);
    }
} elseif ($step === 'bewertung_uebersicht') {
    // Config holen
    $oConfig_arr = Shop::DB()->query(
        "SELECT *
            FROM teinstellungenconf
            WHERE kEinstellungenSektion = " . CONF_BEWERTUNG . "
            ORDER BY nSort", 2
    );
    $configCount = count($oConfig_arr);
    for ($i = 0; $i < $configCount; $i++) {
        if ($oConfig_arr[$i]->cInputTyp === 'selectbox') {
            $oConfig_arr[$i]->ConfWerte = Shop::DB()->query(
                "SELECT *
                    FROM teinstellungenconfwerte
                    WHERE kEinstellungenConf = " . (int)$oConfig_arr[$i]->kEinstellungenConf . "
                    ORDER BY nSort", 2
            );
        } elseif ($oConfig_arr[$i]->cInputTyp === 'listbox') {
            $oConfig_arr[$i]->ConfWerte = Shop::DB()->query(
                "SELECT kKundengruppe, cName
                    FROM tkundengruppe
                    ORDER BY cStandard DESC", 2
            );
        }

        if ($oConfig_arr[$i]->cInputTyp === 'listbox') {
            $oSetValue = Shop::DB()->query(
                "SELECT cWert
                    FROM teinstellungen
                    WHERE kEinstellungenSektion = " . CONF_BEWERTUNG . "
                        AND cName = '" . $oConfig_arr[$i]->cWertName . "'", 2
            );
            $oConfig_arr[$i]->gesetzterWert = $oSetValue;
        } else {
            $oSetValue = Shop::DB()->query(
                "SELECT cWert
                    FROM teinstellungen
                    WHERE kEinstellungenSektion = " . CONF_BEWERTUNG . "
                        AND cName = '" . $oConfig_arr[$i]->cWertName . "'", 1
            );
            $oConfig_arr[$i]->gesetzterWert = (isset($oSetValue->cWert)) ? $oSetValue->cWert : null;
        }
    }

    $nBewertungenProSeite = 15;
    $nAktuelleSeite1      = 1;
    $nAktuelleSeite2      = 1;
    $cSQL1                = " LIMIT " . $nBewertungenProSeite;
    $cSQL2                = " LIMIT " . $nBewertungenProSeite;
    if (verifyGPCDataInteger('s1') > 0) {
        $cSQL1           = " LIMIT " . ((intval(verifyGPCDataInteger('s1')) - 1) * $nBewertungenProSeite) . ", " . $nBewertungenProSeite;
        $nAktuelleSeite1 = verifyGPCDataInteger('s1');
    }
    if (verifyGPCDataInteger('s2') > 0) {
        $cSQL2           = " LIMIT " . ((intval(verifyGPCDataInteger('s2')) - 1) * $nBewertungenProSeite) . ", " . $nBewertungenProSeite;
        $nAktuelleSeite2 = verifyGPCDataInteger('s2');
    }
    // Bewertungen holen
    $oBewertung_arr = Shop::DB()->query(
        "SELECT tbewertung.*, DATE_FORMAT(tbewertung.dDatum, '%d.%m.%Y') AS Datum, tartikel.cName AS ArtikelName
            FROM tbewertung
            LEFT JOIN tartikel ON tbewertung.kArtikel = tartikel.kArtikel
            WHERE tbewertung.kSprache = " . (int)$_SESSION['kSprache'] . "
                AND tbewertung.nAktiv = 0
            ORDER BY tbewertung.kArtikel, tbewertung.dDatum DESC" . $cSQL1, 2
    );
    // Aktive Bewertungen
    $oBewertungLetzten50_arr = Shop::DB()->query(
        "SELECT tbewertung.*, DATE_FORMAT(tbewertung.dDatum, '%d.%m.%Y') AS Datum, tartikel.cName AS ArtikelName
            FROM tbewertung
            LEFT JOIN tartikel ON tbewertung.kArtikel = tartikel.kArtikel
            WHERE tbewertung.kSprache = " . (int)$_SESSION['kSprache'] . "
                AND tbewertung.nAktiv = 1
            ORDER BY tbewertung.dDatum DESC" . $cSQL2, 2
    );
    // Bewertungen Anzahl holen
    $oBewertung = Shop::DB()->query(
        "SELECT count(*) AS nAnzahl
            FROM tbewertung
            WHERE kSprache = " . (int)$_SESSION['kSprache'] . "
                AND nAktiv = 0", 1
    );
    // Aktive Bewertungen Anzahl holen
    $oBewertungAktiv = Shop::DB()->query(
        "SELECT count(*) AS nAnzahl
            FROM tbewertung
            WHERE kSprache = " . (int)$_SESSION['kSprache'] . "
                AND nAktiv = 1", 1
    );

    $oBlaetterNaviInaktiv = baueBlaetterNavi($nAktuelleSeite1, $oBewertung->nAnzahl, $nBewertungenProSeite);
    $oBlaetterNaviAktiv   = baueBlaetterNavi($nAktuelleSeite2, $oBewertungAktiv->nAnzahl, $nBewertungenProSeite);

    $smarty->assign('oBlaetterNaviInaktiv', $oBlaetterNaviInaktiv)
           ->assign('oBlaetterNaviAktiv', $oBlaetterNaviAktiv)
           ->assign('oBewertung_arr', $oBewertung_arr)
           ->assign('oBewertungLetzten50_arr', $oBewertungLetzten50_arr)
           ->assign('oBewertungAktiv_arr', (isset($oBewertungAktiv_arr) ? $oBewertungAktiv_arr : null))
           ->assign('oConfig_arr', $oConfig_arr)
           ->assign('Sprachen', gibAlleSprachen());
}

$smarty->assign('hinweis', $cHinweis)
       ->assign('fehler', $cFehler)
       ->assign('step', $step)
       ->display('bewertung.tpl');
